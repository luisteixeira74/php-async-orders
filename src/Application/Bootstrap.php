<?php

namespace App\Application;

use App\Application\Event\EventDispatcherInterface;
use App\Application\Event\SimpleEventDispatcher;

use App\Application\Listener\PublishOrderToQueueListener;

use App\Domain\Event\OrderReceived;

use App\Application\UseCase\CreateOrderUseCase;
use App\Application\UseCase\ProcessOrderUseCase;
use App\Application\UseCase\FailOrderUseCase;

use App\Domain\Repository\OrderRepository;

use App\Infrastructure\Persistence\PDOOrderRepository;
use App\Infrastructure\Persistence\ConnectionFactory;
use App\Infrastructure\Persistence\InMemoryOrderRepository;

use App\Infrastructure\Messaging\QueuePublisher;
use App\Infrastructure\Messaging\InMemoryQueuePublisher;

use Dotenv\Dotenv;

class Bootstrap
{
    private OrderRepository $orderRepository;
    private QueuePublisher $queuePublisher;
    private EventDispatcherInterface $eventDispatcher;

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
                $this->queuePublisher  = new InMemoryQueuePublisher();
                break;

            case 'dev':
            default:
                $this->orderRepository = new InMemoryOrderRepository();
                $this->queuePublisher  = new InMemoryQueuePublisher();
        }

        $this->initializeEventDispatcher();
    }

    private function initializeEventDispatcher(): void
    {
        $dispatcher = new SimpleEventDispatcher();

        $dispatcher->listen(
            OrderReceived::class,
            new PublishOrderToQueueListener($this->queuePublisher)
        );

        $this->eventDispatcher = $dispatcher;
    }

    public function createOrderUseCase(): CreateOrderUseCase
    {
        return new CreateOrderUseCase(
            $this->orderRepository,
            $this->eventDispatcher
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