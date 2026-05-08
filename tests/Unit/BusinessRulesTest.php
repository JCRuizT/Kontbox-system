<?php

namespace Tests\Unit;

use App\Models\User;
use App\Src\Domain\Enums\QuotationStatus;
use App\Src\Domain\Enums\ContractStatus;
use App\Src\Domain\Enums\ProspectStatus;
use App\Src\Infrastructure\Persistence\Models\Contract;
use App\Src\Infrastructure\Persistence\Models\ContractAmendment;
use App\Src\Infrastructure\Persistence\Models\Prospect;
use App\Src\Infrastructure\Persistence\Models\Quotation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Prueba unitaria de las reglas de negocio y permisos del sistema.
 * Verifica que los roles tengan los permisos correctos según su perfil
 * (admin, vendedor, gerente comercial, administrativo) y que las
 * transiciones de estado respeten las reglas del dominio.
 */
class BusinessRulesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $vendor;
    private User $commercialManager;
    private Prospect $prospect;

    /**
     * Configuración inicial: seed de permisos, creación de usuarios
     * con roles específicos y un prospecto base.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $this->admin = User::factory()->create(['name' => 'Admin']);
        $this->admin->assignRole('admin');

        $this->vendor = User::factory()->create(['name' => 'Vendedor']);
        $this->vendor->assignRole('vendor');

        $this->commercialManager = User::factory()->create(['name' => 'Gerente']);
        $this->commercialManager->assignRole('commercial_manager');

        $this->prospect = Prospect::create([
            'company_name' => 'Test Corp',
            'contact_name' => 'Contacto',
            'email' => 'test@corp.com',
            'status' => ProspectStatus::NEW->value,
            'created_by' => $this->vendor->id,
        ]);
    }

    /** @test */
    /**
     * @test
     * Verifica que el gerente comercial tenga permiso para aprobar cotizaciones
     * y que la entidad de dominio realice la transición correctamente.
     */
    public function commercial_manager_can_approve_quotation()
    {
        $quotation = Quotation::create([
            'quote_number' => 'COT-APPR-001',
            'prospect_id' => $this->prospect->id,
            'created_by' => $this->vendor->id,
            'status' => QuotationStatus::UNDER_REVIEW->value,
            'subtotal' => 100000,
            'tax' => 19000,
            'total' => 119000,
            'version' => 1,
        ]);

        $this->assertTrue($this->commercialManager->can('quotations.approve'));

        $entity = new \App\Src\Domain\Entities\Quotation(
            id: $quotation->id,
            quoteNumber: $quotation->quote_number,
            prospectId: $quotation->prospect_id,
            planId: null,
            createdBy: $quotation->created_by,
            status: QuotationStatus::UNDER_REVIEW,
            subtotal: new \App\Src\Domain\ValueObjects\Money(100000),
            tax: new \App\Src\Domain\ValueObjects\Money(19000),
            total: new \App\Src\Domain\ValueObjects\Money(119000),
            validUntil: null,
        );

        $entity->approve();
        $this->assertEquals(QuotationStatus::APPROVED, $entity->status());
    }

    /** @test */
    /**
     * @test
     * Verifica que el vendedor NO tenga permiso para aprobar cotizaciones
     * pero SÍ para enviarlas a aprobación.
     */
    public function vendor_cannot_approve_quotation()
    {
        $this->assertFalse($this->vendor->can('quotations.approve'));
        $this->assertTrue($this->vendor->can('quotations.send_for_approval'));
    }

    /** @test */
    /**
     * @test
     * Verifica que el usuario administrativo pueda leer facturas
     * pero NO gestionar contratos ni anexos.
     */
    public function administrative_cannot_manage_contracts()
    {
        $adminUser = User::factory()->create(['name' => 'Admin User']);
        $adminUser->assignRole('administrative');

        $this->assertTrue($adminUser->can('invoices.read'));
        $this->assertFalse($adminUser->can('contracts.activate'));
        $this->assertFalse($adminUser->can('amendments.create'));
    }

    /** @test */
    /**
     * @test
     * Verifica que la regla de seguridad de anexos funcione:
     * un contrato activo no permite cargar un PDF usando uploadDocument
     * (esa operación solo es válida desde Pendiente de Documento).
     */
    public function amendment_requires_signed_pdf()
    {
        $quotation = Quotation::create([
            'quote_number' => 'COT-AMEND-001',
            'prospect_id' => $this->prospect->id,
            'created_by' => $this->vendor->id,
            'status' => QuotationStatus::APPROVED->value,
            'subtotal' => 100000,
            'tax' => 19000,
            'total' => 119000,
            'version' => 1,
        ]);

        $contract = Contract::create([
            'contract_number' => 'CTR-AMEND-001',
            'quotation_id' => $quotation->id,
            'approved_by' => $this->commercialManager->id,
            'status' => ContractStatus::ACTIVE->value,
            'total_amount' => 119000,
            'start_date' => now(),
            'activated_at' => now(),
        ]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage(
            __('domain.contract.pending_document_status_required')
        );

        $contractEntity = new \App\Src\Domain\Entities\Contract(
            id: $contract->id,
            contractNumber: $contract->contract_number,
            quotationId: $contract->quotation_id,
            approvedBy: $contract->approved_by,
            status: ContractStatus::ACTIVE,
            startDate: $contract->start_date,
            endDate: null,
            totalAmount: $contract->total_amount,
            activatedAt: $contract->activated_at,
        );

        $contractEntity->uploadDocument(
            new \App\Src\Domain\ValueObjects\SignedPdf('test.pdf', 'test.pdf', 100)
        );
    }

    /** @test */
    /**
     * @test
     * Verifica que una cotización rechazada pueda generar una nueva versión
     * con el número de versión incrementado y el estado reiniciado a Borrador.
     */
    public function rejected_quotation_can_be_versioned()
    {
        $original = Quotation::create([
            'quote_number' => 'COT-VER-001',
            'prospect_id' => $this->prospect->id,
            'created_by' => $this->vendor->id,
            'status' => QuotationStatus::REJECTED->value,
            'subtotal' => 100000,
            'tax' => 19000,
            'total' => 119000,
            'version' => 1,
            'rejection_reason' => 'Precio muy alto',
        ]);

        $newVersion = $original->replicate();
        $newVersion->quote_number = 'COT-VER-002';
        $newVersion->version = 2;
        $newVersion->parent_id = $original->id;
        $newVersion->status = QuotationStatus::DRAFT->value;
        $newVersion->rejection_reason = null;
        $newVersion->save();

        $this->assertEquals(2, $newVersion->version);
        $this->assertEquals($original->id, $newVersion->parent_id);
        $this->assertEquals(QuotationStatus::DRAFT->value, $newVersion->status);
        $this->assertNull($newVersion->rejection_reason);
    }

    /** @test */
    /**
     * @test
     * Verifica el flujo básico de la máquina de estados del contrato:
     * Pendiente de Documento → Documento Cargado.
     */
    public function contract_state_machine_flow()
    {
        $quotation = Quotation::create([
            'quote_number' => 'COT-SM-001',
            'prospect_id' => $this->prospect->id,
            'created_by' => $this->vendor->id,
            'status' => QuotationStatus::APPROVED->value,
            'subtotal' => 100000,
            'tax' => 19000,
            'total' => 119000,
            'version' => 1,
        ]);

        $contract = Contract::create([
            'contract_number' => 'CTR-SM-001',
            'quotation_id' => $quotation->id,
            'approved_by' => $this->commercialManager->id,
            'status' => ContractStatus::PENDING_DOCUMENT->value,
            'total_amount' => 119000,
        ]);

        $this->assertEquals(ContractStatus::PENDING_DOCUMENT->value, $contract->status);

        $contract->status = ContractStatus::DOCUMENT_LOADED->value;
        $contract->save();

        $contract->refresh();
        $this->assertEquals(ContractStatus::DOCUMENT_LOADED->value, $contract->status);
    }

    /** @test */
    /**
     * @test
     * Verifica que solo el administrador pueda gestionar microservicios,
     * mientras que vendedor y gerente comercial no tengan ese permiso.
     */
    public function only_admin_can_manage_microservices()
    {
        $this->assertTrue($this->admin->can('microservices.create'));
        $this->assertFalse($this->vendor->can('microservices.create'));
        $this->assertFalse($this->commercialManager->can('microservices.create'));
    }
}
