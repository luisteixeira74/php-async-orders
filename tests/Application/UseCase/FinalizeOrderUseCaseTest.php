<?php

namespace Tests\Application\UseCase;

use PHPUnit\Framework\TestCase;
use App\Application\UseCase\FinalizeOrderUseCase;
use App\Domain\Entity\Order;
use App\Domain\Enum\OrderStatus;
use App\Domain\Exception\InvalidOrderStateException;
use App\Domain\Exception\OrderNotFoundException;
use App\Infrastructure\Repository\InMemoryOrderRepository;

class FinalizeOrderUseCaseTest extends TestCase
{
    public function test_it_finalizes_an_order_in_processing_state(): void
    {
        $repository = new InMemoryOrderRepository();

        $customerId = 1234;
        $value = 100.0;

        $order = Order::create($customerId, $value);
        $order->markProcessing();

        $repository->save($order);

        $orderId = $order->getId();

        $useCase = new FinalizeOrderUseCase($repository);
        $useCase->execute($orderId);

        $savedOrder = $repository->findById($orderId);

        $this->assertEquals(OrderStatus::PROCESSED, $savedOrder->getStatus());
    }

    public function test_it_throws_exception_when_order_does_not_exist(): void
    {
        $this->expectException(OrderNotFoundException::class);

        $repository = new InMemoryOrderRepository();
        $useCase = new FinalizeOrderUseCase($repository);

        $useCase->execute('non-existing-order');
    }

    public function test_it_throws_exception_when_order_is_not_in_processing_state(): void
    {
        $this->expectException(InvalidOrderStateException::class);

        $repository = new InMemoryOrderRepository();

        $customerId = 1234;
        $value = 100.0;

        $order = Order::create($customerId, $value);
        // Estado inicial: RECEIVED
        $repository->save($order);

        $orderId = $order->getId();

        $useCase = new FinalizeOrderUseCase($repository);
        $useCase->execute($orderId);
    }

}
