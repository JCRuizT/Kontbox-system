<?php

namespace App\Src\Domain\Events;

class QuotationApproved
{
    public function __construct(
        public readonly int $quotationId,
        public readonly string $quoteNumber,
    ) {}
}
