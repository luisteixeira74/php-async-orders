<?php

namespace App\Application\UseCase;

use App\Domain\Exception\OrderNotFoundException;
use App\Domain\Repository\OrderRepository;

class ProcessOrderUseCase
{
    public function __construct(
        private OrderRepository $orderRepository
    ) {}

    public function execute(string $orderId): void
    {
        $order = $this->orderRepository->findById($orderId);

        if (!$order) {
            throw new OrderNotFoundException("Order {$orderId} not found");
        }

        $order->markProcessing();

        $this->orderRepository->save($order);
    }
}
