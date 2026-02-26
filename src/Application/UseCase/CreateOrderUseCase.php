<?php

namespace App\Application\UseCase;

use App\Domain\Entity\Order;
use App\Domain\Repository\OrderRepository;
use App\Application\Event\EventDispatcherInterface;

class CreateOrderUseCase
{
    public function __construct(
        private OrderRepository $orderRepository,
        private EventDispatcherInterface $eventDispatcher
    ) {}

    public function execute(int $customerId, float $total): string
    {
        $order = Order::create(
            customerId: $customerId,
            total: $total
        );

        $this->orderRepository->save($order);

        foreach ($order->pullDomainEvents() as $event) {
            $this->eventDispatcher->dispatch($event);
        }

        return $order->getId();
    }
}