<?php

namespace App\Application\Listener;

use App\Domain\Event\LeadReceived;
use App\Infrastructure\Messaging\QueuePublisher;

class PublishLeadToQueueListener
{
    public function __construct(
        private QueuePublisher $publisher
    ) {}

    public function __invoke(LeadReceived $event): void
    {
        $this->publisher->publish([
            'type' => 'lead.received',
            'payload' => [
                'leadId' => $event->leadId,
                'name' => $event->name,
                'message' => $event->message
            ]
        ]);
    }
}

