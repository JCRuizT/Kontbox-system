<?php

namespace App\Src\Application\UseCases\Quotations;

use App\Src\Domain\Entities\Quotation;
use App\Src\Domain\Events\QuotationApproved;
use App\Src\Domain\Exceptions\QuotationNotFoundException;
use App\Src\Domain\Repositories\QuotationRepositoryInterface;
use Illuminate\Contracts\Events\Dispatcher;

/**
 * Caso de uso: ApproveQuotationUseCase.
 */
class ApproveQuotationUseCase
{
    public function __construct(
        private QuotationRepositoryInterface $quotationRepository,
        private Dispatcher $events,
    ) {}
    /**
     * Aprueba una cotizaci\u00f3n. Solo la gerencia comercial puede ejecutarlo. Dispara el evento QuotationApproved.
     */


    public function execute(int $quotationId): Quotation
    {
        $quotation = $this->quotationRepository->findById($quotationId);

        if (!$quotation) {
            throw new QuotationNotFoundException($quotationId);
        }

        $quotation->approve();

        $this->quotationRepository->save($quotation);

        $this->events->dispatch(new QuotationApproved(
            quotationId: $quotationId,
            quoteNumber: $quotation->quoteNumber(),
        ));

        return $quotation;
    }
}
