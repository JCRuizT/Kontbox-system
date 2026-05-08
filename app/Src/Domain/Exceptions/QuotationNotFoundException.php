<?php

namespace App\Src\Domain\Exceptions;

class QuotationNotFoundException extends \RuntimeException
{
    public function __construct(int $id)
    {
        parent::__construct("domain.quotation.not_found");
    }
}
