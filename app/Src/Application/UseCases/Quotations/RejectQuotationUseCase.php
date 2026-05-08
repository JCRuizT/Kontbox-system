<?php

namespace App\Src\Application\UseCases\Quotations;

use App\Src\Domain\Entities\Quotation;
use App\Src\Domain\Events\QuotationRejected;
use App\Src\Domain\Exceptions\QuotationNotFoundException;
use App\Src\Domain\Repositories\QuotationRepositoryInterface;
use Illuminate\Contracts\Events\Dispatcher;

/**
 * Caso de uso: RejectQuotationUseCase.
 */
class RejectQuotationUseCase
{
    public function __construct(
        private QuotationRepositoryInterface $quotationRepository,
        private Dispatcher $events,
    ) {}
    /**
     * Rechaza una cotizaci\u00f3n con un motivo obligatorio. Dispara el evento QuotationRejected.
     */


    public function execute(int $quotationId, string $reason): Quotation
    {
        $quotation = $this->quotationRepository->findById($quotationId);

        if (!$quotation) {
            throw new QuotationNotFoundException($quotationId);
        }

        $quotation->reject($reason);

        $this->quotationRepository->save($quotation);

        $this->events->dispatch(new QuotationRejected(
            quotationId: $quotationId,
            quoteNumber: $quotation->quoteNumber(),
            reason: $reason,
        ));

        return $quotation;
    }
}
