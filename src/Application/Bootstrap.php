<?php

declare(strict_types=1);

namespace App\Application;

use App\Application\Event\EventDispatcherInterface;
use App\Application\Event\SimpleEventDispatcher;
use App\Application\Listener\PublishOrderToQueueListener;
use App\Domain\Event\OrderReceived;
use App\Application\UseCase\CreateOrderUseCase;
use App\Application\UseCase\ProcessOrderUseCase;
use App\Application\UseCase\FailOrderUseCase;
use App\Domain\Repository\OrderRepository;
use App\Domain\Repository\OrderEventRepository;
use App\Infrastructure\Persistence\PDOOrderRepository;
use App\Infrastructure\Persistence\PDOOrderEventRepository;
use App\Infrastructure\Persistence\ConnectionFactory;
use App\Infrastructure\Persistence\InMemoryOrderRepository;
use App\Infrastructure\Persistence\InMemoryOrderEventRepository;
use App\Infrastructure\Messaging\QueuePublisher;
use App\Infrastructure\Messaging\InMemoryQueuePublisher;
use App\Application\Projection\UpdateOrderProjectionHandler;
use App\Infrastructure\Persistence\PDOOrderProjectionRepository;

use Dotenv\Dotenv;

class Bootstrap
{
    private OrderRepository $orderRepository;
    private OrderEventRepository $orderEventRepository;
    private QueuePublisher $queuePublisher;
    private EventDispatcherInterface $eventDispatcher;
    private ?\PDO $pdo = null;

    public function __construct(private string $environment = 'dev')
    {
        $this->loadEnv(); // variáveis de ambiente
        $this->initialize(); // inicializa publishers, repositories e dispatcher
    }

    private function loadEnv(): void
    {
        $root = dirname(__DIR__, 2);

        if (file_exists($root . '/.env')) {
            $dotenv = Dotenv::createImmutable($root);
            $dotenv->load();
        } else {
            // fallback inteligente
            if ($this->environment === 'dev') {
                throw new \RuntimeException("Arquivo .env não encontrado na raiz do projeto ($root)");
            }
            // Em CI ou prod, não temos .env → usar getenv() ou defaults
            echo "[Bootstrap] .env não encontrado, usando variáveis do ambiente ou defaults\n";
        }
    }

    private function initialize(): void
    {
        echo "Inicializando aplicação no ambiente: {$this->environment}\n";

        // Publisher
        $this->queuePublisher = new InMemoryQueuePublisher();

        if ($this->environment === 'prod') {
            $this->orderRepository = new PDOOrderRepository($this->getConnection());
            $this->orderEventRepository = new PDOOrderEventRepository($this->getConnection());
            $this->queuePublisher = new QueuePublisher(); // real, tipo Redis
        } else {
            $this->orderRepository = new InMemoryOrderRepository();
            $this->orderEventRepository = new InMemoryOrderEventRepository();
            $this->queuePublisher = new InMemoryQueuePublisher();
        }

        // Dispatcher (usa publisher → precisa vir depois)
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

    public function getConnection(): \PDO
    {
        if ($this->pdo === null) {
            $this->pdo = ConnectionFactory::create();
        }

        return $this->pdo;
    }

    // --- Getters / factories ---

    public function orderRepository(): OrderRepository
    {
        return $this->orderRepository;
    }

    public function createOrderUseCase(): CreateOrderUseCase
    {
        return new CreateOrderUseCase(
            $this->orderRepository,
            $this->eventDispatcher,
            $this->orderEventRepository
        );
    }

    public function processOrderUseCase(): ProcessOrderUseCase
    {
        return new ProcessOrderUseCase(
            $this->orderRepository,
            $this->eventDispatcher,
            $this->orderEventRepository
        );
    }

    public function failOrderUseCase(): FailOrderUseCase
    {
        return new FailOrderUseCase(
            $this->orderRepository,
            $this->orderEventRepository
        );
    }

    public function updateOrderProjectionHandler(): UpdateOrderProjectionHandler
    {
        $pdo = $this->getConnection();
        $projectionRepository = new PDOOrderProjectionRepository($pdo);
        return new UpdateOrderProjectionHandler($projectionRepository);
    }
}