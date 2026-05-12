<?php

namespace App\Src\Domain\Repositories;

use App\Src\Domain\Entities\Microservice;

interface MicroserviceRepositoryInterface
{
    public function findById(int $id): ?Microservice;
    public function save(Microservice $microservice): void;
    public function findAllActive(): array;
}
