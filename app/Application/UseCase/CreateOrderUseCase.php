<?php

namespace App\Application\UseCase;

use App\Domain\Entity\Order;
use App\Domain\Repository\OrderRepository;
use App\Infrastructure\Queue\QueuePublisher;

class CreateOrderUseCase
{
    public function __construct(
        private OrderRepository $orderRepository,
        private QueuePublisher $queuePublisher
    ) {}

    public function execute(int $customerId, float $total): string
    {
        $orderId = uniqid('order_', true);

        $order = new Order(
            id: $orderId,
            customerId: $customerId,
            total: $total
        );

        $this->orderRepository->save($order);

        $this->queuePublisher->publish('orders.created', [
            'order_id' => $orderId
        ]);

        return $orderId;
    }
}
