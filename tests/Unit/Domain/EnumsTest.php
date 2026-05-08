<?php

namespace Tests\Unit\Domain;

use App\Src\Domain\Enums\ContractStatus;
use App\Src\Domain\Enums\InvoiceStatus;
use App\Src\Domain\Enums\ProspectStatus;
use App\Src\Domain\Enums\QuotationStatus;
use Tests\TestCase;

/**
 * Prueba unitaria de las máquinas de estado (enums) del dominio.
 * Verifica que cada estado solo permita las transiciones válidas
 * definidas en las reglas de negocio: cotizaciones (borrador→revisión→aprobado/rechazado)
 * y contratos (pendiente→documento→activo→cancelado).
 */
class EnumsTest extends TestCase
{
    // ========== ESTADOS DE COTIZACIÓN ==========

    /**
     * Verifica que una cotización en Borrador sea editable y pueda enviarse a revisión,
     * pero no pueda aprobarse ni rechazarse directamente.
     */
    public function test_quotation_status_draft_is_editable(): void
    {
        $this->assertTrue(QuotationStatus::DRAFT->isEditable());
        $this->assertTrue(QuotationStatus::DRAFT->canBeSentForApproval());
        $this->assertFalse(QuotationStatus::DRAFT->canBeApproved());
        $this->assertFalse(QuotationStatus::DRAFT->canBeRejected());
    }

    /**
     * Verifica que una cotización En Revisión no sea editable,
     * no pueda reenviarse, pero sí pueda aprobarse o rechazarse.
     */
    public function test_quotation_status_under_review_cannot_be_edited(): void
    {
        $this->assertFalse(QuotationStatus::UNDER_REVIEW->isEditable());
        $this->assertFalse(QuotationStatus::UNDER_REVIEW->canBeSentForApproval());
        $this->assertTrue(QuotationStatus::UNDER_REVIEW->canBeApproved());
        $this->assertTrue(QuotationStatus::UNDER_REVIEW->canBeRejected());
    }

    /**
     * Verifica que una cotización Aprobada sea un estado terminal:
     * no permite edición, envío, aprobación ni rechazo.
     */
    public function test_quotation_status_approved_cannot_transition(): void
    {
        $this->assertFalse(QuotationStatus::APPROVED->isEditable());
        $this->assertFalse(QuotationStatus::APPROVED->canBeSentForApproval());
        $this->assertFalse(QuotationStatus::APPROVED->canBeApproved());
        $this->assertFalse(QuotationStatus::APPROVED->canBeRejected());
    }

    /**
     * Verifica que una cotización Rechazada sea un estado terminal:
     * no permite ninguna transición, solo versionado.
     */
    public function test_quotation_status_rejected_cannot_transition(): void
    {
        $this->assertFalse(QuotationStatus::REJECTED->isEditable());
        $this->assertFalse(QuotationStatus::REJECTED->canBeSentForApproval());
        $this->assertFalse(QuotationStatus::REJECTED->canBeApproved());
        $this->assertFalse(QuotationStatus::REJECTED->canBeRejected());
    }

    /**
     * Verifica que las etiquetas textuales de los estados de cotización
     * sean cadenas de texto y que Borrador devuelva el valor esperado.
     */
    public function test_quotation_status_labels(): void
    {
        $this->assertIsString(QuotationStatus::DRAFT->label());
        $this->assertIsString(QuotationStatus::APPROVED->label());
        $this->assertEquals('Borrador', QuotationStatus::DRAFT->label());
    }

    // ========== ESTADOS DE CONTRATO ==========

    /**
     * Estado Pendiente de Documento: solo permite cargar el PDF,
     * no activar ni anular.
     */
    public function test_contract_status_pending_document(): void
    {
        $this->assertTrue(ContractStatus::PENDING_DOCUMENT->canUploadDocument());
        $this->assertFalse(ContractStatus::PENDING_DOCUMENT->canActivate());
        $this->assertFalse(ContractStatus::PENDING_DOCUMENT->canAnulate());
    }

    /**
     * Estado Documento Cargado: permite activación pero no
     * nueva carga de PDF ni anulación directa.
     */
    public function test_contract_status_document_loaded(): void
    {
        $this->assertFalse(ContractStatus::DOCUMENT_LOADED->canUploadDocument());
        $this->assertTrue(ContractStatus::DOCUMENT_LOADED->canActivate());
        $this->assertFalse(ContractStatus::DOCUMENT_LOADED->canAnulate());
    }

    /**
     * Estado Activo: permite anulación pero no cargar documentos
     * ni reactivar.
     */
    public function test_contract_status_active(): void
    {
        $this->assertFalse(ContractStatus::ACTIVE->canUploadDocument());
        $this->assertFalse(ContractStatus::ACTIVE->canActivate());
        $this->assertTrue(ContractStatus::ACTIVE->canAnulate());
    }

    /**
     * Estado Cancelado: estado terminal, no permite ninguna operación.
     */
    public function test_contract_status_cancelled(): void
    {
        $this->assertFalse(ContractStatus::CANCELLED->canUploadDocument());
        $this->assertFalse(ContractStatus::CANCELLED->canActivate());
        $this->assertFalse(ContractStatus::CANCELLED->canAnulate());
    }

    /**
     * Verifica que las etiquetas textuales de los estados de contrato
     * sean cadenas de texto válidas.
     */
    public function test_contract_status_labels(): void
    {
        $this->assertIsString(ContractStatus::PENDING_DOCUMENT->label());
        $this->assertIsString(ContractStatus::ACTIVE->label());
    }

    // ========== VALORES DE LOS ENUM ==========

    /**
     * Verifica los valores string asociados a cada estado de cotización.
     */
    public function test_quotation_status_values(): void
    {
        $this->assertEquals('draft', QuotationStatus::DRAFT->value);
        $this->assertEquals('under_review', QuotationStatus::UNDER_REVIEW->value);
        $this->assertEquals('approved', QuotationStatus::APPROVED->value);
        $this->assertEquals('rejected', QuotationStatus::REJECTED->value);
    }

    /**
     * Verifica los valores string asociados a cada estado de contrato.
     */
    public function test_contract_status_values(): void
    {
        $this->assertEquals('pending_document', ContractStatus::PENDING_DOCUMENT->value);
        $this->assertEquals('document_loaded', ContractStatus::DOCUMENT_LOADED->value);
        $this->assertEquals('active', ContractStatus::ACTIVE->value);
        $this->assertEquals('cancelled', ContractStatus::CANCELLED->value);
    }

    /**
     * Verifica que las etiquetas de los estados de prospecto
     * sean cadenas de texto.
     */
    public function test_prospect_status_labels(): void
    {
        $this->assertIsString(ProspectStatus::NEW->label());
        $this->assertIsString(ProspectStatus::WON->label());
    }

    /**
     * Verifica los valores del enum de estados de factura.
     */
    public function test_invoice_status_enum(): void
    {
        $this->assertEquals('issued', InvoiceStatus::ISSUED->value);
        $this->assertEquals('paid', InvoiceStatus::PAID->value);
        $this->assertEquals('cancelled', InvoiceStatus::CANCELLED->value);
    }
}
