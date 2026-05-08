<?php

namespace App\Src\Application\UseCases\Contracts;

use App\Src\Domain\Entities\Contract;
use App\Src\Domain\Repositories\ContractRepositoryInterface;

/**
 * Caso de uso: anular un contrato activo. Libera los recursos aprovisionados.
 */
class AnulateContractUseCase
{
    public function __construct(
        private ContractRepositoryInterface $contractRepository,
    ) {}

    /**
     * @throws \DomainException
     */
    public function execute(int $contractId, string $reason): Contract
    {
        $contract = $this->contractRepository->findById($contractId);

        if (!$contract) {
            throw new \RuntimeException(__('domain.contract.not_found'));
        }

        $contract->anulate($reason);

        $this->contractRepository->save($contract);

        return $contract;
    }
}
