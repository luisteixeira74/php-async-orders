<?php

namespace App\Application\UseCase;

use App\Domain\Exception\OrderNotFoundException;
use App\Domain\Repository\OrderRepository;

final class FailOrderUseCase
{
    public function __construct(
        private OrderRepository $repository
    ) {}

    public function execute(string $orderId): void
    {
        $order = $this->repository->findById($orderId);

        if (!$order) {
            throw new OrderNotFoundException();
        }

        $order->markFailed();

        $this->repository->save($order);
    }
}
