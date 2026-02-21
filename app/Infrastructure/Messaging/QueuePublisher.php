<?php

namespace App\Infrastructure\Queue;

interface QueuePublisher
{
    public function publish(string $topic, array $payload): void;
}
