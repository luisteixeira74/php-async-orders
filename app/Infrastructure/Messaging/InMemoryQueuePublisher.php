<?php

namespace App\Infrastructure\Queue;

class InMemoryQueuePublisher implements QueuePublisher
{
    public array $messages = [];

    public function publish(string $topic, array $payload): void
    {
        $this->messages[] = compact('topic', 'payload');
    }
}
