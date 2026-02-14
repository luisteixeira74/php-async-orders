<?php

namespace App\Application;

use App\Application\UseCase\CreateOrderUseCase;
use App\Application\UseCase\ProcessOrderUseCase;
use App\Application\UseCase\FinalizeOrderUseCase;
use App\Application\UseCase\FailOrderUseCase;

use App\Domain\Repository\OrderRepository;
use App\Infrastructure\Repository\InMemoryOrderRepository;

use App\Infrastructure\Queue\QueuePublisher;
use App\Infrastructure\Queue\InMemoryQueuePublisher;

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
        switch ($this->environment) {
            case 'prod':
                // $this->orderRepository = new PdoOrderRepository($pdo);
                // $this->queuePublisher = new RabbitMQPublisher(...);

                // Temporário enquanto infra real não existe:
                $this->orderRepository = new InMemoryOrderRepository();
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

    public function finalizeOrderUseCase(): FinalizeOrderUseCase
    {
        return new FinalizeOrderUseCase(
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
