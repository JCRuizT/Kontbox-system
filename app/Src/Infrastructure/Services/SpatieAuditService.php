<?php

namespace App\Src\Infrastructure\Services;

use App\Src\Domain\Contracts\AuditServiceInterface;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;

class SpatieAuditService implements AuditServiceInterface
{
    public function log(string $description, $subject = null, array $properties = [], string $logName = 'app'): void
    {
        $request = request();
        $log = activity($logName)
            ->withProperties(array_merge($properties, [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'method' => $request->method(),
                'url' => $request->fullUrl(),
            ]));

        if (auth()->check()) {
            $log->causedBy(auth()->user());
        }

        $log->event($logName);

        if ($subject !== null && $subject instanceof Model) {
            $log->performedOn($subject);
        }

        $log->log($description);
    }

    public function logCreate($subject, string $entityName, array $data = []): void
    {
        $description = __('domain.audit_log.created_entity', ['entity' => $entityName, 'id' => $subject->id ?? '']);
        $this->log($description, $subject, ['action' => 'create', 'entity' => $entityName, 'data' => $this->redactSensitive($data)], self::CRUD);
    }

    public function logUpdate($subject, string $entityName, array $original, array $changes): void
    {
        $description = __('domain.audit_log.updated_entity', ['entity' => $entityName, 'id' => $subject->id ?? '']);
        $this->log($description, $subject, [
            'action' => 'update',
            'entity' => $entityName,
            'old' => $this->redactSensitive($original),
            'new' => $this->redactSensitive($changes),
        ], self::CRUD);
    }

    public function logDelete($subject, string $entityName): void
    {
        $description = __('domain.audit_log.deleted_entity', ['entity' => $entityName, 'id' => $subject->id ?? '']);
        $this->log($description, $subject, ['action' => 'deactivate', 'entity' => $entityName], self::CRUD);
    }

    public function logStatusChange($subject, string $entityName, string $from, string $to): void
    {
        $description = __('domain.audit_log.status_changed', ['entity' => $entityName, 'id' => $subject->id ?? '', 'from' => $from, 'to' => $to]);
        $this->log($description, $subject, ['action' => 'status_change', 'from' => $from, 'to' => $to], self::BUSINESS);
    }

    public function logBusiness(string $description, $subject = null, array $extraProps = []): void
    {
        $this->log($description, $subject, $extraProps, self::BUSINESS);
    }

    public function logError(string $description, array $context = []): void
    {
        $this->log($description, null, ['action' => 'error', 'context' => $context], self::ERROR);
    }

    private function redactSensitive(array $data): array
    {
        $sensitive = ['password', 'token', 'secret', 'api_key', 'access_token', 'refresh_token'];
        foreach ($data as $key => &$value) {
            if (in_array(strtolower($key), $sensitive)) {
                $value = '***REDACTED***';
            }
        }
        return $data;
    }
}
