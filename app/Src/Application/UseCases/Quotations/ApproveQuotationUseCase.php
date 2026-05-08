<?php

namespace App\Src\Application\UseCases\Quotations;

use App\Src\Domain\Entities\Quotation;
use App\Src\Domain\Repositories\QuotationRepositoryInterface;

/**
 * Caso de uso: aprobar cotización. Solo la gerencia comercial puede ejecutarlo.
 */
class ApproveQuotationUseCase
{
    public function __construct(
        private QuotationRepositoryInterface $quotationRepository,
    ) {}

    /**
     * @throws \DomainException
     */
    public function execute(int $quotationId): Quotation
    {
        $quotation = $this->quotationRepository->findById($quotationId);

        if (!$quotation) {
            throw new \RuntimeException(__('domain.quotation.not_found'));
        }

        $quotation->approve();

        $this->quotationRepository->save($quotation);

        return $quotation;
    }
}
