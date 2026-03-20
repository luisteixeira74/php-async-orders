<?php

declare(strict_types=1);

namespace App\Application\Consumer;

use App\Application\Projection\UpdateOrderProjectionHandler;
use App\Domain\Repository\OrderEventRepository;
use InvalidArgumentException;

class OrderProjectionConsumer
{
    public function __construct(
        private UpdateOrderProjectionHandler $handler,
        private OrderEventRepository $eventRepository
    ) {}

    public function consume(array $event): void
    {
        $this->validate($event);

        // idempotência
        if ($this->eventRepository->alreadyExists(
            $event['order_id'],
            $event['status']
        )) {
            return;
        }


        // Atualiza projection (read model)
        $this->handler->handle($event);

        // Persiste histórico do evento
        $this->eventRepository->save(
            $event['order_id'],
            $event['status']
        );

        if ($this->eventRepository->alreadyExists($event['order_id'], $event['status'])) {
            return;
        }
    }

    private function validate(array $event): void
    {
        $requiredFields = [
            'order_id',
            'customer_id',
            'total',
            'status'
        ];

        foreach ($requiredFields as $field) {
            if (!isset($event[$field])) {
                throw new InvalidArgumentException("Missing field: {$field}");
            }
        }

        if ($this->eventRepository->alreadyExists($event['order_id'], $event['status'])) {
            return;
        }
    }
}