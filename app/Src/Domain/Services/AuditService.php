<?php

namespace App\Src\Domain\Services;

use Spatie\Activitylog\Models\Activity;

/**
 * Servicio central de auditoría. Registra cualquier evento relevante del sistema
 * usando la librería spatie/laravel-activitylog. Cada entrada se categoriza para
 * facilitar su filtrado y consulta posterior.
 */
class AuditService
{
    /** Eventos del sistema (inicio/cierre de sesión) */
    const APP = 'app';
    /** Operaciones de datos (crear, actualizar, desactivar) */
    const CRUD = 'crud';
    /** Flujos de negocio (cambios de estado, aprobaciones) */
    const BUSINESS = 'business';
    /** Operaciones fallidas */
    const ERROR = 'error';

    /** Campos que nunca deben almacenarse en los logs por seguridad */
    const SENSITIVE_KEYS = [
        'password', 'password_confirmation', 'remember_token',
        'api_token', 'two_factor_secret', 'two_factor_recovery_codes',
        'secret', 'token', 'private_key',
    ];

    /**
     * Método base: escribe una entrada en el log de actividad.
     * Enriquece automáticamente con IP, user-agent, método HTTP, URL y categoría.
     * Redacta campos sensibles antes de persistir.
     */
    public static function log(string $description, $subject = null, array $properties = [], string $logName = self::APP): void
    {
        $request = request();

        // Redact sensitive fields from properties
        foreach (self::SENSITIVE_KEYS as $key) {
            if (isset($properties[$key])) {
                $properties[$key] = '[REDACTED]';
            }
        }

        $log = activity($logName)
            ->withProperties(array_merge($properties, [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'category' => $logName,
            ]));

        if ($subject) {
            $log->performedOn($subject);
        }

        if (auth()->check()) {
            $log->causedBy(auth()->user());
        }

        $log->log($description);
    }

    /**
     * Registra la creación de una entidad. Excluye automáticamente campos sensibles.
     */
    public static function logCreate($subject, string $entityName, array $data): void
    {
        $safe = array_diff_key($data, array_flip(self::SENSITIVE_KEYS));
        self::log(
            "Creó {$entityName} #{$subject->id}",
            $subject,
            ['action' => 'create', 'entity' => $entityName, 'new_values' => $safe],
            self::CRUD
        );
    }

    /**
     * Compara valores originales vs cambios usando getChanges() de Eloquent.
     * Ignora timestamps y campos sensibles. Solo persiste si hay diferencias reales.
     */
    public static function logUpdate($subject, string $entityName, array $original, array $changes): void
    {
        $diff = [];
        $skipKeys = array_merge(self::SENSITIVE_KEYS, ['updated_at', 'created_at', 'deleted_at']);
        foreach ($changes as $key => $newVal) {
            if (in_array($key, $skipKeys)) continue;
            $oldVal = array_key_exists($key, $original) ? $original[$key] : null;
            // Comparación flexible: string vs string, ignorando diferencias de formato numérico
            $strNew = is_bool($newVal) ? ($newVal ? '1' : '0') : (string)$newVal;
            $strOld = is_bool($oldVal) ? ($oldVal ? '1' : '0') : (string)$oldVal;
            if ($strNew !== $strOld) {
                $diff[$key] = ['from' => $oldVal, 'to' => $newVal];
            }
        }
        if (empty($diff)) return;

        self::log(
            "Actualizó {$entityName} #{$subject->id}",
            $subject,
            ['action' => 'update', 'entity' => $entityName, 'changes' => $diff],
            self::CRUD
        );
    }

    /**
     * Registra la desactivación (soft delete) de una entidad.
     */
    public static function logDelete($subject, string $entityName): void
    {
        self::log(
            "Desactivó {$entityName} #{$subject->id}",
            $subject,
            ['action' => 'delete', 'entity' => $entityName],
            self::CRUD
        );
    }

    /**
     * Registra un cambio de estado en una entidad (ej: pendiente → activo).
     */
    public static function logStatusChange($subject, string $entityName, string $from, string $to): void
    {
        self::log(
            "Cambió estado de {$entityName} #{$subject->id}: {$from} → {$to}",
            $subject,
            ['action' => 'status_change', 'entity' => $entityName, 'from' => $from, 'to' => $to],
            self::BUSINESS
        );
    }

    /**
     * Registra eventos de negocio personalizados (aprobaciones, flujos complejos, etc.).
     */
    public static function logBusiness(string $description, $subject = null, array $extraProps = []): void
    {
        self::log($description, $subject, $extraProps, self::BUSINESS);
    }

    /**
     * Registra errores y operaciones fallidas con contexto adicional.
     */
    public static function logError(string $description, array $context = []): void
    {
        self::log($description, null, ['context' => $context, 'action' => 'error'], self::ERROR);
    }
}
