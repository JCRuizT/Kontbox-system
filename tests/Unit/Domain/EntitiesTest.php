<?php

namespace Tests\Unit\Domain;

use App\Src\Domain\Entities\Contract;
use App\Src\Domain\Entities\Quotation;
use App\Src\Domain\Enums\ContractStatus;
use App\Src\Domain\Enums\QuotationStatus;
use App\Src\Domain\ValueObjects\Money;
use App\Src\Domain\ValueObjects\SignedPdf;
use Tests\TestCase;

/**
 * Prueba unitaria de las entidades del dominio (Cotización y Contrato).
 * Verifica el ciclo de vida completo de cada entidad según las reglas
 * de negocio: creación, transiciones de estado, inmutabilidad y
 * bloqueos de seguridad.
 */
class EntitiesTest extends TestCase
{
    // ========== ENTIDAD COTIZACIÓN ==========

    /**
     * Verifica que una cotización se cree correctamente en estado Borrador,
     * con los valores iniciales y permitiendo modificaciones.
     */
    public function test_quotation_can_be_created(): void
    {
        $q = new Quotation(
            id: null,
            quoteNumber: 'COT-001',
            prospectId: 1,
            planId: null,
            createdBy: 1,
            status: QuotationStatus::DRAFT,
            subtotal: new Money(100000),
            tax: new Money(19000),
            total: new Money(119000),
            validUntil: null,
            version: 1,
        );

        $this->assertNull($q->id());
        $this->assertEquals('COT-001', $q->quoteNumber());
        $this->assertEquals(QuotationStatus::DRAFT, $q->status());
        $this->assertEquals(1, $q->version());
        $this->assertTrue($q->canBeModified());
    }

    /**
     * Verifica la transición de Borrador a En Revisión al enviar la cotización.
     */
    public function test_quotation_send_for_approval(): void
    {
        $q = new Quotation(null, 'COT-002', 1, null, 1, QuotationStatus::DRAFT,
            new Money(100), new Money(19), new Money(119), null, 1);
        $q->sendForApproval();
        $this->assertEquals(QuotationStatus::UNDER_REVIEW, $q->status());
    }

    /**
     * Verifica que no se pueda enviar a revisión una cotización que ya está
     * en ese estado (evita doble envío).
     */
    public function test_quotation_cannot_send_from_under_review(): void
    {
        $q = new Quotation(null, 'COT-003', 1, null, 1, QuotationStatus::UNDER_REVIEW,
            new Money(100), new Money(19), new Money(119), null, 1);
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage(__('domain.quotation.immutable'));
        $q->sendForApproval();
    }

    /**
     * Verifica que una cotización aprobada no pueda reenviarse a revisión.
     */
    public function test_quotation_approved_cannot_send(): void
    {
        $q = new Quotation(null, 'COT-004', 1, null, 1, QuotationStatus::APPROVED,
            new Money(100), new Money(19), new Money(119), null, 1);
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage(__('domain.quotation.immutable'));
        $q->sendForApproval();
    }

    /**
     * Verifica la aprobación de una cotización desde el estado En Revisión.
     */
    public function test_quotation_approve(): void
    {
        $q = new Quotation(null, 'COT-005', 1, null, 1, QuotationStatus::UNDER_REVIEW,
            new Money(100), new Money(19), new Money(119), null, 1);
        $q->approve();
        $this->assertEquals(QuotationStatus::APPROVED, $q->status());
    }

    /**
     * Verifica que no se pueda aprobar una cotización desde Borrador.
     */
    public function test_quotation_approve_from_draft_throws(): void
    {
        $q = new Quotation(null, 'COT-006', 1, null, 1, QuotationStatus::DRAFT,
            new Money(100), new Money(19), new Money(119), null, 1);
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage(__('domain.quotation.must_be_under_review_to_approve'));
        $q->approve();
    }

    /**
     * Verifica el rechazo de una cotización desde En Revisión con motivo obligatorio.
     */
    public function test_quotation_reject(): void
    {
        $q = new Quotation(null, 'COT-007', 1, null, 1, QuotationStatus::UNDER_REVIEW,
            new Money(100), new Money(19), new Money(119), null, 1);
        $q->reject('Precio alto');
        $this->assertEquals(QuotationStatus::REJECTED, $q->status());
    }

    /**
     * Verifica que no se pueda rechazar una cotización desde Borrador.
     */
    public function test_quotation_reject_from_draft_throws(): void
    {
        $q = new Quotation(null, 'COT-008', 1, null, 1, QuotationStatus::DRAFT,
            new Money(100), new Money(19), new Money(119), null, 1);
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage(__('domain.quotation.must_be_under_review_to_reject'));
        $q->reject('Motivo');
    }

    // ========== ENTIDAD CONTRATO ==========

    /**
     * Verifica que un contrato se cree correctamente en estado Pendiente de Documento.
     */
    public function test_contract_can_be_created(): void
    {
        $c = new Contract(
            id: null,
            contractNumber: 'CTR-001',
            quotationId: 1,
            approvedBy: 1,
            status: ContractStatus::PENDING_DOCUMENT,
        );
        $this->assertNull($c->id());
        $this->assertEquals('CTR-001', $c->contractNumber());
        $this->assertEquals(ContractStatus::PENDING_DOCUMENT, $c->status());
        $this->assertNull($c->signedPdf());
    }

