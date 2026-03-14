<?php

namespace Tests\Domain\Entity;

use PHPUnit\Framework\TestCase;
use App\Domain\Entity\Order;
use App\Domain\Enum\OrderStatus;
use App\Domain\Exception\InvalidOrderStateException;

class OrderTest extends TestCase
{
    public function testItCreatesOrderWithReceivedStatus(): void
    {
        $order = Order::create(
            customerId: 1,
            total: 100.0
        );

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals(OrderStatus::RECEIVED, $order->getStatus());
        $this->assertEquals(1, $order->getCustomerId());
        $this->assertEquals(100.0, $order->getTotal());
        $this->assertNotEmpty($order->getId());
        $this->assertNotNull($order->getCreatedAt());
    }

    public function test_order_moves_from_received_to_processing(): void
    {
        $order = Order::create(1, 100.0);

        $order->markProcessing();

        $this->assertEquals(OrderStatus::PROCESSING, $order->getStatus());
    }

    public function test_order_moves_from_received_to_processing_to_processed(): void
    {
        $order = Order::create(1, 100.0);
        $order->markProcessing();

        $order->markProcessed();

        $this->assertEquals(OrderStatus::PROCESSED, $order->getStatus());
    }

    public function testItTransitionsFromProcessingToFailed(): void
    {
        $order = Order::create(1, 100.0);
        $order->markProcessing();

        $order->markFailed();

        $this->assertEquals(OrderStatus::FAILED, $order->getStatus());
    }

    public function testItCannotProcessIfNotReceived(): void
    {
        $order = Order::create(1, 100.0);
        $order->markProcessing();
        $order->markProcessed();

        $this->expectException(InvalidOrderStateException::class);

        $order->markProcessing();
    }
    
    public function testProcessedOrderCannotChangeState(): void
    {
        $order = Order::create(1, 100.0);
        $order->markProcessing();
        $order->markProcessed();

        $this->expectException(InvalidOrderStateException::class);

        $order->markFailed();
    }

    public function testItCannotMarkProcessedIfNotProcessing(): void
    {
        $order = Order::create(1, 100.0);

        $this->expectException(InvalidOrderStateException::class);

        $order->markProcessed();
    }

    public function testItCannotFailIfNotProcessing(): void
    {
        $order = Order::create(1, 100.0);

        $this->expectException(InvalidOrderStateException::class);

        $order->markFailed();
    }

    public function testRehydrateRestoresStateCorrectly(): void
    {
        $date = new \DateTimeImmutable('2024-01-01 10:00:00');

        $order = Order::rehydrate(
            id: 'order_test',
            customerId: 2,
            total: 250.0,
            status: OrderStatus::PROCESSED,
            createdAt: $date
        );

        $this->assertEquals('order_test', $order->getId());
        $this->assertEquals(2, $order->getCustomerId());
        $this->assertEquals(250.0, $order->getTotal());
        $this->assertEquals(OrderStatus::PROCESSED, $order->getStatus());
        $this->assertEquals($date, $order->getCreatedAt());
    }
}
