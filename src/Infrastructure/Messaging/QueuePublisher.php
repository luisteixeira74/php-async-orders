<?php

namespace App\Infrastructure\Messaging;

interface QueuePublisher
{
    public function publish(object $event): void;
}
