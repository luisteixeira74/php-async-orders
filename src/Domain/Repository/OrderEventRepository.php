<?php

namespace App\Domain\Repository;

interface OrderEventRepository
{
    public function save(string $orderId, string $eventType): void;

    public function findByOrderId(string $orderId): array;

    public function alreadyExists(string $orderId, string $eventType): bool;
}