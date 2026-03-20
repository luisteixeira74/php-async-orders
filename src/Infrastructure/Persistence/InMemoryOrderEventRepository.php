<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Repository\OrderEventRepository;

class InMemoryOrderEventRepository implements OrderEventRepository
{
    private array $events = [];

    public function save(string $orderId, string $eventType): void
    {
        $this->events[] = [
            'order_id' => $orderId,
            'event_type' => $eventType,
            'created_at' => date('Y-m-d H:i:s'),
        ];
    }

    public function findByOrderId(string $orderId): array
    {
        return array_values(array_filter(
            $this->events,
            fn ($event) => $event['order_id'] === $orderId
        ));
    }

    public function alreadyExists(string $orderId, string $eventType): bool
    {
        foreach ($this->events as $event) {
            if (
                $event['order_id'] === $orderId &&
                $event['event_type'] === $eventType
            ) {
                return true;
            }
        }

        return false;
    }
}