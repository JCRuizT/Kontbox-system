<?php

namespace App\Src\Application\UseCases\Quotations;

use App\Src\Domain\Entities\Quotation;
use App\Src\Domain\Repositories\QuotationRepositoryInterface;

/**
 * Caso de uso: rechazar cotización con motivo. El vendedor debe crear una nueva versión.
 */
class RejectQuotationUseCase
{
    public function __construct(
        private QuotationRepositoryInterface $quotationRepository,
    ) {}

    /**
     * @throws \DomainException
     */
    public function execute(int $quotationId, string $reason): Quotation
    {
        $quotation = $this->quotationRepository->findById($quotationId);

        if (!$quotation) {
            throw new \RuntimeException(__('domain.quotation.not_found'));
        }

        $quotation->reject($reason);

        $this->quotationRepository->save($quotation);

        return $quotation;
    }
}
