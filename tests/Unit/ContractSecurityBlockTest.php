<?php

namespace Tests\Unit;

use App\Models\User;
use App\Src\Domain\Entities\Contract;
use App\Src\Domain\Enums\ContractStatus;
use App\Src\Domain\Enums\QuotationStatus;
use App\Src\Domain\ValueObjects\SignedPdf;
use App\Src\Infrastructure\Persistence\Models\Prospect;
use App\Src\Infrastructure\Persistence\Models\Quotation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Prueba unitaria del bloqueo de seguridad PDF en contratos.
 * Verifica que el ciclo de vida del contrato esté protegido:
 * - No se puede activar sin PDF firmado.
 * - No se puede activar desde un estado incorrecto.
 * - Solo se puede cargar PDF en Pendiente de Documento.
 * - Solo se puede anular un contrato Activo.
 */
class ContractSecurityBlockTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Prospect $prospect;
    private Quotation $quotation;

    /**
     * Configuración inicial: crea un usuario, prospecto y cotización aprobada
     * para usar como base en todos los tests de seguridad de contratos.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->prospect = Prospect::create([
            'company_name' => 'Test Corp',
            'contact_name' => 'John Doe',
            'email' => 'john@test.com',
            'phone' => '1234567890',
            'status' => 'new',
            'created_by' => $this->user->id,
        ]);
        $this->quotation = Quotation::create([
            'quote_number' => 'COT-TEST-001',
            'prospect_id' => $this->prospect->id,
            'created_by' => $this->user->id,
            'status' => QuotationStatus::APPROVED->value,
            'subtotal' => 100000.00,
            'tax' => 19000.00,
            'total' => 119000.00,
            'valid_until' => now()->addDays(15),
            'version' => 1,
        ]);
    }

    /**
     * Bloqueo: no se debe activar un contrato sin PDF firmado cargado.
     * La entidad debe lanzar DomainException si SignedPdf es null.
     */
    public function test_it_must_not_activate_contract_without_signed_pdf(): void
    {
        $contract = new Contract(
            id: null,
            contractNumber: 'CTR-001',
            quotationId: $this->quotation->id,
            approvedBy: $this->user->id,
            status: ContractStatus::DOCUMENT_LOADED,
        );

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage(
            'domain.contract.cannot_activate_without_pdf'
        );

        $contract->activate();
    }

    /**
     * Bloqueo: no se debe activar un contrato desde Pendiente de Documento,
     * incluso si tiene un objeto SignedPdf asociado (el estado no lo permite).
     */
    public function test_it_must_not_activate_contract_when_status_is_pending_document(): void
    {
        $contract = new Contract(
            id: null,
            contractNumber: 'CTR-002',
            quotationId: $this->quotation->id,
            approvedBy: $this->user->id,
            status: ContractStatus::PENDING_DOCUMENT,
            signedPdf: new SignedPdf(
                path: 'contracts/test/signed.pdf',
                originalName: 'signed.pdf',
                sizeInBytes: 1024
            ),
        );

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage(
            'domain.contract.cannot_activate_without_pdf'
        );

        $contract->activate();
    }

    /**
     * Verifica que un contrato se active correctamente cuando el PDF
     * firmado existe físicamente y el estado es Documento Cargado.
     */
    public function test_it_must_activate_contract_when_pdf_is_loaded(): void
    {
        $tempPdfPath = 'contracts/test/signed.pdf';
        $fullPath = storage_path('app/' . $tempPdfPath);
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($fullPath, 'fake-pdf-content');

        $contract = new Contract(
            id: null,
            contractNumber: 'CTR-003',
            quotationId: $this->quotation->id,
            approvedBy: $this->user->id,
            status: ContractStatus::DOCUMENT_LOADED,
            signedPdf: new SignedPdf(
                path: $tempPdfPath,
                originalName: 'signed.pdf',
                sizeInBytes: 1024
            ),
        );

        $contract->activate();

        $this->assertEquals(ContractStatus::ACTIVE, $contract->status());
        $this->assertNotNull($contract->signedPdf());
    }

    /**
     * Verifica que se pueda cargar un PDF cuando el contrato está
     * en estado Pendiente de Documento, transicionando a Documento Cargado.
     */
    public function test_it_must_upload_document_when_status_is_pending_document(): void
    {
        $tempPdfPath = 'contracts/test/new-signed.pdf';
        $fullPath = storage_path('app/' . $tempPdfPath);
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($fullPath, 'fake-pdf-content');

        $contract = new Contract(
            id: null,
            contractNumber: 'CTR-004',
            quotationId: $this->quotation->id,
            approvedBy: $this->user->id,
            status: ContractStatus::PENDING_DOCUMENT,
        );

        $pdf = new SignedPdf(
            path: $tempPdfPath,
            originalName: 'signed.pdf',
            sizeInBytes: 1024,
        );

        $contract->uploadDocument($pdf);

        $this->assertEquals(ContractStatus::DOCUMENT_LOADED, $contract->status());
        $this->assertNotNull($contract->signedPdf());
    }

    /**
     * Verifica que no se permita cargar un PDF en un contrato ya activo.
     */
    public function test_it_must_reject_upload_when_status_is_already_active(): void
    {
        $contract = new Contract(
            id: null,
            contractNumber: 'CTR-005',
            quotationId: $this->quotation->id,
            approvedBy: $this->user->id,
            status: ContractStatus::ACTIVE,
        );

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage(
            'domain.contract.pending_document_status_required'
        );

        $contract->uploadDocument(new SignedPdf('path.pdf', 'doc.pdf', 100));
    }

    /**
     * Verifica que no se pueda anular un contrato que no esté en estado Activo.
     */
    public function test_it_cannot_anulate_non_active_contract(): void
    {
        $contract = new Contract(
            id: null,
            contractNumber: 'CTR-006',
            quotationId: $this->quotation->id,
            approvedBy: $this->user->id,
            status: ContractStatus::PENDING_DOCUMENT,
        );

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage(
            'domain.contract.only_active_can_be_cancelled'
        );

        $contract->anulate('Razón de prueba');
    }

    /**
     * Verifica la anulación exitosa de un contrato activo con motivo registrado.
     */
    public function test_it_must_anulate_active_contract(): void
    {
        $contract = new Contract(
            id: null,
            contractNumber: 'CTR-007',
            quotationId: $this->quotation->id,
            approvedBy: $this->user->id,
            status: ContractStatus::ACTIVE,
        );

        $contract->anulate('Cliente rescindió contrato');

        $this->assertEquals(ContractStatus::CANCELLED, $contract->status());
    }
}
