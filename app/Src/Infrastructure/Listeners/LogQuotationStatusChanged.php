<?php

namespace App\Src\Infrastructure\Listeners;

use App\Src\Domain\Contracts\AuditServiceInterface;
use App\Src\Domain\Enums\QuotationStatus;
use App\Src\Domain\Events\QuotationApproved;
use App\Src\Domain\Events\QuotationRejected;
use App\Src\Infrastructure\Persistence\Models\Quotation;

class LogQuotationStatusChanged
{
    public function __construct(
        private AuditServiceInterface $auditService,
    ) {}

    public function handle(QuotationApproved|QuotationRejected $event): void
    {
        $quotation = Quotation::find($event->quotationId);
        $isApproved = $event instanceof QuotationApproved;

        $this->auditService->logStatusChange(
            $quotation,
            'Cotización',
            QuotationStatus::UNDER_REVIEW->value,
            $isApproved ? QuotationStatus::APPROVED->value : QuotationStatus::REJECTED->value
        );
    }
}
