<?php
namespace App\Src\Domain\Entities;

class ContractAmendment
{
    public function __construct(
        private ?int $id,
        private int $contractId,
        private string $amendmentNumber,
        private string $description,
        private ?string $signedPdfPath,
        private ?array $modifiedServices,
        private int $createdBy,
    ) {}
    /**
     * Retorna el identificador interno de la modificaci\u00f3n.
     */

    public function id(): ?int
    {
        return $this->id;
    }
    /**
     * Retorna el ID del contrato asociado.
     */

    public function contractId(): int
    {
        return $this->contractId;
    }
    /**
     * Retorna el n\u00famero \u00fanico de la modificaci\u00f3n (Otros\u00ed).
     */

    public function amendmentNumber(): string
    {
        return $this->amendmentNumber;
    }
    /**
     * Retorna la descripci\u00f3n de la modificaci\u00f3n.
     */

    public function description(): string
    {
        return $this->description;
    }
    /**
     * Retorna la ruta del PDF firmado, o null si no se ha cargado.
     */

    public function signedPdfPath(): ?string
    {
        return $this->signedPdfPath;
    }
    /**
     * Retorna los servicios modificados como array, o null si no hay cambios.
     */

    public function modifiedServices(): ?array
    {
        return $this->modifiedServices;
    }
    /**
     * Retorna el ID del usuario que cre\u00f3 la modificaci\u00f3n.
     */

    public function createdBy(): int
    {
        return $this->createdBy;
    }
}
