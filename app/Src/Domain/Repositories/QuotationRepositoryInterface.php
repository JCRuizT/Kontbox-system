<?php

namespace App\Src\Domain\Repositories;

use App\Src\Domain\Entities\Quotation;

/**
 * Interfaz de repositorio para la persistencia del agregado Quotation.
 *
 * Define el contrato para almacenar y recuperar entidades de Quotation.
 * Las implementaciones pueden usar Eloquent, Doctrine o cualquier otra fuente de datos.
 */
interface QuotationRepositoryInterface
{
    /**
     * Busca un quotation por su identificador primario.
     *
     * @param int $id El ID de quotation
     * @return Quotation|null La entidad de quotation o null si no se encuentra
     */
    public function findById(int $id): ?Quotation;

    /**
     * Persiste una entidad de quotation (insertar o actualizar).
     *
     * @param Quotation $quotation The quotation entity to save
     */
    public function save(Quotation $quotation): void;

    /**
     * Busca la cotización más reciente asociada a un prospecto dado.
     * Usado para recuperar la versión más reciente de la cotización de un prospecto.
     *
     * @param int $prospectId The prospect ID
     * @return Quotation|null La cotización más reciente o null si no existe
     */
    public function findLatestByProspect(int $prospectId): ?Quotation;
}
