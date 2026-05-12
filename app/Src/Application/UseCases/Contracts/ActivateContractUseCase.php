<?php
namespace App\Src\Application\UseCases\Contracts;

use App\Src\Domain\Entities\Contract;
use App\Src\Domain\Events\ContractActivated;
use App\Src\Domain\Exceptions\ContractNotFoundException;
use App\Src\Domain\Repositories\ContractRepositoryInterface;
use Illuminate\Contracts\Events\Dispatcher;

/**
 * Caso de uso: ActivateContractUseCase.
 */
class ActivateContractUseCase
{
    public function __construct(
        private ContractRepositoryInterface $contractRepository,
        private Dispatcher $events,
    ) {}
    /**
     * Activa un contrato con bloqueo de seguridad PDF. Dispara el evento ContractActivated.
     */

    public function execute(int $contractId): Contract
    {
        $contract = $this->contractRepository->findById($contractId);

        if (! $contract) {
            throw new ContractNotFoundException($contractId);
        }

        $contract->activate();

        $this->contractRepository->save($contract);

        $this->events->dispatch(new ContractActivated(
            contractId: $contractId,
            contractNumber: $contract->contractNumber(),
        ));

        return $contract;
    }
}
