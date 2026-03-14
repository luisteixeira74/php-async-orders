<?php

declare(strict_types=1);

namespace App\Application\Projection;

use App\Domain\Repository\OrderProjectionRepository;
use InvalidArgumentException;
use DateTimeImmutable;

class UpdateOrderProjectionHandler
{
    public function __construct(
        private OrderProjectionRepository $repository
    ) {}

    public function handle(array $event): void
    {
        if (!isset($event['order_id'])) {
            throw new InvalidArgumentException('Invalid event payload: order_id missing');
        }

        $orderId = (string) $event['order_id'];
        $customerId = (int) ($event['customer_id'] ?? 0);
        $total = (float) ($event['total'] ?? 0);
        $status = (string) ($event['status'] ?? 'received');
        $createdAt = (new DateTimeImmutable())->format('Y-m-d H:i:s');

        $this->repository->upsert(
            $orderId,
            $customerId,
            $total,
            $status,
            $createdAt
        );
    }
}