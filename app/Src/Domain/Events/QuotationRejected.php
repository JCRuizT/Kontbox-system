<?php

namespace App\Src\Domain\Events;

class QuotationRejected
{
    public function __construct(
        public readonly int $quotationId,
        public readonly string $quoteNumber,
        public readonly string $reason,
    ) {}
}
