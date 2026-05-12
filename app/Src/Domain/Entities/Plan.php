<?php
namespace App\Src\Domain\Entities;

class Plan
{
    /** @param array<int, array{microservice_id: int, custom_price: ?float}> $services */
    public function __construct(
        private ?int $id,
        private string $name,
        private ?string $description,
        private bool $isActive,
        private array $services = [],
        private bool $isCustom = false,
        private ?int $parentPlanId = null,
    ) {}
    /**
     * Retorna el identificador interno del plan.
     */

    public function id(): ?int
    {
        return $this->id;
    }
    /**
     * Retorna el nombre del plan.
     */

    public function name(): string
    {
        return $this->name;
    }
    /**
     * Retorna la descripci\u00f3n del plan, o null si no tiene.
     */

    public function description(): ?string
    {
        return $this->description;
    }
    /**
     * Retorna si el plan est\u00e1 activo.
     */

    public function isActive(): bool
    {
        return $this->isActive;
    }
    /**
     * Retorna la colecci\u00f3n de servicios del plan.
     */

    public function services(): array
    {
        return $this->services;
    }
    /**
     * Retorna si el plan es personalizado para un cliente.
     */

    public function isCustom(): bool
    {
        return $this->isCustom;
    }
    /**
     * Retorna el ID del plan original si es personalizado, o null.
     */

    public function parentPlanId(): ?int
    {
        return $this->parentPlanId;
    }
    /**
     * Desactiva el plan (baja l\u00f3gica).
     */

    public function deactivate(): void
    {
        $this->isActive = false;
    }
    /**
     * Reactiva el plan previamente desactivado.
     */

    public function activate(): void
    {
        $this->isActive = true;
    }
}
