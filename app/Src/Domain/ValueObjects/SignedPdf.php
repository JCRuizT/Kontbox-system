<?php

namespace App\Src\Domain\ValueObjects;

use Illuminate\Support\Facades\Storage;

/**
 * Value Object que representa un documento PDF firmado cargado en la plataforma.
 *
 * Immutable representation of a signed PDF document with physical file verification.
 * The document is stored on the local Laravel disk and this VO provides
 * metadata access and existence checks for security validations.
 */
class SignedPdf
{
    /**
     * @param string $path          Storage path relative to the Laravel local disk
     * @param string $originalName  Original filename uploaded by the user
     * @param int    $sizeInBytes   File size in bytes
     */
    public function __construct(
        private string $path,
        private string $originalName,
        private int $sizeInBytes,
    ) {}

    /** Returns the storage path relative to the Laravel local disk. */
    public function path(): string
    {
        return $this->path;
    }

    /** Returns the original filename as uploaded by the user. */
    public function originalName(): string
    {
        return $this->originalName;
    }

    /** Returns the file size in bytes. */
    public function sizeInBytes(): int
    {
        return $this->sizeInBytes;
    }

    /**
     * Verifies whether the PDF file physically exists on the storage disk.
     *
     * Utiliza primero el disco de Laravel (local) para evitar diferencias entre
     * sistemas operativos en las rutas. Como fallback, hace una verificación
     * directa con el sistema de archivos. Esto es crítico para el bloqueo de
     * seguridad que impide activar contratos sin un PDF físicamente presente.
     */
    public function exists(): bool
    {
        if (empty($this->path)) {
            return false;
        }
        // Verifica usando el disco de Laravel (local) para evitar
        // diferencias entre sistemas operativos en las rutas
        if (Storage::disk('local')->exists($this->path)) {
            return true;
        }
        // Fallback: verificación directa con el sistema de archivos
        return file_exists(storage_path("app/{$this->path}"));
    }
}
