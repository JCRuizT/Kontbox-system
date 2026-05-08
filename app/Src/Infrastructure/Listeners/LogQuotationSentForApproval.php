<?php

namespace App\Src\Infrastructure\Listeners;

use App\Src\Domain\Contracts\AuditServiceInterface;
use App\Src\Domain\Enums\QuotationStatus;
use App\Src\Domain\Events\QuotationSentForApproval;
use App\Src\Infrastructure\Persistence\Models\Quotation;

class LogQuotationSentForApproval
{
    public function __construct(
        private AuditServiceInterface $auditService,
    ) {}

    public function handle(QuotationSentForApproval $event): void
    {
        $quotation = Quotation::find($event->quotationId);
        $this->auditService->logStatusChange(
            $quotation,
            'Cotización',
            QuotationStatus::DRAFT->value,
            QuotationStatus::UNDER_REVIEW->value
        );
    }
}
