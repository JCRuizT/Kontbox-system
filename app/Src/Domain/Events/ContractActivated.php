<?php

namespace App\Src\Domain\Events;

class ContractActivated
{
    public function __construct(
        public readonly int $contractId,
        public readonly string $contractNumber,
    ) {}
}
