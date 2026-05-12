<?php

namespace App\Src\Domain\ValueObjects;

/**
 * Value Object inmutable que representa un documento PDF firmado cargado en la plataforma.
 *
 * Almacena metadatos del documento: ruta, nombre original y tamaño.
 * La verificación de existencia física del archivo se delega al
 * Use Case o Service de Infraestructura correspondiente.
 */
class SignedPdf
{
    public function __construct(
        private string $path,
        private string $originalName,
        private int $sizeInBytes,
    ) {}
    /**
     * Retorna la ruta de almacenamiento del PDF.
     */


    public function path(): string
{
    return $this->path;
}
    /**
     * Retorna el nombre original del archivo subido.
     */

    public function originalName(): string
{
    return $this->originalName;
}
    /**
     * Retorna el tama\u00f1o del archivo en bytes.
     */

    public function sizeInBytes(): int
{
    return $this->sizeInBytes;
}
}
