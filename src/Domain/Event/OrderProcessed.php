<?php

namespace App\Domain\Event;

use DateTimeImmutable;

final class OrderProcessed implements DomainEvent
{
    private DateTimeImmutable $occurredOn;

    public function __construct(
        public readonly string $orderId,
        public readonly int $customerId,
        public readonly float $total
    ) {
        $this->occurredOn = new DateTimeImmutable();
    }

    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }

    public function eventName(): string
    {
        return 'orders.processed';
    }

    public function toArray(): array
    {
        return [
            'order_id' => $this->orderId,
            'customer_id' => $this->customerId,
            'total' => $this->total,
            'occurred_on' => $this->occurredOn->format(DATE_ATOM),
        ];
    }
}