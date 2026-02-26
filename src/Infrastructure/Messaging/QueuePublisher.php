<?php

namespace App\Infrastructure\Messaging;

interface QueuePublisher
{
    public function publish(string $topic, array $payload): void;
}
