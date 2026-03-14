<?php

namespace App\Domain\Repository;

interface OrderProjectionRepository
{   
    /**
     * Persiste dados da projeção da Order no read model
     */
    public function upsert(
        string $orderId,
        int $customerId,
        float $total,
        string $status,
        string $createdAt
    ): void;
}