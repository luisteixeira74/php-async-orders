<?php

declare(strict_types=1);

namespace App\Application\Consumer;

use App\Application\Projection\UpdateOrderProjectionHandler;
use App\Domain\Repository\OrderRepository;

class OrderProjectionConsumer
{
    public function __construct(
        private UpdateOrderProjectionHandler $handler,
        private OrderRepository $orderRepository
    ) {}

    public function consume(): void
    {
        $orders = $this->orderRepository->findByStatus('processed');

        foreach ($orders as $order) {
            $this->handler->handle([
                'order_id'    => $order->getId(),
                'customer_id' => $order->getCustomerId(),
                'total'       => $order->getTotal(),
                'status'      => $order->getStatus()->value,
                'created_at'  => $order->getCreatedAt()->format('Y-m-d H:i:s'),
            ]);
        }
    }
}   