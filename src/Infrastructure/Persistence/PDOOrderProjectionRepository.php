<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use PDO;
use App\Domain\Repository\OrderProjectionRepository;

final class PDOOrderProjectionRepository implements OrderProjectionRepository
{
    public function __construct(
        private PDO $connection
    ) {
    }

    public function upsert(
        string $orderId,
        int $customerId,
        float $total,
        string $status,
        string $createdAt
    ): void {
        $sql = <<<SQL
        INSERT INTO order_projection (
            order_id,
            customer_id,
            total,
            status,
            created_at
        ) VALUES (
            :order_id,
            :customer_id,
            :total,
            :status,
            :created_at
        )
        ON CONFLICT (order_id)
        DO UPDATE SET
            customer_id = EXCLUDED.customer_id,
            total = EXCLUDED.total,
            status = EXCLUDED.status,
            created_at = EXCLUDED.created_at
        SQL;

        $stmt = $this->connection->prepare($sql);

        $stmt->execute([
            ':order_id' => $orderId,
            ':customer_id' => $customerId,
            ':total' => $total,
            ':status' => $status,
            ':created_at' => $createdAt,
        ]);
    }
}