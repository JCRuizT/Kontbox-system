<?php

namespace App\Src\Domain\Events;

class QuotationSentForApproval
{
    public function __construct(
        public readonly int $quotationId,
    ) {}
}
