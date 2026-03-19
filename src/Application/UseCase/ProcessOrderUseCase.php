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

    /**
     * Processa uma order específica pelo ID
     */
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

    /**
     * Retorna todas as orders pendentes (status ainda não processadas)
     *
     * @return array<int, \App\Domain\Entity\Order>
     */
    public function getPendingOrders(): array
    {
        // Delegamos para o repository buscar todas as orders com status "created"
        return $this->orderRepository->findByStatus('received');
    }
}