    /**
     * Verifica la carga exitosa de un PDF firmado, que transiciona
     * el contrato a Documento Cargado.
     */
    public function test_contract_upload_document(): void
    {
        $c = new Contract(null, 'CTR-002', 1, 1, ContractStatus::PENDING_DOCUMENT);
        $pdf = new SignedPdf('contracts/2/doc.pdf', 'signed.pdf', 2048);
        $c->uploadDocument($pdf);
        $this->assertEquals(ContractStatus::DOCUMENT_LOADED, $c->status());
        $this->assertNotNull($c->signedPdf());
        $this->assertEquals('contracts/2/doc.pdf', $c->signedPdf()->path());
    }

    /**
     * Verifica que no se pueda cargar un PDF en un contrato ya activo.
     */
    public function test_contract_upload_from_active_throws(): void
    {
        $c = new Contract(null, 'CTR-003', 1, 1, ContractStatus::ACTIVE);
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage(__('domain.contract.pending_document_status_required'));
        $c->uploadDocument(new SignedPdf('path.pdf', 'doc.pdf', 100));
    }

    /**
     * Verifica el bloqueo de seguridad: no se puede activar un contrato
     * sin tener un PDF firmado cargado (SignedPdf es null).
     */
    public function test_contract_activate_without_pdf_throws(): void
    {
        $c = new Contract(null, 'CTR-004', 1, 1, ContractStatus::DOCUMENT_LOADED);
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage(__('domain.contract.cannot_activate_without_pdf'));
        $c->activate();
    }

    /**
     * Verifica que no se pueda activar un contrato desde Pendiente de Documento,
     * incluso si tiene un objeto SignedPdf (el estado no lo permite).
     */
    public function test_contract_activate_from_pending_throws(): void
    {
        $c = new Contract(null, 'CTR-005', 1, 1, ContractStatus::PENDING_DOCUMENT,
            new SignedPdf('contracts/5/doc.pdf', 'doc.pdf', 100));
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage(__('domain.contract.cannot_activate_without_pdf'));
        $c->activate();
    }

    /**
     * Verifica la activación exitosa de un contrato cuando el PDF
     * firmado existe físicamente en disco y el estado es Documento Cargado.
     */
    public function test_contract_activate_with_valid_pdf(): void
    {
        $pdfPath = 'contracts/test_activate/contract.pdf';
        $fullPath = storage_path('app/' . $pdfPath);
        $dir = dirname($fullPath);
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        file_put_contents($fullPath, 'fake-pdf');

        $c = new Contract(null, 'CTR-006', 1, 1, ContractStatus::DOCUMENT_LOADED,
            new SignedPdf($pdfPath, 'contract.pdf', 1024));
        $c->activate();
        $this->assertEquals(ContractStatus::ACTIVE, $c->status());
    }

    /**
     * Verifica la anulación exitosa de un contrato activo con motivo.
     */
    public function test_contract_anulate(): void
    {
        $c = new Contract(null, 'CTR-007', 1, 1, ContractStatus::ACTIVE);
        $c->anulate('Cliente canceló');
        $this->assertEquals(ContractStatus::CANCELLED, $c->status());
    }

    /**
     * Verifica que no se pueda anular un contrato en Pendiente de Documento.
     */
    public function test_contract_anulate_from_pending_throws(): void
    {
        $c = new Contract(null, 'CTR-008', 1, 1, ContractStatus::PENDING_DOCUMENT);
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage(__('domain.contract.only_active_can_be_cancelled'));
        $c->anulate('Motivo');
    }

    /**
     * Verifica que no se pueda anular un contrato ya cancelado.
     */
    public function test_contract_anulate_from_cancelled_throws(): void
    {
        $c = new Contract(null, 'CTR-009', 1, 1, ContractStatus::CANCELLED);
        $this->expectException(\DomainException::class);
        $c->anulate('Motivo');
    }

    // ========== CICLO DE VIDA COMPLETO (STATE MACHINE) ==========

    /**
     * Verifica el ciclo de vida completo de una cotización:
     * Borrador → En Revisión → Aprobada.
     */
    public function test_quotation_full_lifecycle(): void
    {
        $q = new Quotation(null, 'COT-FULL', 1, null, 1, QuotationStatus::DRAFT,
            new Money(500), new Money(95), new Money(595), null, 1);

        $this->assertTrue($q->canBeModified());
        $q->sendForApproval();
        $this->assertEquals(QuotationStatus::UNDER_REVIEW, $q->status());
        $this->assertFalse($q->canBeModified());

        $q->approve();
        $this->assertEquals(QuotationStatus::APPROVED, $q->status());
    }

    /**
     * Verifica el ciclo de vida completo de un contrato:
     * Pendiente de Documento → Documento Cargado → Activo → Cancelado.
     */
    public function test_contract_full_lifecycle(): void
    {
        $pdfPath = 'contracts/test_full/contract.pdf';
        $fullPath = storage_path('app/' . $pdfPath);
        $dir = dirname($fullPath);
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        file_put_contents($fullPath, 'fake-pdf');

        $c = new Contract(null, 'CTR-FULL', 1, 1, ContractStatus::PENDING_DOCUMENT);

        $pdf = new SignedPdf('nonexistent/pdf.pdf', 'signed.pdf', 1024);
        $c->uploadDocument($pdf);
        $this->assertEquals(ContractStatus::DOCUMENT_LOADED, $c->status());

        $c = new Contract(null, 'CTR-FULL-2', 1, 1, ContractStatus::DOCUMENT_LOADED,
            new SignedPdf($pdfPath, 'signed.pdf', 1024));
        $c->activate();
        $this->assertEquals(ContractStatus::ACTIVE, $c->status());

        $c->anulate('Fin del servicio');
        $this->assertEquals(ContractStatus::CANCELLED, $c->status());
    }
}
