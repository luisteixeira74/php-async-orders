<?php

declare(strict_types=1);

namespace App\Domain\Event;

use DateTimeImmutable;

class LeadReceived implements DomainEvent
{
    private DateTimeImmutable $occurredOn;

    public function __construct(
        public string $leadId,
        public string $name,
        public string $message
    ) {
        $this->occurredOn = new DateTimeImmutable();
    }

    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }

    public function eventName(): string
    {
        return 'lead.received';
    }

    public function toArray(): array
    {
        return [
            'lead_id' => $this->leadId,
            'name' => $this->name,
            'message' => $this->message,
            'occurred_on' => $this->occurredOn->format('Y-m-d H:i:s'),
        ];
    }
}