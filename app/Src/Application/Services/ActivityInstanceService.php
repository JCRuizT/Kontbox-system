<?php
namespace App\Src\Application\Services;

use App\Src\Domain\Contracts\AuditServiceInterface;
use App\Src\Infrastructure\Persistence\Models\Contract;

/**
 * Caso de uso: ActivityInstanceService.
 */
class ActivityInstanceService
{
    public function __construct(
        private AuditServiceInterface $auditService,
    ) {}
    /**
     * Crea las instancias de actividad para un contrato activado. Recorre los servicios del contrato y sus actividades.
     */

    public function createForContract(Contract $contract): int
    {
        $contract->loadMissing('services.microservice.activities');
        $created = 0;

        foreach ($contract->services as $service) {
            if (! $service->microservice) {
                continue;
            }

            foreach ($service->microservice->activities as $activity) {
                if (! $activity->is_active) {
                    continue;
                }

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

        return $created;
    }
}
