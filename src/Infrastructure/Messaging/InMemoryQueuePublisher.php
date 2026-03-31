<?php

namespace App\Infrastructure\Messaging;

class InMemoryQueuePublisher implements QueuePublisher
{
    public array $messages = [];

    public function publish(object $event): void
    {
        $this->messages[] = compact('topic', 'payload');
    }
}
