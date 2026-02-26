<?php

namespace App\Application\UseCase;

use App\Domain\Repository\OrderRepository;
use App\Domain\Exception\OrderNotFoundException;
class ProcessOrderUseCase
{
    public function __construct(
        private OrderRepository $orderRepository
    ) {}

    public function execute(string $orderId): void
    {
        $order = $this->orderRepository->findById($orderId);

        if (!$order) {
            throw new OrderNotFoundException();
        }

        $order->markProcessing();
        $order->markProcessed();

        $this->orderRepository->save($order);
    }
}
