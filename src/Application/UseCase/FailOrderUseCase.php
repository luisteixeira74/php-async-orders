<?php

namespace App\Application\UseCase;

use App\Domain\Exception\OrderNotFoundException;
use App\Domain\Repository\OrderRepository;
use App\Domain\Repository\OrderEventRepository;

final class FailOrderUseCase
{
    public function __construct(
        private OrderRepository $repository,
        private OrderEventRepository $eventRepository
    ) {}

    public function execute(string $orderId): void
    {
        $order = $this->repository->findById($orderId);

        if (!$order) {
            throw new OrderNotFoundException();
        }

        $order->markFailed();

        // registra evento
        $this->eventRepository->save($order->getId(), 'failed');

        $this->repository->save($order);
    }
}