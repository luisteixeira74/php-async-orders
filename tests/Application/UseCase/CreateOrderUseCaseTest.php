<?php

namespace Tests\Application\UseCase;

use App\Application\Event\SimpleEventDispatcher;
use App\Application\UseCase\CreateOrderUseCase;
use App\Domain\Enum\OrderStatus;
use App\Infrastructure\Messaging\InMemoryQueuePublisher;
use App\Infrastructure\Persistence\InMemoryOrderRepository;
use PHPUnit\Framework\TestCase;
use App\Application\Listener\PublishOrderToQueueListener;
use App\Domain\Event\OrderReceived;

class CreateOrderUseCaseTest extends TestCase
{
    public function test_it_creates_order_and_publishes_event(): void
    {
        // Arrange
        $orderRepository = new InMemoryOrderRepository();
        $queuePublisher = new InMemoryQueuePublisher();
        
        $eventDispatcher  = new SimpleEventDispatcher();
        $eventDispatcher->listen(
            OrderReceived::class,
            new PublishOrderToQueueListener($queuePublisher)
        );

        $useCase = new CreateOrderUseCase(
            $orderRepository,
            $eventDispatcher
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

        $this->assertEquals('orders.received', $message['topic']);
        $this->assertEquals($orderId, $message['payload']['order_id']);
    }
    
    public function test_multiple_orders_generate_unique_ids(): void
    {
        $orderRepository = new InMemoryOrderRepository();
        $eventDispatcher  = new SimpleEventDispatcher();

        $useCase = new CreateOrderUseCase(
            $orderRepository,
            $eventDispatcher
        );

        $id1 = $useCase->execute(1, 100.0);
        $id2 = $useCase->execute(2, 200.0);

        $this->assertNotEquals($id1, $id2);
    }
}
