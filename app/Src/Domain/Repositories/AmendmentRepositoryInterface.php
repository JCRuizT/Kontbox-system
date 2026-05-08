<?php

namespace App\Src\Domain\Repositories;

use App\Src\Domain\Entities\ContractAmendment;

interface AmendmentRepositoryInterface
{
    public function findById(int $id): ?ContractAmendment;
    public function save(ContractAmendment $amendment): void;
}
