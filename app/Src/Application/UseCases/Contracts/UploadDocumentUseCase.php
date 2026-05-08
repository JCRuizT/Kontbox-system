<?php

namespace App\Src\Application\UseCases\Contracts;

use App\Src\Domain\Entities\Contract;
use App\Src\Domain\Repositories\ContractRepositoryInterface;
use App\Src\Domain\ValueObjects\SignedPdf;

/**
 * Caso de uso: cargar documento PDF firmado a un contrato pendiente de documento.
 */
class UploadDocumentUseCase
{
    public function __construct(
        private ContractRepositoryInterface $contractRepository,
    ) {}

    /**
     * @throws \DomainException
     */
    public function execute(int $contractId, string $pdfPath, string $originalName, int $sizeInBytes): Contract
    {
        $contract = $this->contractRepository->findById($contractId);

        if (!$contract) {
            throw new \RuntimeException(__('domain.contract.not_found'));
        }

        $pdf = new SignedPdf($pdfPath, $originalName, $sizeInBytes);
        $contract->uploadDocument($pdf);

        $this->contractRepository->save($contract);

        return $contract;
    }
}
