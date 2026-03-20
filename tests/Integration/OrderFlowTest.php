<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use App\Application\UseCase\CreateOrderUseCase;
use App\Application\UseCase\ProcessOrderUseCase;
use App\Domain\Enum\OrderStatus;
use App\Infrastructure\Persistence\InMemoryOrderRepository;
use App\Application\Event\SimpleEventDispatcher;
use App\Infrastructure\Persistence\InMemoryOrderEventRepository;

class OrderFlowTest extends TestCase
{
    public function testCompleteOrderFlow(): void
    {
        $repository = new InMemoryOrderRepository();
        $dispatcher = new SimpleEventDispatcher();
        $eventRepository = new InMemoryOrderEventRepository();

        $create = new CreateOrderUseCase($repository, $dispatcher, $eventRepository);
        $process = new ProcessOrderUseCase($repository, $dispatcher, $eventRepository);

        $orderId = $create->execute(customerId: 1, total: 500.0);

        $process->execute($orderId);

        $order = $repository->findById($orderId);

        $this->assertNotNull($order);
        $this->assertEquals(OrderStatus::PROCESSED, $order->getStatus());
    }
}
