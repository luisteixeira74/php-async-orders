<?php

namespace App\Application\Listener;

use App\Domain\Event\OrderReceived;
use App\Infrastructure\Messaging\QueuePublisher;

class PublishOrderToQueueListener
{
    public function __construct(
        private QueuePublisher $queuePublisher
    ) {}

    public function __invoke(OrderReceived $event): void
    {
        $this->queuePublisher->publish(
            'order.received', // topic
            $event->toArray() // payload
        );
    }
}