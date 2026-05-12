<?php

namespace App\Src\Infrastructure\Listeners;

use App\Src\Domain\Contracts\AuditServiceInterface;
use App\Src\Domain\Enums\ContractStatus;
use App\Src\Domain\Events\ContractActivated;
use App\Src\Infrastructure\Persistence\Models\Contract;

class LogContractActivated
{
    public function __construct(
        private AuditServiceInterface $auditService,
    ) {}

    public function handle(ContractActivated $event): void
    {
        $contract = Contract::find($event->contractId);
        $this->auditService->logStatusChange(
            $contract,
            'Contrato',
            ContractStatus::DOCUMENT_LOADED->value,
            ContractStatus::ACTIVE->value
        );
    }
}
