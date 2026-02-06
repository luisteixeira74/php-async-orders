<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Order;
use App\Domain\Repository\OrderRepository;

class InMemoryOrderRepository implements OrderRepository
{
    private array $orders = [];

    public function save(Order $order): void
    {
        $this->orders[$order->getId()] = $order;
    }

    public function findById(string $id): ?Order
    {
        return $this->orders[$id] ?? null;
    }
}
