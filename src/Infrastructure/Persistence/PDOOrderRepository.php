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
            "INSERT INTO orders (id, customer_id, total, status, created_at)
             VALUES (:id, :customer_id, :total, :status, :created_at)
             ON CONFLICT (id)
             DO UPDATE SET
                 customer_id = EXCLUDED.customer_id,
                 total       = EXCLUDED.total,
                 status      = EXCLUDED.status"
        );

        $stmt->execute([
            ':id'          => $order->getId(),
            ':customer_id' => $order->getCustomerId(),
            ':total'       => $order->getTotal(),
            ':status'      => $order->getStatus()->value,
            ':created_at'  => $order->getCreatedAt()->format('Y-m-d H:i:s'),
        ]);
    }

    public function findById(string $id): Order
    {
        $stmt = $this->pdo->prepare(
            "SELECT id, customer_id, total, status, created_at
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

    public function findByStatus(string $status): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT id, customer_id, total, status, created_at
            FROM orders
            WHERE status = :status"
        );
        $stmt->execute([':status' => $status]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $orders = [];
        foreach ($rows as $data) {
            $orders[] = Order::rehydrate(
                $data['id'],
                (int) $data['customer_id'],
                (float) $data['total'],
                OrderStatus::from($data['status']),
                new \DateTimeImmutable($data['created_at'])
            );
        }

        return $orders;
    }
}