<?php

namespace App\Src\Infrastructure\Providers;

use App\Src\Domain\Contracts\AuditServiceInterface;
use App\Src\Domain\Repositories\ActivityRepositoryInterface;
use App\Src\Domain\Repositories\AmendmentRepositoryInterface;
use App\Src\Domain\Repositories\ContractRepositoryInterface;
use App\Src\Domain\Repositories\InvoiceRepositoryInterface;
use App\Src\Domain\Repositories\MicroserviceRepositoryInterface;
use App\Src\Domain\Repositories\PlanRepositoryInterface;
use App\Src\Domain\Repositories\ProspectRepositoryInterface;
use App\Src\Domain\Repositories\QuotationRepositoryInterface;
use App\Src\Infrastructure\Persistence\Repositories\EloquentActivityRepository;
use App\Src\Infrastructure\Persistence\Repositories\EloquentAmendmentRepository;
use App\Src\Infrastructure\Persistence\Repositories\EloquentContractRepository;
use App\Src\Infrastructure\Persistence\Repositories\EloquentInvoiceRepository;
use App\Src\Infrastructure\Persistence\Repositories\EloquentMicroserviceRepository;
use App\Src\Infrastructure\Persistence\Repositories\EloquentPlanRepository;
use App\Src\Infrastructure\Persistence\Repositories\EloquentProspectRepository;
use App\Src\Infrastructure\Persistence\Repositories\EloquentQuotationRepository;
use App\Src\Infrastructure\Services\SpatieAuditService;
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

        $this->app->bind(
            MicroserviceRepositoryInterface::class,
            EloquentMicroserviceRepository::class
        );

        $this->app->bind(
            ActivityRepositoryInterface::class,
            EloquentActivityRepository::class
        );

        $this->app->bind(
            PlanRepositoryInterface::class,
            EloquentPlanRepository::class
        );

        $this->app->bind(
            ProspectRepositoryInterface::class,
            EloquentProspectRepository::class
        );

        $this->app->bind(
            InvoiceRepositoryInterface::class,
            EloquentInvoiceRepository::class
        );

        $this->app->bind(
            AmendmentRepositoryInterface::class,
            EloquentAmendmentRepository::class
        );

        $this->app->singleton(
            AuditServiceInterface::class,
            SpatieAuditService::class
        );
    }

    public function boot(): void {}
}
