<?php

namespace App\Application\Event;

use App\Domain\Event\DomainEvent;

interface EventDispatcherInterface
{
    public function dispatch(DomainEvent $event): void;
}