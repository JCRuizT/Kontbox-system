<?php

namespace App\Src\Application\UseCases\Contracts;

use App\Src\Domain\Contracts\AuditServiceInterface;
use App\Src\Domain\Entities\Contract;
use App\Src\Domain\Repositories\ContractRepositoryInterface;
use App\Src\Domain\ValueObjects\SignedPdf;

/**
 * Caso de uso: UploadDocumentUseCase.
 */
class UploadDocumentUseCase
{
    public function __construct(
        private ContractRepositoryInterface $contractRepository,
        private AuditServiceInterface $auditService,
    ) {}
    /**
     * Carga un PDF firmado al contrato. Valida el estado del contrato y registra la auditor\u00eda.
     */


    public function execute(int $contractId, string $path, string $originalName, int $size): Contract
    {
        $contract = $this->contractRepository->findById($contractId);

        if (!$contract) {
            throw new \App\Src\Domain\Exceptions\ContractNotFoundException($contractId);
        }

        $pdf = new SignedPdf($path, $originalName, $size);
        $contract->uploadDocument($pdf);

        $this->contractRepository->save($contract);

        $this->auditService->log(
            'Cargó PDF firmado',
            null,
            ['action' => 'upload_pdf', 'file' => $originalName],
            AuditServiceInterface::BUSINESS
        );

        return $contract;
    }
}
