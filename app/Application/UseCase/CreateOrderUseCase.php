<?php

namespace App\Application\UseCase;

use App\Domain\Entity\Order;
use App\Domain\Repository\OrderRepository;
use App\Infrastructure\Messaging\QueuePublisher;

class CreateOrderUseCase
{
    public function __construct(
        private OrderRepository $orderRepository,
        private QueuePublisher $queuePublisher
    ) {}

    public function execute(int $customerId, float $total): string
    {
        $order = Order::create(
            customerId: $customerId,
            total: $total
        );

        $this->orderRepository->save($order);

        $this->queuePublisher->publish('orders.created', [
            'order_id' => $order->getId()
        ]);

        return $order->getId();
    }
}
