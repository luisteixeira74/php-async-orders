<?php

namespace App\Infrastructure\Messaging;

interface QueuePublisher
{
    public function publish(array $message): void;
}