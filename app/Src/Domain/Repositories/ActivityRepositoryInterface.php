<?php

namespace App\Src\Domain\Repositories;

use App\Src\Domain\Entities\Activity;

interface ActivityRepositoryInterface
{
    public function findById(int $id): ?Activity;
    public function save(Activity $activity): void;
    public function findByMicroservice(int $microserviceId): array;
}
