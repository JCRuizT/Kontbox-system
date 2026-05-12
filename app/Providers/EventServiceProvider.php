<?php

namespace App\Providers;

use App\Src\Domain\Events\ContractActivated;
use App\Src\Domain\Events\QuotationApproved;
use App\Src\Domain\Events\QuotationRejected;
use App\Src\Domain\Events\QuotationSentForApproval;
use App\Src\Infrastructure\Listeners\CreateActivityInstances;
use App\Src\Infrastructure\Listeners\LogContractActivated;
use App\Src\Infrastructure\Listeners\LogQuotationSentForApproval;
use App\Src\Infrastructure\Listeners\LogQuotationStatusChanged;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        ContractActivated::class => [
            LogContractActivated::class,
            CreateActivityInstances::class,
        ],
        QuotationSentForApproval::class => [
            LogQuotationSentForApproval::class,
        ],
        QuotationApproved::class => [
            LogQuotationStatusChanged::class,
        ],
        QuotationRejected::class => [
            LogQuotationStatusChanged::class,
        ],
    ];
}
