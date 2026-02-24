<?php

namespace Tests\Application\UseCase;

use PHPUnit\Framework\TestCase;
use App\Application\UseCase\ProcessOrderUseCase;
use App\Domain\Entity\Order;
use App\Domain\Enum\OrderStatus;
use App\Domain\Exception\OrderNotFoundException;
use App\Infrastructure\Persistence\InMemoryOrderRepository;

class ProcessOrderUseCaseTest extends TestCase
{
    public function test_it_processes_an_existing_order(): void
    {
        $orderRepository = new InMemoryOrderRepository();

        $order = Order::create(
            customerId: 99,
            total: 120.00
        );

        $orderRepository->save($order);

        $useCase = new ProcessOrderUseCase($orderRepository);

        $useCase->execute($order->getId());

        $processedOrder = $orderRepository->findById($order->getId());

        $this->assertEquals(
            OrderStatus::PROCESSED,
            $processedOrder->getStatus()
        );
    }

    public function test_it_throws_exception_when_order_does_not_exist(): void
    {
        $orderRepository = new InMemoryOrderRepository();

        $useCase = new ProcessOrderUseCase($orderRepository);

        $this->expectException(OrderNotFoundException::class);

        $useCase->execute('non-existing-id');
    }
}