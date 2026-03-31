<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Entity\Lead;
use App\Domain\Repository\LeadRepository;

class InMemoryLeadRepository implements LeadRepository
{
    private array $leads = [];

    public function save(Lead $lead): void
    {
        $this->leads[$lead->getId()] = $lead;
    }

    public function findById(string $id): ?Lead
    {
        return $this->leads[$id] ?? null;
    }

    public function findAll(): array
    {
        return array_values($this->leads);
    }
}