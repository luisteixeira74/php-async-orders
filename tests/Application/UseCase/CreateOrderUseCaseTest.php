<?php

namespace Tests\Application\UseCase;

use PHPUnit\Framework\TestCase;
use App\Application\UseCase\CreateOrderUseCase;
use App\Infrastructure\Repository\InMemoryOrderRepository;
use App\Infrastructure\Queue\InMemoryQueuePublisher;
use App\Domain\Enum\OrderStatus;

class CreateOrderUseCaseTest extends TestCase
{
    public function test_it_creates_order_and_publishes_event(): void
    {
        // Arrange
        $orderRepository = new InMemoryOrderRepository();
        $queuePublisher  = new InMemoryQueuePublisher();

        $useCase = new CreateOrderUseCase(
            $orderRepository,
            $queuePublisher
        );

        // Act
        $orderId = $useCase->execute(
            customerId: 10,
            total: 250.50
        );

        // Assert - Order created
        $order = $orderRepository->findById($orderId);

        $this->assertNotNull($order);
        $this->assertEquals(10, $order->getCustomerId());
        $this->assertEquals(250.50, $order->getTotal());
        $this->assertEquals(OrderStatus::RECEIVED, $order->getStatus());

        // Assert - Message published
        $this->assertCount(1, $queuePublisher->messages);

        $message = $queuePublisher->messages[0];

        $this->assertEquals('orders.created', $message['topic']);
        $this->assertEquals($orderId, $message['payload']['order_id']);
    }
}
