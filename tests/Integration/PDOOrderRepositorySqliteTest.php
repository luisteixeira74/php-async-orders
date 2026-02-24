<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use App\Domain\Entity\Order;
use App\Domain\Exception\OrderNotFoundException;
use App\Infrastructure\Persistence\PDOOrderRepository;
use PDO;

class PDOOrderRepositorySqliteTest extends TestCase
{
    private PDO $pdo;
    private PDOOrderRepository $repository;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec("
            CREATE TABLE orders (
                id TEXT PRIMARY KEY,
                customer_id INTEGER NOT NULL,
                total REAL NOT NULL,
                status TEXT NOT NULL,
                created_at TEXT NOT NULL
            )
        ");

        $this->repository = new PDOOrderRepository($this->pdo);
    }

    public function testItPersistsAndRetrievesOrder(): void
    {
        $order = Order::create(1, 300.0);

        $this->repository->save($order);

        $found = $this->repository->findById($order->getId());

        $this->assertEquals($order->getId(), $found->getId());
        $this->assertEquals($order->getCustomerId(), $found->getCustomerId());
        $this->assertEquals($order->getTotal(), $found->getTotal());
        $this->assertEquals($order->getStatus(), $found->getStatus());
        $this->assertEquals(
            $order->getCreatedAt()->format('Y-m-d H:i:s'),
            $found->getCreatedAt()->format('Y-m-d H:i:s')
        );
    }

    public function testItUpdatesExistingOrder(): void
    {
        $order = Order::create(1, 100.0);
        $this->repository->save($order);

        $order->markProcessing();
        $this->repository->save($order);

        $updated = $this->repository->findById($order->getId());

        $this->assertEquals($order->getStatus(), $updated->getStatus());
    }

    public function testItThrowsExceptionWhenOrderNotFound(): void
    {
        $this->expectException(OrderNotFoundException::class);

        $this->repository->findById('non-existing-id');
    }
}