<?php

namespace App\Application;

use App\Infrastructure\Persistence\PDOOrderRepository;
use App\Infrastructure\Persistence\ConnectionFactory;

use App\Application\UseCase\CreateOrderUseCase;
use App\Application\UseCase\ProcessOrderUseCase;
use App\Application\UseCase\FailOrderUseCase;

use App\Domain\Repository\OrderRepository;
use App\Infrastructure\Persistence\InMemoryOrderRepository;

use App\Infrastructure\Messaging\QueuePublisher;
use App\Infrastructure\Messaging\InMemoryQueuePublisher;

use Dotenv\Dotenv;

class Bootstrap
{
    private OrderRepository $orderRepository;
    private QueuePublisher $queuePublisher;

    public function __construct(private string $environment = 'dev')
    {
        $this->initialize();
    }

    private function initialize(): void
    {
        $root = dirname(__DIR__, 2);

        if (file_exists($root . '/.env')) {
            $dotenv = Dotenv::createImmutable($root);
            $dotenv->load();
        }

        switch ($this->environment) {
            case 'prod':
                $pdo = ConnectionFactory::create();
                $this->orderRepository = new PDOOrderRepository($pdo);
                // ainda pode manter queue fake até ter mensageria real
                $this->queuePublisher  = new InMemoryQueuePublisher();
                break;

            case 'dev':
            default:
                $this->orderRepository = new InMemoryOrderRepository();
                $this->queuePublisher  = new InMemoryQueuePublisher();
        }
    }

    public function createOrderUseCase(): CreateOrderUseCase
    {
        return new CreateOrderUseCase(
            $this->orderRepository,
            $this->queuePublisher
        );
    }

    public function processOrderUseCase(): ProcessOrderUseCase
    {
        return new ProcessOrderUseCase(
            $this->orderRepository
        );
    }

    public function failOrderUseCase(): FailOrderUseCase
    {
        return new FailOrderUseCase(
            $this->orderRepository
        );
    }
}
