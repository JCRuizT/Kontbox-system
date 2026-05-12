<?php

namespace App\Src\Domain\Services;

use App\Src\Domain\Contracts\AuditServiceInterface;

/**
 * Static facade para el servicio de auditoría.
 *
 * Delega todas las llamadas estáticas a la implementación concreta
 * registrada en el contenedor (App\Src\Infrastructure\Services\SpatieAuditService).
 * Esto permite mantener compatibilidad con todos los llamadores existentes
 * mientras la implementación real reside en Infrastructure.
 */
class AuditService
{
    public const APP = AuditServiceInterface::APP;
    public const CRUD = AuditServiceInterface::CRUD;
    public const BUSINESS = AuditServiceInterface::BUSINESS;
    public const ERROR = AuditServiceInterface::ERROR;

    private static function instance(): AuditServiceInterface
    {
        return app(AuditServiceInterface::class);
    }

    public static function log(string $description, $subject = null, array $properties = [], string $logName = 'app'): void
    {
        self::instance()->log($description, $subject, $properties, $logName);
    }

    public static function logCreate($subject, string $entityName, array $data = []): void
    {
        self::instance()->logCreate($subject, $entityName, $data);
    }

    public static function logUpdate($subject, string $entityName, array $original, array $changes): void
    {
        self::instance()->logUpdate($subject, $entityName, $original, $changes);
    }

    public static function logDelete($subject, string $entityName): void
    {
        self::instance()->logDelete($subject, $entityName);
    }

    public static function logStatusChange($subject, string $entityName, string $from, string $to): void
    {
        self::instance()->logStatusChange($subject, $entityName, $from, $to);
    }

    public static function logBusiness(string $description, $subject = null, array $extraProps = []): void
    {
        self::instance()->logBusiness($description, $subject, $extraProps);
    }

    public static function logError(string $description, array $context = []): void
    {
        self::instance()->logError($description, $context);
    }
}
