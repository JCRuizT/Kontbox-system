<?php

namespace App\Src\Application\UseCases\Quotations;

use App\Src\Domain\Entities\Quotation;
use App\Src\Domain\Events\QuotationSentForApproval;
use App\Src\Domain\Exceptions\QuotationNotFoundException;
use App\Src\Domain\Repositories\QuotationRepositoryInterface;
use Illuminate\Contracts\Events\Dispatcher;

/**
 * Caso de uso: SendQuotationForApprovalUseCase.
 */
class SendQuotationForApprovalUseCase
{
    public function __construct(
        private QuotationRepositoryInterface $quotationRepository,
        private Dispatcher $events,
    ) {}
    /**
     * Env\u00eda una cotizaci\u00f3n a revisi\u00f3n. Cambia el estado de Borrador a En revisi\u00f3n y dispara el evento QuotationSentForApproval.
     */


    public function execute(int $quotationId): Quotation
    {
        $quotation = $this->quotationRepository->findById($quotationId);

        if (!$quotation) {
            throw new QuotationNotFoundException($quotationId);
        }

        $quotation->sendForApproval();

        $this->quotationRepository->save($quotation);

        $this->events->dispatch(new QuotationSentForApproval(
            quotationId: $quotationId,
        ));

        return $quotation;
    }
}
