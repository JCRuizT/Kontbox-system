<?php

namespace App\Src\Domain\Exceptions;

class ContractNotFoundException extends \RuntimeException
{
    public function __construct(int $id)
    {
        parent::__construct("domain.contract.not_found");
    }
}
