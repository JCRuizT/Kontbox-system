<?php

namespace App\Src\Domain\Repositories;

use App\Src\Domain\Entities\Plan;

interface PlanRepositoryInterface
{
    public function findById(int $id): ?Plan;
    public function save(Plan $plan): void;
    public function findAllActive(): array;
}
