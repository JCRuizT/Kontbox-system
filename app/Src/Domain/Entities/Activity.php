<?php
namespace App\Src\Domain\Entities;

class Activity
{
    public function __construct(
        private ?int $id,
        private int $microserviceId,
        private string $name,
        private ?string $description,
        private bool $isActive,
        private bool $isEssential,
    ) {}
    /**
     * Retorna el identificador interno de la actividad.
     */

    public function id(): ?int
    {
        return $this->id;
    }
    /**
     * Retorna el ID del microservicio padre.
     */

    public function microserviceId(): int
    {
        return $this->microserviceId;
    }
    /**
     * Retorna el nombre de la actividad.
     */

    public function name(): string
    {
        return $this->name;
    }
    /**
     * Retorna la descripci\u00f3n de la actividad, o null si no tiene.
     */

    public function description(): ?string
    {
        return $this->description;
    }
    /**
     * Retorna si la actividad est\u00e1 activa.
     */

    public function isActive(): bool
    {
        return $this->isActive;
    }
    /**
     * Retorna si la actividad es esencial (no puede desactivarse).
     */

    public function isEssential(): bool
    {
        return $this->isEssential;
    }
    /**
     * Verifica si la actividad puede ser desactivada (no es esencial).
     */

    public function canBeDeactivated(): bool
    {
        return ! $this->isEssential;
    }
    /**
     * Desactiva la actividad. Lanza DomainException si es esencial.
     */

    public function deactivate(): void
    {
        if (! $this->canBeDeactivated()) {
            throw new \DomainException('domain.activity.essential_cannot_deactivate');
        }
        $this->isActive = false;
    }
    /**
     * Reactiva la actividad previamente desactivada.
     */

    public function activate(): void
    {
        $this->isActive = true;
    }
}
