<?php

declare(strict_types=1);

namespace App\Application;

use App\Application\Event\EventDispatcherInterface;
use App\Application\Event\SimpleEventDispatcher;
use App\Application\Listener\PublishOrderToQueueListener; // pode renomear depois
use App\Domain\Event\LeadReceived;
use App\Application\UseCase\CreateLeadUseCase;
use App\Domain\Repository\LeadRepository;
use App\Infrastructure\Persistence\InMemoryLeadRepository;
use App\Infrastructure\Messaging\QueuePublisher;
use App\Infrastructure\Messaging\InMemoryQueuePublisher;
use App\Infrastructure\Messaging\RabbitMQQueuePublisher;

use Dotenv\Dotenv;

class Bootstrap
{
    private LeadRepository $leadRepository;
    private QueuePublisher $queuePublisher;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(private string $environment = 'dev')
    {
        $this->loadEnv();
        $this->initialize();
    }

    private function loadEnv(): void
    {
        $root = dirname(__DIR__, 2);

        if (file_exists($root . '/.env')) {
            $dotenv = Dotenv::createImmutable($root);
            $dotenv->load();
        } else {
            if ($this->environment === 'dev') {
                throw new \RuntimeException("Arquivo .env não encontrado na raiz do projeto ($root)");
            }

            echo "[Bootstrap] .env não encontrado, usando variáveis do ambiente\n";
        }
    }

    private function initialize(): void
    {
        echo "Inicializando aplicação no ambiente: {$this->environment}\n";

        // 🔥 Infra simplificada
        if ($this->environment === 'prod') {
            $this->queuePublisher = new RabbitMQQueuePublisher(
                getenv('RABBITMQ_HOST') ?: 'rabbitmq',
                (int) getenv('RABBITMQ_PORT') ?: 5672,
                getenv('RABBITMQ_USER') ?: 'guest',
                getenv('RABBITMQ_PASS') ?: 'guest'
            );
        } else {
            $this->queuePublisher = new InMemoryQueuePublisher();
        }

        // 🔥 Repository (simples por enquanto)
        $this->leadRepository = new InMemoryLeadRepository();

        $this->initializeEventDispatcher();
    }

    private function initializeEventDispatcher(): void
    {
        $dispatcher = new SimpleEventDispatcher();

        // 🔥 Evento novo (Lead)
        $dispatcher->listen(
            LeadReceived::class,
            new PublishOrderToQueueListener($this->queuePublisher)
        );

        $this->eventDispatcher = $dispatcher;
    }

    // =========================
    // GETTERS
    // =========================

    public function leadRepository(): LeadRepository
    {
        return $this->leadRepository;
    }

    public function createLeadUseCase(): CreateLeadUseCase
    {
        return new CreateLeadUseCase(
            $this->leadRepository,
            $this->eventDispatcher
        );
    }
}