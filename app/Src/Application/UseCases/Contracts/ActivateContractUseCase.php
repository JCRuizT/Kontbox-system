<?php

namespace App\Src\Application\UseCases\Contracts;

use App\Src\Domain\Entities\Contract;
use App\Src\Domain\Repositories\ContractRepositoryInterface;

/**
 * Caso de uso: activar un contrato. Verifica que el PDF firmado exista en la plataforma.
 */
class ActivateContractUseCase
{
    public function __construct(
        private ContractRepositoryInterface $contractRepository,
    ) {}

    /**
     * Regla de seguridad: no se puede activar sin PDF firmado.
     *
     * @throws \DomainException
     */
    public function execute(int $contractId): Contract
    {
        $contract = $this->contractRepository->findById($contractId);

        if (!$contract) {
            throw new \RuntimeException(__('domain.contract.not_found'));
        }

        $contract->activate();

        $this->contractRepository->save($contract);

        return $contract;
    }
}
