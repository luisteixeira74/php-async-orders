<?php

namespace App\Domain\Repository;

use App\Domain\Entity\Lead;

interface LeadRepository
{
    public function save(Lead $lead): void;

    public function findById(string $id): ?Lead;

    public function findAll(): array;
}