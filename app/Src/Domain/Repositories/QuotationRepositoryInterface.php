<?php

namespace App\Src\Domain\Repositories;

use App\Src\Domain\Entities\Quotation;

/**
 * Repository interface for Quotation aggregate persistence.
 *
 * Defines the contract for storing and retrieving Quotation entities.
 * Implementations can use Eloquent, Doctrine, or any other data source.
 */
interface QuotationRepositoryInterface
{
    /**
     * Finds a quotation by its primary identifier.
     *
     * @param int $id The quotation ID
     * @return Quotation|null The quotation entity or null if not found
     */
    public function findById(int $id): ?Quotation;

    /**
     * Persists a quotation entity (insert or update).
     *
     * @param Quotation $quotation The quotation entity to save
     */
    public function save(Quotation $quotation): void;

    /**
     * Finds the latest quotation associated with a given prospect.
     * Used to retrieve the most recent version of a prospect's quotation.
     *
     * @param int $prospectId The prospect ID
     * @return Quotation|null The latest quotation or null if none exist
     */
    public function findLatestByProspect(int $prospectId): ?Quotation;
}
