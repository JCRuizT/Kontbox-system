<?php

namespace App\Src\Domain\Repositories;

use App\Src\Domain\Entities\Contract;

/**
 * Interfaz de repositorio para la persistencia del agregado Contract.
 *
 * Define el contrato para almacenar y recuperar entidades de Contract.
 * Las implementaciones pueden usar Eloquent, Doctrine o cualquier otra fuente de datos.
 */
interface ContractRepositoryInterface
{
    /**
     * Busca un contract por su identificador primario.
     *
     * @param int $id El ID de contract
     * @return Contract|null La entidad de contract o null si no se encuentra
     */
    public function findById(int $id): ?Contract;

    /**
     * Persiste una entidad de contract (insertar o actualizar).
     *
     * @param Contract $contract The contract entity to save
     */
    public function save(Contract $contract): void;

    /**
     * Recupera todos los contratos que coinciden con un estado dado.
     *
     * @param string $status El valor del estado del contrato por el cual filtrar
     * @return Contract[] Array de entidades de contract coincidentes
     */
    public function findByStatus(string $status): array;
}
