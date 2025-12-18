<?php

namespace App\Repository;

/**
 * @deprecated This repository is no longer used because DreamFulfillment entity is deprecated.
 */
class DreamFulfillmentRepository
{
    // Empty class to prevent autoloading errors.

    /**
     * @deprecated This method returns 0 because the donation system has been removed.
     */
    public function count(array $criteria = []): int
    {
        return 0;
    }

    /**
     * @deprecated This method returns an empty array because the donation system has been removed.
     */
    public function findAll(): array
    {
        return [];
    }

    /**
     * @deprecated This method returns null because the donation system has been removed.
     */
    public function find($id, $lockMode = null, $lockVersion = null)
    {
        return null;
    }

    /**
     * @deprecated This method returns an empty array because the donation system has been removed.
     */
    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array
    {
        return [];
    }

    /**
     * @deprecated This method returns null because the donation system has been removed.
     */
    public function findOneBy(array $criteria)
    {
        return null;
    }
}
