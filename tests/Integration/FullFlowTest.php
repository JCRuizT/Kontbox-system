<?php

namespace Tests\Integration;

use App\Models\User;
use App\Src\Domain\Enums\ContractStatus;
use App\Src\Domain\Enums\QuotationStatus;
use App\Src\Domain\Services\AuditService;
use App\Src\Infrastructure\Persistence\Models\Contract;
use App\Src\Infrastructure\Persistence\Models\Prospect;
use App\Src\Infrastructure\Persistence\Models\Quotation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Prueba de integración que verifica el flujo completo del sistema.
 * Simula un escenario real de negocio recorriendo todos los módulos:
 * creación de cotización → envío a aprobación → aprobación →
 * creación de contrato → carga de PDF → activación → facturación → anulación.
 * También prueba el rechazo y versionado de cotizaciones, y los permisos
 * de roles a lo largo del flujo.
 */
class FullFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $vendor;
    private User $manager;
    private User $admin;
    private Prospect $prospect;

    /**
     * Configuración inicial: seed de permisos, creación de usuarios
     * (vendedor, gerente, admin) y un prospecto base.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $this->admin = User::factory()->create(['name' => 'Admin']);
        $this->admin->assignRole('admin');

        $this->vendor = User::factory()->create(['name' => 'Vendedor']);
        $this->vendor->assignRole('vendor');

        $this->manager = User::factory()->create(['name' => 'Gerente']);
        $this->manager->assignRole('commercial_manager');

        $this->prospect = Prospect::create([
            'company_name' => 'Integración Corp',
            'contact_name' => 'Test',
            'email' => 'test@int.com',
            'created_by' => $this->vendor->id,
        ]);
    }

    /**
     * Flujo completo de negocio:
     * 1. Vendedor crea cotización en Borrador.
     * 2. Vendedor envía a aprobación → En Revisión.
     * 3. Gerente aprueba → Aprobada.
     * 4. Gerente crea contrato desde cotización aprobada → Pendiente de Documento.
     * 5. Se carga PDF firmado → Documento Cargado.
     * 6. Se activa el contrato → Activo.
     * 7. Se crea factura sobre el contrato activo.
     * 8. Se anula el contrato → Cancelado.
     * 9. Se verifica que los logs de auditoría se hayan registrado.
     */
    public function test_complete_business_flow(): void
    {
        // 1. VENDEDOR crea cotización en Borrador
        $q = Quotation::create([
            'quote_number' => 'COT-INT-001',
            'prospect_id' => $this->prospect->id,
            'created_by' => $this->vendor->id,
            'status' => QuotationStatus::DRAFT->value,
            'subtotal' => 500000,
            'tax' => 95000,
            'total' => 595000,
            'version' => 1,
        ]);

        AuditService::logCreate($q, 'Cotización', ['prospect' => $this->prospect->id]);

        // 2. VENDEDOR envía a aprobación → En Revisión
        $useCase = app(\App\Src\Application\UseCases\Quotations\SendQuotationForApprovalUseCase::class);
        $useCase->execute($q->id);
        $q->refresh();
        $this->assertEquals(QuotationStatus::UNDER_REVIEW->value, $q->status);

        // 3. GERENTE aprueba → Aprobada
        $approveUseCase = app(\App\Src\Application\UseCases\Quotations\ApproveQuotationUseCase::class);
        $approveUseCase->execute($q->id);
        $q->refresh();
        $this->assertEquals(QuotationStatus::APPROVED->value, $q->status);

        // 4. GERENTE crea contrato desde cotización aprobada → Pendiente de Documento
        $contract = Contract::create([
            'contract_number' => 'CTR-INT-001',
            'quotation_id' => $q->id,
            'approved_by' => $this->manager->id,
            'status' => ContractStatus::PENDING_DOCUMENT->value,
            'total_amount' => 595000,
        ]);
        AuditService::logCreate($contract, 'Contrato', ['quotation' => $q->id]);

        // 5. SUBIR PDF firmado (vía caso de uso) → Documento Cargado
        $uploadUseCase = app(\App\Src\Application\UseCases\Contracts\UploadDocumentUseCase::class);
        $uploadUseCase->execute($contract->id, 'contracts/int/test.pdf', 'contrato_firmado.pdf', 2048);
        $contract->refresh();
        $this->assertEquals(ContractStatus::DOCUMENT_LOADED->value, $contract->status);
        $this->assertNotNull($contract->signed_pdf_path);

        // 6. ACTIVAR contrato → Activo
        $pdfPath = $contract->signed_pdf_path;
        $fullPath = storage_path('app/' . $pdfPath);
        $dir = dirname($fullPath);
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        file_put_contents($fullPath, 'fake-pdf-content');

        $activateUseCase = app(\App\Src\Application\UseCases\Contracts\ActivateContractUseCase::class);
        $activateUseCase->execute($contract->id);
        $contract->refresh();
        $this->assertEquals(ContractStatus::ACTIVE->value, $contract->status);
        $this->assertNotNull($contract->activated_at);

        // 7. CREAR FACTURA sobre contrato activo
        $invoice = \App\Src\Infrastructure\Persistence\Models\Invoice::create([
            'invoice_number' => 'INV-INT-001',
            'contract_id' => $contract->id,
            'amount' => 595000,
            'issued_date' => now(),
            'status' => 'issued',
            'created_by' => $this->admin->id,
        ]);
        AuditService::logCreate($invoice, 'Factura', ['contract' => $contract->id]);
        $this->assertDatabaseHas('invoices', ['invoice_number' => 'INV-INT-001']);

        // 8. ANULAR contrato → Cancelado
        $anulateUseCase = app(\App\Src\Application\UseCases\Contracts\AnulateContractUseCase::class);
        $anulateUseCase->execute($contract->id, 'Fin del período contractual');
        $contract->refresh();
        $this->assertEquals(ContractStatus::CANCELLED->value, $contract->status);

        // 9. Verificar logs de auditoría
        $this->assertDatabaseHas('activity_log', [
            'subject_type' => Quotation::class,
            'event' => 'created',
        ]);
    }

    /**
     * Prueba el flujo de rechazo y versionado de cotizaciones:
     * - Crear cotización en estado En Revisión.
     * - Rechazar con motivo.
     * - Crear nueva versión (incrementa versión, reinicia a Borrador).
     */
    public function test_quotation_rejection_and_new_version(): void
    {
        // Crear cotización y enviar a aprobación
        $q = Quotation::create([
            'quote_number' => 'COT-INT-002',
            'prospect_id' => $this->prospect->id,
            'created_by' => $this->vendor->id,
            'status' => QuotationStatus::UNDER_REVIEW->value,
            'subtotal' => 300000,
            'tax' => 57000,
            'total' => 357000,
            'version' => 1,
        ]);

        // Rechazar con motivo
        $rejectUseCase = app(\App\Src\Application\UseCases\Quotations\RejectQuotationUseCase::class);
        $rejectUseCase->execute($q->id, 'Presupuesto no disponible');
        $q->refresh();
        $this->assertEquals(QuotationStatus::REJECTED->value, $q->status);

        // Crear nueva versión a partir de la rechazada
        $newVersion = $q->replicate();
        $newVersion->quote_number = 'COT-INT-002-V2';
        $newVersion->version = 2;
        $newVersion->parent_id = $q->id;
        $newVersion->status = QuotationStatus::DRAFT->value;
        $newVersion->rejection_reason = null;
        $newVersion->save();

        AuditService::logCreate($newVersion, 'Cotización (nueva versión)', ['parent_id' => $q->id, 'version' => 2]);

        $this->assertEquals(2, $newVersion->version);
        $this->assertEquals($q->id, $newVersion->parent_id);
        $this->assertEquals(QuotationStatus::DRAFT->value, $newVersion->status);
    }

    /**
     * Verifica que los permisos de cada rol se mantengan
     * correctamente a lo largo del flujo de negocio:
     * - Admin puede crear microservicios.
     * - Vendedor puede crear cotizaciones pero no aprobarlas.
     * - Gerente comercial puede aprobar cotizaciones y activar contratos.
     */
    public function test_role_permissions_in_flow(): void
    {
        $this->assertTrue($this->admin->can('microservices.create'));
        $this->assertTrue($this->admin->hasRole('admin'));

        $this->assertTrue($this->vendor->can('quotations.create'));
        $this->assertFalse($this->vendor->can('quotations.approve'));
        $this->assertTrue($this->vendor->can('quotations.send_for_approval'));

        $this->assertTrue($this->manager->can('quotations.approve'));
        $this->assertTrue($this->manager->can('contracts.activate'));
        $this->assertTrue($this->manager->can('amendments.create'));
        $this->assertFalse($this->manager->can('microservices.create'));
    }
}
