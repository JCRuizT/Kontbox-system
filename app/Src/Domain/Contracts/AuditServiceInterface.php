<?php
namespace App\Src\Domain\Contracts;

interface AuditServiceInterface
{
    public const APP      = 'app';
    public const CRUD     = 'crud';
    public const BUSINESS = 'business';
    public const ERROR    = 'error';

    public function log(string $description, $subject = null, array $properties = [], string $logName = 'app'): void;
    public function logCreate($subject, string $entityName, array $data = []): void;
    public function logUpdate($subject, string $entityName, array $original, array $changes): void;
    public function logDelete($subject, string $entityName): void;
    public function logStatusChange($subject, string $entityName, string $from, string $to): void;
    public function logBusiness(string $description, $subject = null, array $extraProps = []): void;
    public function logError(string $description, array $context = []): void;
}
