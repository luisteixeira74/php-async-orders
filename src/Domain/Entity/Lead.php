<?php

namespace App\Domain\Entity;

use DateTimeImmutable;
use Ramsey\Uuid\Uuid;
use App\Domain\Event\LeadReceived;
use App\Domain\Event\DomainEvent;

class Lead
{
    private string $id;
    private string $name;
    private string $message;
    private DateTimeImmutable $createdAt;

    // resultado da IA (enriquecimento)
    private ?string $intent = null;
    private ?string $priority = null;
    private ?float $score = null;
    private ?string $suggestedAction = null;
    

    private array $domainEvents = [];

    private function __construct(
        string $id,
        string $name,
        string $message,
        ?DateTimeImmutable $createdAt = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->message = $message;
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
    }

    public static function create(string $name, string $message): self
    {
        $lead = new self(
            id: self::generateId(),
            name: $name,
            message: $message,
            createdAt: new DateTimeImmutable()
        );

        $lead->recordEvent(
            new LeadReceived(
                leadId: $lead->getId(),
                name: $lead->getName(),
                message: $lead->getMessage()
            )
        );

        return $lead;
    }

    public function classify(
        string $intent,
        string $priority,
        float $score,
        string $suggestedAction
    ): void {
        $this->intent = $intent;
        $this->priority = $priority;
        $this->score = $score;
        $this->suggestedAction = $suggestedAction;
    }

    public static function rehydrate(
        string $id,
        string $name,
        string $message,
        ?string $priority,
        ?float $score,
        DateTimeImmutable $createdAt
    ): self {
        $lead = new self(
            id: $id,
            name: $name,
            message: $message,
            createdAt: $createdAt
        );

        $lead->priority = $priority;
        $lead->score = $score;

        return $lead;
    }

    // ========================
    // GETTERS
    // ========================

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getPriority(): ?string
    {
        return $this->priority;
    }

    public function getScore(): ?float
    {
        return $this->score;
    }

    // ========================
    // ENRIQUECIMENTO (IA)
    // ========================

    public function enrich(
        string $category,
        string $priority,
        float $score
    ): void {
        $this->priority = $priority;
        $this->score = $score;
    }

    // ========================
    // EVENTS
    // ========================

    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }

    private function recordEvent(DomainEvent $event): void
    {
        $this->domainEvents[] = $event;
    }

    private static function generateId(): string
    {
        return Uuid::uuid7()->toString();
    }
}