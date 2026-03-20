<?php

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;
use PDO;
use App\Application\Projection\UpdateOrderProjectionHandler;
use App\Application\Consumer\OrderProjectionConsumer;
use App\Infrastructure\Persistence\PDOOrderProjectionRepository;
use App\Infrastructure\Persistence\InMemoryOrderEventRepository;

class ProjectionFlowTest extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec("
                CREATE TABLE order_projection (
                order_id TEXT PRIMARY KEY,
                customer_id INTEGER NOT NULL,
                total REAL NOT NULL,
                status TEXT NOT NULL,
                created_at TEXT NOT NULL
            )
        ");
    }

    public function test_projection_is_updated_after_event(): void
    {
        $repository = new PDOOrderProjectionRepository($this->pdo);
        $handler = new UpdateOrderProjectionHandler($repository);
        $eventRepository = new InMemoryOrderEventRepository();

        $consumer = new OrderProjectionConsumer($handler, $eventRepository);

        $event = [
            'order_id' => 'order-test-1',
            'customer_id' => 10,
            'total' => 150.00,
            'status' => 'received' // obrigatório
        ];

        $consumer->consume($event);

        $stmt = $this->pdo->query("
            SELECT order_id, customer_id, total
            FROM order_projection
            WHERE order_id = 'order-test-1'
        ");

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals('order-test-1', $result['order_id']);
        $this->assertEquals(10, $result['customer_id']);
        $this->assertEquals(150.00, $result['total']);
    }
}
