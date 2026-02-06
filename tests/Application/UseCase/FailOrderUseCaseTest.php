<?php

namespace Tests\Application\UseCase;

use PHPUnit\Framework\TestCase;
use App\Application\UseCase\FailOrderUseCase;
use App\Infrastructure\Repository\InMemoryOrderRepository;
use App\Domain\Exception\OrderNotFoundException;
use App\Domain\Exception\InvalidOrderStateException;
use App\Domain\Entity\Order;
use App\Domain\Enum\OrderStatus;

class FailOrderUseCaseTest extends TestCase
{
    public function test_it_fails_an_order_in_processing_state(): void
    {
        $repository = new InMemoryOrderRepository();

        $order = Order::create(1234, 100.0);
        $order->markProcessing();

        $repository->save($order);

        $useCase = new FailOrderUseCase($repository);
        $useCase->execute($order->getId());

        $failedOrder = $repository->findById($order->getId());

        $this->assertEquals(OrderStatus::FAILED, $failedOrder->getStatus());
    }

    public function test_it_throws_exception_when_order_does_not_exist(): void
    {
        $this->expectException(OrderNotFoundException::class);

        $repository = new InMemoryOrderRepository();

        $useCase = new FailOrderUseCase($repository);
        $useCase->execute('non-existing-id');
    }

    public function test_it_throws_exception_when_order_is_not_in_processing_state(): void
    {
        $this->expectException(InvalidOrderStateException::class);

        $repository = new InMemoryOrderRepository();

        $order = Order::create(1234, 100.0);
        // estado RECEIVED
        $repository->save($order);

        $useCase = new FailOrderUseCase($repository);
        $useCase->execute($order->getId());
    }

}