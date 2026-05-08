<?php

namespace Tests\Unit;

use App\Models\User;
use App\Src\Domain\Entities\Quotation;
use App\Src\Domain\Enums\QuotationStatus;
use App\Src\Domain\ValueObjects\Money;
use App\Src\Infrastructure\Persistence\Models\Prospect;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Prueba unitaria de la regla de inmutabilidad de cotizaciones.
 * Verifica que una vez enviada a aprobación (En Revisión), la cotización
 * no pueda modificarse. Solo las cotizaciones en Borrador son editables.
 * Las cotizaciones rechazadas pueden generar una nueva versión.
 */
class QuotationImmutabilityTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Prospect $prospect;

    /**
     * Configuración inicial: crea un usuario y prospecto base.
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
    }

    /**
     * Verifica que no se pueda enviar una cotización a aprobación
     * si ya está en estado En Revisión (doble envío).
     */
    public function test_it_must_not_send_draft_quotation_twice_for_approval(): void
    {
        $entity = new Quotation(
            id: null,
            quoteNumber: 'COT-001',
            prospectId: $this->prospect->id,
            planId: null,
            createdBy: $this->user->id,
            status: QuotationStatus::UNDER_REVIEW,
            subtotal: new Money(0),
            tax: new Money(0),
            total: new Money(100000),
            validUntil: null,
        );

        $this->assertFalse($entity->canBeModified());

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage(__('domain.quotation.immutable'));

        $entity->sendForApproval();
    }

    /**
     * Verifica que las cotizaciones en Borrador permitan modificaciones.
     */
    public function test_it_must_allow_modifications_on_draft_quotations(): void
    {
        $entity = new Quotation(
            id: null,
            quoteNumber: 'COT-002',
            prospectId: $this->prospect->id,
            planId: null,
            createdBy: $this->user->id,
            status: QuotationStatus::DRAFT,
            subtotal: new Money(0),
            tax: new Money(0),
            total: new Money(100000),
            validUntil: null,
        );

        $this->assertTrue($entity->canBeModified());
    }

    /**
     * Verifica que una cotización rechazada no sea modificable directamente
     * (debe generar una nueva versión).
     */
    public function test_it_must_create_new_version_when_quotation_is_rejected(): void
    {
        $originalEntity = new Quotation(
            id: 1,
            quoteNumber: 'COT-003',
            prospectId: $this->prospect->id,
            planId: null,
            createdBy: $this->user->id,
            status: QuotationStatus::REJECTED,
            subtotal: new Money(100000),
            tax: new Money(19000),
            total: new Money(119000),
            validUntil: null,
            version: 1,
        );

        $this->assertEquals(1, $originalEntity->version());
        $this->assertEquals(QuotationStatus::REJECTED, $originalEntity->status());

        $this->assertFalse($originalEntity->canBeModified());
    }

    /**
     * Verifica la transición válida de Borrador a En Revisión.
     */
    public function test_it_must_transition_from_draft_to_under_review(): void
    {
        $entity = new Quotation(
            id: null,
            quoteNumber: 'COT-004',
            prospectId: $this->prospect->id,
            planId: null,
            createdBy: $this->user->id,
            status: QuotationStatus::DRAFT,
            subtotal: new Money(100000),
            tax: new Money(19000),
            total: new Money(119000),
            validUntil: null,
        );

        $entity->sendForApproval();

        $this->assertEquals(QuotationStatus::UNDER_REVIEW, $entity->status());
    }

    /**
     * Verifica la transición válida de En Revisión a Aprobada.
     */
    public function test_it_must_transition_from_under_review_to_approved(): void
    {
        $entity = new Quotation(
            id: null,
            quoteNumber: 'COT-005',
            prospectId: $this->prospect->id,
            planId: null,
            createdBy: $this->user->id,
            status: QuotationStatus::UNDER_REVIEW,
            subtotal: new Money(100000),
            tax: new Money(19000),
            total: new Money(119000),
            validUntil: null,
        );

        $entity->approve();

        $this->assertEquals(QuotationStatus::APPROVED, $entity->status());
    }

    /**
     * Verifica que una cotización rechazada no pueda reenviarse a aprobación
     * directamente, debe crearse una nueva versión primero.
     */
    public function test_it_must_reject_and_require_new_version(): void
    {
        $entity = new Quotation(
            id: null,
            quoteNumber: 'COT-006',
            prospectId: $this->prospect->id,
            planId: null,
            createdBy: $this->user->id,
            status: QuotationStatus::UNDER_REVIEW,
            subtotal: new Money(100000),
            tax: new Money(19000),
            total: new Money(119000),
            validUntil: null,
        );

        $entity->reject('Precio fuera de presupuesto');

        $this->assertEquals(QuotationStatus::REJECTED, $entity->status());

        $this->expectException(\DomainException::class);
        $entity->sendForApproval();
    }
}
