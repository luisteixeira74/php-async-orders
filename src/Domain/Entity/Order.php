<?php

namespace App\Domain\Entity;

use App\Domain\Enum\OrderStatus;
use App\Domain\Exception\InvalidOrderStateException;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;
use App\Domain\Event\DomainEvent;
use App\Domain\Event\OrderProcessed;
use App\Domain\Event\OrderReceived;

class Order
{
    private string $id;
    private int $customerId;
    private float $total;
    private OrderStatus $status;
    private DateTimeImmutable $createdAt;
    private array $domainEvents = [];

    private function __construct(
        string $id,
        int $customerId,
        float $total,
        OrderStatus $status = OrderStatus::RECEIVED,
        ?DateTimeImmutable $createdAt = null
    ) {
        $this->id = $id;
        $this->customerId = $customerId;
        $this->total = $total;
        $this->status = $status;
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
    }

    public static function create(int $customerId, float $total): self
    {
        $order = new self(
            id: self::generateId(),
            customerId: $customerId,
            total: $total,
            status: OrderStatus::RECEIVED,
            createdAt: new DateTimeImmutable()
        );

        $order->recordEvent(
            new OrderReceived(
                orderId: $order->getId(),
                customerId: $order->getCustomerId(),
                total: $order->getTotal()
            )
        );

        return $order;
    }

    public static function rehydrate(
        string $id,
        int $customerId,
        float $total,
        OrderStatus $status,
        DateTimeImmutable $createdAt
    ): self {
        return new self(
            id: $id,
            customerId: $customerId,
            total: $total,
            status: $status,
            createdAt: $createdAt
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCustomerId(): int
    {
        return $this->customerId;
    }

    public function getTotal(): float
    {
        return $this->total;
    }

    public function getStatus(): OrderStatus
    {
        return $this->status;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function markProcessing(): void
    {
        if ($this->status !== OrderStatus::RECEIVED) {
            throw new InvalidOrderStateException(
                'Only RECEIVED orders can be processed'
            );
        }

        $this->status = OrderStatus::PROCESSING;
    }

    public function markProcessed(): void
    {
        if ($this->status !== OrderStatus::PROCESSING) {
            throw new InvalidOrderStateException(
                'Only PROCESSING orders can be marked as processed'
            );
        }

        $this->status = OrderStatus::PROCESSED;

        $this->recordEvent(
            new OrderProcessed(
                orderId: $this->getId(),
                customerId: $this->getCustomerId(),
                total: $this->getTotal()
            )
        );
    }

    public function markFailed(): void
    {
        if ($this->status !== OrderStatus::PROCESSING) {
            throw new InvalidOrderStateException();
        }

        $this->status = OrderStatus::FAILED;
    }

    private static function generateId(): string
    {
        return Uuid::uuid7()->toString();
    }

    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }

    private function recordEvent(DomainEvent $event): void
    {
        $this->domainEvents[] = $event;
    }
}
