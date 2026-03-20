<?php

namespace App\Application\UseCase;

use App\Domain\Repository\OrderRepository;
use App\Domain\Exception\OrderNotFoundException;
use App\Application\Event\EventDispatcherInterface;
use App\Domain\Event\OrderProcessed;
use App\Domain\Repository\OrderEventRepository;

class ProcessOrderUseCase
{
    public function __construct(
        private OrderRepository $orderRepository,
        private EventDispatcherInterface $eventDispatcher,
        private OrderEventRepository $eventRepository
    ) {}

    public function execute(string $orderId): void
    {
        $order = $this->orderRepository->findById($orderId);

        if (!$order) {
            throw new OrderNotFoundException();
        }

        // passo 1
        $order->markProcessing();
        $this->eventRepository->save($order->getId(), 'processing');

        // passo 2
        $order->markProcessed();
        $this->eventRepository->save($order->getId(), 'processed');

        $this->orderRepository->save($order);

        $this->eventDispatcher->dispatch(
            new OrderProcessed(
                orderId: $order->getId(),
                customerId: $order->getCustomerId(),
                total: $order->getTotal()
            )
        );
    }

    public function getPendingOrders(): array
    {
        return $this->orderRepository->findByStatus('received');
    }
}