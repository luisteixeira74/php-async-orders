<?php

namespace Tests\Application\UseCase;

use PHPUnit\Framework\TestCase;
use App\Application\UseCase\ProcessOrderUseCase;
use App\Domain\Entity\Order;
use App\Domain\Enum\OrderStatus;
use App\Infrastructure\Repository\InMemoryOrderRepository;

class ProcessOrderUseCaseTest extends TestCase
{
    public function test_it_processes_an_existing_order(): void
    {
        // Arrange
        $orderRepository = new InMemoryOrderRepository();

        $order = Order::create(
            customerId: 99,
            total: 120.00
        );

        $orderRepository->save($order);

        $useCase = new ProcessOrderUseCase($orderRepository);

        // Act
        $useCase->execute($order->getId());

        // Assert
        $processedOrder = $orderRepository->findById($order->getId());

        $this->assertEquals(
            OrderStatus::PROCESSING,
            $processedOrder->getStatus()
        );
    }
}
