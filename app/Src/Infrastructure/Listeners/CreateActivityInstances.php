<?php

namespace App\Src\Infrastructure\Listeners;

use App\Src\Domain\Contracts\AuditServiceInterface;
use App\Src\Domain\Events\ContractActivated;
use App\Src\Infrastructure\Persistence\Models\Contract;

class CreateActivityInstances
{
    public function __construct(
        private AuditServiceInterface $auditService,
    ) {}

    public function handle(ContractActivated $event): void
    {
        $contract = Contract::with('services.microservice.activities')->find($event->contractId);
        if (!$contract) return;

        $created = 0;
        foreach ($contract->services as $service) {
            if (!$service->microservice) continue;
            foreach ($service->microservice->activities as $activity) {
                if (!$activity->is_active) continue;
                $contract->activityInstances()->firstOrCreate(
                    ['activity_id' => $activity->id],
                    ['is_enabled' => true, 'status' => 'pending']
                );
                $created++;
            }
        }

        if ($created > 0) {
            $this->auditService->log(
                "Se crearon {$created} instancias de actividad para el contrato",
                $contract,
                ['instances_created' => $created, 'action' => 'create_activity_instances'],
                AuditServiceInterface::BUSINESS
            );
        }
    }
}
