<?php
namespace App\Src\Domain\Entities;

class Prospect
{
    public function __construct(
        private ?int $id,
        private string $companyName,
        private string $contactName,
        private string $email,
        private ?string $phone,
        private string $status,
        private ?string $notes,
        private int $createdBy,
    ) {}
    /**
     * Retorna el identificador interno del prospecto.
     */

    public function id(): ?int
    {
        return $this->id;
    }
    /**
     * Retorna el nombre de la empresa del prospecto.
     */

    public function companyName(): string
    {
        return $this->companyName;
    }
    /**
     * Retorna el nombre del contacto del prospecto.
     */

    public function contactName(): string
    {
        return $this->contactName;
    }
    /**
     * Retorna el correo electr\u00f3nico del prospecto.
     */

    public function email(): string
    {
        return $this->email;
    }
    /**
     * Retorna el tel\u00e9fono del prospecto, o null si no tiene.
     */

    public function phone(): ?string
    {
        return $this->phone;
    }
    /**
     * Retorna el estado comercial del prospecto.
     */

    public function status(): string
    {
        return $this->status;
    }
    /**
     * Retorna las notas del prospecto, o null si no hay.
     */

    public function notes(): ?string
    {
        return $this->notes;
    }
    /**
     * Retorna el ID del usuario que cre\u00f3 el prospecto.
     */

    public function createdBy(): int
    {
        return $this->createdBy;
    }
}
