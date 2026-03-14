<?php

namespace App\Application\UseCase;

use App\Domain\Repository\OrderRepository;
use App\Domain\Exception\OrderNotFoundException;
use App\Application\Event\EventDispatcherInterface;
use App\Domain\Event\OrderProcessed;

class ProcessOrderUseCase
{
    public function __construct(
        private OrderRepository $orderRepository,
        private EventDispatcherInterface $eventDispatcher
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

        $this->eventDispatcher->dispatch(
            new OrderProcessed(
                orderId: $order->getId(),
                customerId: $order->getCustomerId(),
                total: $order->getTotal()
            )
        );
    }
}