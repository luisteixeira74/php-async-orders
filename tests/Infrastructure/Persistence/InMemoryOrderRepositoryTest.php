<?php

namespace Tests\Infrastructure\Persistence;

use PHPUnit\Framework\TestCase;
use App\Domain\Entity\Order;
use App\Infrastructure\Persistence\InMemoryOrderRepository;

class InMemoryOrderRepositoryTest extends TestCase
{
    private InMemoryOrderRepository $repository;

    protected function setUp(): void
    {
        $this->repository = new InMemoryOrderRepository();
    }

    public function testItSavesAndFindsOrderById(): void
    {
        $order = Order::create(1, 150.0);

        $this->repository->save($order);

        $found = $this->repository->findById($order->getId());

        $this->assertNotNull($found);
        $this->assertEquals($order->getId(), $found->getId());
        $this->assertEquals(150.0, $found->getTotal());
    }

    public function testItReturnsNullWhenOrderDoesNotExist(): void
    {
        $result = $this->repository->findById('non_existing_id');

        $this->assertNull($result);
    }

    public function testItUpdatesOrderCorrectly(): void
    {
        $order = Order::create(1, 200.0);
        $this->repository->save($order);

        $order->markProcessing();
        $this->repository->save($order);

        $updated = $this->repository->findById($order->getId());

        $this->assertEquals(
            $order->getStatus(),
            $updated->getStatus()
        );
    }
}
