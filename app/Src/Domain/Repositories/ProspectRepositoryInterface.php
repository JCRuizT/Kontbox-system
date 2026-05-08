<?php

namespace App\Src\Domain\Repositories;

use App\Src\Domain\Entities\Prospect;

interface ProspectRepositoryInterface
{
    public function findById(int $id): ?Prospect;
    public function save(Prospect $prospect): void;
}
