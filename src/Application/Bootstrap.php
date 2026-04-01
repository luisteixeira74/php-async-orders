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
use App\Application\Validation\LeadValidator;
use App\Application\UseCase\ProcessLeadUseCase;
use App\Application\AI\AIProvider;

use App\Infrastructure\AI\FakeAIProvider;
use App\Infrastructure\AI\OpenAIProvider;
use App\Application\Listener\PublishLeadToQueueListener;

use Dotenv\Dotenv;

class Bootstrap
{
    private LeadRepository $leadRepository;
    private QueuePublisher $queuePublisher;
    private EventDispatcherInterface $eventDispatcher;
    private LeadValidator $leadValidator;
    private AIProvider $aiProvider;

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

        // =========================
        // QUEUE
        // =========================
        if ($this->environment === 'prod') {
            $this->queuePublisher = new RabbitMQQueuePublisher(
                getenv('RABBITMQ_HOST') ?: 'rabbitmq',
                (int) getenv('RABBITMQ_PORT') ?: 5672,
                getenv('RABBITMQ_USER') ?: 'guest',
                getenv('RABBITMQ_PASSWORD') ?: 'guest'
            );
        } else {
            $this->queuePublisher = new InMemoryQueuePublisher();
        }

        // =========================
        // REPOSITORY
        // =========================
        $this->leadRepository = new InMemoryLeadRepository();

        // =========================
        // VALIDATOR
        // =========================
        $this->leadValidator = new LeadValidator();

        // =========================
        // AI PROVIDER
        // =========================
        $this->initializeAIProvider();

        // =========================
        // EVENTS
        // =========================
        $this->initializeEventDispatcher();
    }

    private function initializeEventDispatcher(): void
    {
        $dispatcher = new SimpleEventDispatcher();

        $dispatcher->listen(
            LeadReceived::class,
            new PublishLeadToQueueListener($this->queuePublisher)
        );

        $this->eventDispatcher = $dispatcher;
    }

    private function initializeAIProvider(): void
    {
        $provider = getenv('AI_PROVIDER') ?: 'fake';

        if ($provider === 'openai') {
            $this->aiProvider = new OpenAIProvider(
                getenv('OPENAI_API_KEY') ?: '',
                getenv('OPENAI_MODEL') ?: 'gpt-4o-mini'
            );
        } else {
            $this->aiProvider = new FakeAIProvider();
        }
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
            $this->eventDispatcher,
            $this->leadValidator
        );
    }

    public function processLeadUseCase(): ProcessLeadUseCase
    {
        return new ProcessLeadUseCase(
            $this->leadRepository,
            $this->aiProvider
        );
    }
}