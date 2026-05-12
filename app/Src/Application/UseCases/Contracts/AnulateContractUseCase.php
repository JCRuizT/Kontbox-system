<?php
namespace App\Src\Application\UseCases\Contracts;

use App\Src\Domain\Contracts\AuditServiceInterface;
use App\Src\Domain\Entities\Contract;
use App\Src\Domain\Enums\ContractStatus;
use App\Src\Domain\Repositories\ContractRepositoryInterface;

/**
 * Caso de uso: AnulateContractUseCase.
 */
class AnulateContractUseCase
{
    public function __construct(
        private ContractRepositoryInterface $contractRepository,
        private AuditServiceInterface $auditService,
    ) {}
    /**
     * Anula un contrato activo con un motivo obligatorio. Registra la anulaci\u00f3n en auditor\u00eda.
     */

    public function execute(int $contractId, string $reason): Contract
    {
        $contract = $this->contractRepository->findById($contractId);

        if (! $contract) {
            throw new \App\Src\Domain\Exceptions\ContractNotFoundException($contractId);
        }

        $contract->anulate($reason);

        $this->contractRepository->save($contract);

        $this->auditService->logStatusChange(
            null,
            'Contrato',
            ContractStatus::ACTIVE->value,
            ContractStatus::CANCELLED->value
        );

        return $contract;
    }
}
