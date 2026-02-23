<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Entity\Order;
use App\Domain\Enum\OrderStatus;
use App\Domain\Exception\OrderNotFoundException;
use App\Domain\Repository\OrderRepository;
use DateTimeImmutable;
use PDO;

class PDOOrderRepository implements OrderRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function save(Order $order): void
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO orders (id, customer_id, total, status)
             VALUES (:id, :customer_id, :total, :status)
             ON CONFLICT (id)
             DO UPDATE SET
                 customer_id = :customer_id,
                 total = :total,
                 status = :status"
        );

        $stmt->execute([
            ':id'          => $order->getId(),
            ':customer_id' => $order->getCustomerId(),
            ':total'       => $order->getTotal(),
            ':status'      => $order->getStatus()->value,
        ]);
    }

    public function findById(string $id): Order
    {
        $stmt = $this->pdo->prepare(
            "SELECT id, customer_id, total, status
             FROM orders
             WHERE id = :id"
        );

        $stmt->execute([':id' => $id]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            throw new OrderNotFoundException("Order {$id} not found");
        }

        return Order::rehydrate(
            $data['id'],
            (int) $data['customer_id'],
            (float) $data['total'],
            OrderStatus::from($data['status']),
            new DateTimeImmutable($data['created_at'])
        );
    }
}
