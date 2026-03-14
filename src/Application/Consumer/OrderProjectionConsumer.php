<?php

declare(strict_types=1);

namespace App\Application\Consumer;

use App\Application\Projection\UpdateOrderProjectionHandler;

class OrderProjectionConsumer
{
    public function __construct(
        private UpdateOrderProjectionHandler $handler
    ) {}

    public function consume(array $event): void
    {
        $this->handler->handle($event);
    }
}