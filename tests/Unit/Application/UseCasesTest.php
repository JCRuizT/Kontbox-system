<?php

namespace Tests\Unit\Application;

use App\Models\User;
use App\Src\Application\UseCases\Contracts\ActivateContractUseCase;
use App\Src\Application\UseCases\Contracts\AnulateContractUseCase;
use App\Src\Application\UseCases\Contracts\UploadDocumentUseCase;
use App\Src\Application\UseCases\Quotations\ApproveQuotationUseCase;
use App\Src\Application\UseCases\Quotations\RejectQuotationUseCase;
use App\Src\Application\UseCases\Quotations\SendQuotationForApprovalUseCase;
use App\Src\Domain\Enums\ContractStatus;
use App\Src\Domain\Enums\QuotationStatus;
use App\Src\Infrastructure\Persistence\Models\Contract;
use App\Src\Infrastructure\Persistence\Models\Prospect;
use App\Src\Infrastructure\Persistence\Models\Quotation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Prueba unitaria de los casos de uso (Use Cases) de la capa de Aplicación.
 * Verifica que cada caso de uso ejecute correctamente la lógica de negocio
 * orquestando las entidades del dominio y los repositorios de infraestructura.
 */
class UseCasesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Prospect $prospect;

    /**
     * Configuración inicial: ejecuta los seeders de permisos y crea
     * un usuario y prospecto base para todos los tests.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
        $this->user = User::factory()->create();
        $this->prospect = Prospect::create([
            'company_name' => 'Test Corp',
            'contact_name' => 'John',
            'email' => 'john@test.com',
            'created_by' => $this->user->id,
        ]);
    }

    // ========== CASOS DE USO DE COTIZACIONES ==========

    /**
     * Verifica que el caso de uso "Enviar Cotización a Aprobación"
     * transicione correctamente de Borrador a En Revisión.
     */
    public function test_send_quotation_for_approval_use_case(): void
    {
        $q = Quotation::create([
            'quote_number' => 'COT-UC-001', 'prospect_id' => $this->prospect->id,
            'created_by' => $this->user->id, 'status' => QuotationStatus::DRAFT->value,
            'subtotal' => 100, 'tax' => 19, 'total' => 119, 'version' => 1,
        ]);

        $useCase = app(SendQuotationForApprovalUseCase::class);
        $result = $useCase->execute($q->id);

        $this->assertEquals(QuotationStatus::UNDER_REVIEW, $result->status());
    }

    /**
     * Verifica que el caso de uso lance una excepción si la cotización
     * no existe (ID inválido).
     */
    public function test_send_quotation_for_approval_not_found(): void
    {
        $useCase = app(SendQuotationForApprovalUseCase::class);
        $this->expectException(\RuntimeException::class);
        $useCase->execute(99999);
    }

    /**
     * Verifica que el caso de uso "Aprobar Cotización" funcione
     * cuando la cotización está en estado En Revisión.
     */
    public function test_approve_quotation_use_case(): void
    {
        $q = Quotation::create([
            'quote_number' => 'COT-UC-002', 'prospect_id' => $this->prospect->id,
            'created_by' => $this->user->id, 'status' => QuotationStatus::UNDER_REVIEW->value,
            'subtotal' => 100, 'tax' => 19, 'total' => 119, 'version' => 1,
        ]);

        $useCase = app(ApproveQuotationUseCase::class);
        $result = $useCase->execute($q->id);

        $this->assertEquals(QuotationStatus::APPROVED, $result->status());
    }

    /**
     * Verifica que el caso de uso "Rechazar Cotización" registre el
     * motivo de rechazo y cambie el estado de En Revisión a Rechazada.
     */
    public function test_reject_quotation_use_case(): void
    {
        $q = Quotation::create([
            'quote_number' => 'COT-UC-003', 'prospect_id' => $this->prospect->id,
            'created_by' => $this->user->id, 'status' => QuotationStatus::UNDER_REVIEW->value,
            'subtotal' => 100, 'tax' => 19, 'total' => 119, 'version' => 1,
        ]);

        $useCase = app(RejectQuotationUseCase::class);
        $result = $useCase->execute($q->id, 'Precio fuera de presupuesto');

        $this->assertEquals(QuotationStatus::REJECTED, $result->status());
    }

    // ========== CASOS DE USO DE CONTRATOS ==========

    /**
     * Verifica que el caso de uso "Cargar Documento PDF" funcione
     * correctamente desde el estado Pendiente de Documento.
     */
    public function test_upload_document_use_case(): void
    {
        $quotation = Quotation::create([
            'quote_number' => 'COT-UC-010', 'prospect_id' => $this->prospect->id,
            'created_by' => $this->user->id, 'status' => QuotationStatus::APPROVED->value,
            'subtotal' => 100, 'tax' => 19, 'total' => 119, 'version' => 1,
        ]);

        $contract = Contract::create([
            'contract_number' => 'CTR-UC-001', 'quotation_id' => $quotation->id,
            'approved_by' => $this->user->id, 'status' => ContractStatus::PENDING_DOCUMENT->value,
            'total_amount' => 119,
        ]);

        $useCase = app(UploadDocumentUseCase::class);
        $result = $useCase->execute($contract->id, 'contracts/test/test.pdf', 'test.pdf', 1024);

        $this->assertEquals(ContractStatus::DOCUMENT_LOADED, $result->status());
        $this->assertNotNull($result->signedPdf());
    }

    /**
     * Verifica que el caso de uso "Activar Contrato" funcione cuando
     * el PDF firmado ya fue cargado y existe en disco.
     */
    public function test_activate_contract_use_case(): void
    {
        $quotation = Quotation::create([
            'quote_number' => 'COT-UC-011', 'prospect_id' => $this->prospect->id,
            'created_by' => $this->user->id, 'status' => QuotationStatus::APPROVED->value,
            'subtotal' => 100, 'tax' => 19, 'total' => 119, 'version' => 1,
        ]);

        $pdfPath = 'contracts/test_uc/contract.pdf';
        $fullPath = storage_path('app/' . $pdfPath);
        $dir = dirname($fullPath);
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        file_put_contents($fullPath, 'fake-pdf');

        $contract = Contract::create([
            'contract_number' => 'CTR-UC-002', 'quotation_id' => $quotation->id,
            'approved_by' => $this->user->id, 'status' => ContractStatus::DOCUMENT_LOADED->value,
            'total_amount' => 119, 'signed_pdf_path' => $pdfPath,
            'signed_pdf_original_name' => 'contract.pdf', 'signed_pdf_size' => 1024,
        ]);

        $useCase = app(ActivateContractUseCase::class);
        $result = $useCase->execute($contract->id);

        $this->assertEquals(ContractStatus::ACTIVE, $result->status());
    }

    /**
     * Verifica que el caso de uso "Anular Contrato" cancele correctamente
     * un contrato activo con un motivo registrado.
     */
    public function test_anulate_contract_use_case(): void
    {
        $quotation = Quotation::create([
            'quote_number' => 'COT-UC-012', 'prospect_id' => $this->prospect->id,
            'created_by' => $this->user->id, 'status' => QuotationStatus::APPROVED->value,
            'subtotal' => 100, 'tax' => 19, 'total' => 119, 'version' => 1,
        ]);

        $contract = Contract::create([
            'contract_number' => 'CTR-UC-003', 'quotation_id' => $quotation->id,
            'approved_by' => $this->user->id, 'status' => ContractStatus::ACTIVE->value,
            'total_amount' => 119, 'start_date' => now(), 'activated_at' => now(),
        ]);

        $useCase = app(AnulateContractUseCase::class);
        $result = $useCase->execute($contract->id, 'Cliente rescindió');

        $this->assertEquals(ContractStatus::CANCELLED, $result->status());
    }

    /**
     * Verifica que no se pueda anular un contrato que no esté en estado Activo.
     */
    public function test_anulate_non_active_contract_throws(): void
    {
        $quotation = Quotation::create([
            'quote_number' => 'COT-UC-013', 'prospect_id' => $this->prospect->id,
            'created_by' => $this->user->id, 'status' => QuotationStatus::APPROVED->value,
            'subtotal' => 100, 'tax' => 19, 'total' => 119, 'version' => 1,
        ]);

        $contract = Contract::create([
            'contract_number' => 'CTR-UC-004', 'quotation_id' => $quotation->id,
            'approved_by' => $this->user->id, 'status' => ContractStatus::PENDING_DOCUMENT->value,
            'total_amount' => 119,
        ]);

        $useCase = app(AnulateContractUseCase::class);
        $this->expectException(\DomainException::class);
        $useCase->execute($contract->id, 'Motivo');
    }

    // ========== CASOS DE USO: RECURSO NO ENCONTRADO ==========

    /**
     * Verifica que activar un contrato inexistente lance RuntimeException.
     */
    public function test_activate_contract_not_found(): void
    {
        $useCase = app(ActivateContractUseCase::class);
        $this->expectException(\RuntimeException::class);
        $useCase->execute(99999);
    }

    /**
     * Verifica que cargar un documento en un contrato inexistente
     * lance RuntimeException.
     */
    public function test_upload_document_contract_not_found(): void
    {
        $useCase = app(UploadDocumentUseCase::class);
        $this->expectException(\RuntimeException::class);
        $useCase->execute(99999, 'path.pdf', 'doc.pdf', 100);
    }
}
