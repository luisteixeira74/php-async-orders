<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Repository\OrderEventRepository;

class PDOOrderEventRepository implements OrderEventRepository
{
    public function __construct(private \PDO $pdo) {}

    public function save(string $orderId, string $eventType): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO order_events (id, order_id, event_type, created_at)
            VALUES (gen_random_uuid(), :order_id, :event_type, NOW())
        ");

        $stmt->execute([
            ':order_id' => $orderId,
            ':event_type' => $eventType
        ]);
    }

    public function findByOrderId(string $orderId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT event_type, created_at
            FROM order_events
            WHERE order_id = :order_id
            ORDER BY created_at ASC
        ");

        $stmt->execute([':order_id' => $orderId]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function alreadyExists(string $orderId, string $eventType): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT 1
            FROM order_events
            WHERE order_id = :order_id
            AND event_type = :event_type
            LIMIT 1
        ");

        $stmt->execute([
            ':order_id' => $orderId,
            ':event_type' => $eventType
        ]);

        return (bool) $stmt->fetchColumn();
    }
}