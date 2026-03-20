<?php

namespace App\Application\UseCase;

use App\Domain\Entity\Order;
use App\Domain\Repository\OrderRepository;
use App\Application\Event\EventDispatcherInterface;
use App\Domain\Repository\OrderEventRepository;

class CreateOrderUseCase
{
    public function __construct(
        private OrderRepository $orderRepository,
        private EventDispatcherInterface $eventDispatcher,
        private OrderEventRepository $eventRepository
    ) {}

    public function execute(int $customerId, float $total): string
    {
        $order = Order::create(
            customerId: $customerId,
            total: $total
        );

        $this->orderRepository->save($order);

        // salva evento no banco
        $this->eventRepository->save($order->getId(), 'received');

        // mantém seu fluxo atual (event dispatcher)
        foreach ($order->pullDomainEvents() as $event) {
            $this->eventDispatcher->dispatch($event);
        }

        return $order->getId();
    }
}