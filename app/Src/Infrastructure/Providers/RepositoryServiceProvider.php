<?php

namespace App\Src\Infrastructure\Providers;

use App\Src\Domain\Repositories\ContractRepositoryInterface;
use App\Src\Domain\Repositories\QuotationRepositoryInterface;
use App\Src\Infrastructure\Persistence\Repositories\EloquentContractRepository;
use App\Src\Infrastructure\Persistence\Repositories\EloquentQuotationRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Vincula las interfaces de repositorio del dominio con sus implementaciones
 * concretas de Eloquent. Centraliza la resolución de dependencias de persistencia.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ContractRepositoryInterface::class,
            EloquentContractRepository::class
        );

        $this->app->bind(
            QuotationRepositoryInterface::class,
            EloquentQuotationRepository::class
        );
    }

    public function boot(): void {}
}
