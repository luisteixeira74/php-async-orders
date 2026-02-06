<?php

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Order;
use App\Domain\Repository\OrderRepository;
use App\Domain\Exception\OrderNotFoundException;

class InMemoryOrderRepository implements OrderRepository
{
    private array $orders = [];

    public function save(Order $order): void
    {
        $this->orders[$order->getId()] = $order;
    }

    public function findById(string $id): Order
    {
        if (!isset($this->orders[$id])) {
            throw new OrderNotFoundException();
        }

        return $this->orders[$id];
    }
}