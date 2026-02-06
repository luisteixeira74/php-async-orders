<?php

namespace App\Domain\Entity;

use App\Domain\Enum\OrderStatus;
use App\Domain\Exception\InvalidOrderStateException;
use DateTimeImmutable;

class Order
{
    private string $id;
    private int $customerId;
    private float $total;
    private OrderStatus $status;
    private DateTimeImmutable $createdAt;

    public function __construct(string $id, int $customerId, float $total)
    {
        $this->id = $id;
        $this->customerId = $customerId;
        $this->total = $total;
        $this->status = OrderStatus::RECEIVED;
        $this->createdAt = new DateTimeImmutable();
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
    }

    public function markFailed(): void
    {
        $this->status = OrderStatus::FAILED;
    }

    public static function create(int $customerId, float $total): self
    {
        return new self(
            id: self::generateId(),
            customerId: $customerId,
            total: $total
        );
    }

    private static function generateId(): string
    {
        return uniqid('order_', true);
    }
}
