<?php

namespace App\Src\Domain\Entities;

class Microservice
{
    public function __construct(
        private ?int $id,
        private string $name,
        private ?string $description,
        private float $baseCost,
        private string $type,
        private bool $isActive,
    ) {}
    /**
     * Retorna el identificador interno del microservicio.
     */


    public function id(): ?int
{
    return $this->id;
}
    /**
     * Retorna el nombre del microservicio.
     */

    public function name(): string
{
    return $this->name;
}
    /**
     * Retorna la descripci\u00f3n del microservicio, o null si no tiene.
     */

    public function description(): ?string
{
    return $this->description;
}
    /**
     * Retorna el costo base del microservicio.
     */

    public function baseCost(): float
{
    return $this->baseCost;
}
    /**
     * Retorna el tipo del microservicio (recurring/one_time).
     */

    public function type(): string
{
    return $this->type;
}
    /**
     * Retorna si el microservicio est\u00e1 activo.
     */

    public function isActive(): bool
{
    return $this->isActive;
}
    /**
     * Desactiva el microservicio (baja l\u00f3gica).
     */


    public function deactivate(): void
    {
        $this->isActive = false;
    }
    /**
     * Reactiva el microservicio previamente desactivado.
     */


    public function activate(): void
    {
        $this->isActive = true;
    }
}
