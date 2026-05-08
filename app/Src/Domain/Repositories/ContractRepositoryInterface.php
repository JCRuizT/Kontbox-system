<?php

namespace App\Src\Domain\Repositories;

use App\Src\Domain\Entities\Contract;

/**
 * Repository interface for Contract aggregate persistence.
 *
 * Defines the contract for storing and retrieving Contract entities.
 * Implementations can use Eloquent, Doctrine, or any other data source.
 */
interface ContractRepositoryInterface
{
    /**
     * Finds a contract by its primary identifier.
     *
     * @param int $id The contract ID
     * @return Contract|null The contract entity or null if not found
     */
    public function findById(int $id): ?Contract;

    /**
     * Persists a contract entity (insert or update).
     *
     * @param Contract $contract The contract entity to save
     */
    public function save(Contract $contract): void;

    /**
     * Retrieves all contracts matching a given status.
     *
     * @param string $status The contract status value to filter by
     * @return Contract[] Array of matching contract entities
     */
    public function findByStatus(string $status): array;
}
