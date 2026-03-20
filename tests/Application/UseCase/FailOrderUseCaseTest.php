<?php

namespace Tests\Application\UseCase;

use PHPUnit\Framework\TestCase;
use App\Application\UseCase\FailOrderUseCase;
use App\Infrastructure\Persistence\InMemoryOrderRepository;
use App\Domain\Exception\OrderNotFoundException;
use App\Domain\Exception\InvalidOrderStateException;
use App\Domain\Entity\Order;
use App\Domain\Enum\OrderStatus;
use App\Infrastructure\Persistence\InMemoryOrderEventRepository;

class FailOrderUseCaseTest extends TestCase
{
    public function test_it_fails_an_order_in_processing_state(): void
    {
        $repository = new InMemoryOrderRepository();
        $eventRepository = new InMemoryOrderEventRepository();

        $order = Order::create(1234, 100.0);
        $order->markProcessing();

        $repository->save($order);

        $useCase = new FailOrderUseCase(
            $repository,
            $eventRepository
        );
        $useCase->execute($order->getId());

        $failedOrder = $repository->findById($order->getId());

        $this->assertEquals(OrderStatus::FAILED, $failedOrder->getStatus());
    }

    public function test_it_throws_exception_when_order_does_not_exist(): void
    {
        $this->expectException(OrderNotFoundException::class);

        $repository = new InMemoryOrderRepository();
        $eventRepository = new InMemoryOrderEventRepository();

        $useCase = new FailOrderUseCase(
            $repository,
            $eventRepository
        );

        $useCase->execute('non-existing-id');
    }

    public function test_it_throws_exception_when_order_is_not_in_processing_state(): void
    {
        $this->expectException(InvalidOrderStateException::class);

        $repository = new InMemoryOrderRepository();
        $eventRepository = new InMemoryOrderEventRepository();

        $order = Order::create(1234, 100.0);
        // estado RECEIVED
        $repository->save($order);

        $useCase = new FailOrderUseCase(
            $repository,
            $eventRepository
        );
        
        $useCase->execute($order->getId());
    }

}