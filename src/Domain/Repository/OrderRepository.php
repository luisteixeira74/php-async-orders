<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Order;

interface OrderRepository
{
    public function save(Order $order): void;

    public function findById(string $id): ?Order;
}
