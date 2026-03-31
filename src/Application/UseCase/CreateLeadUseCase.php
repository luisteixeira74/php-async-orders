<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Domain\Entity\Lead;
use App\Domain\Repository\LeadRepository;
use App\Application\Event\EventDispatcherInterface;

class CreateLeadUseCase
{
    public function __construct(
        private LeadRepository $repository,
        private EventDispatcherInterface $eventDispatcher
    ) {}

    public function execute(string $name, string $message): Lead
    {
        $lead = Lead::create($name, $message);

        $this->repository->save($lead);

        foreach ($lead->pullDomainEvents() as $event) {
            $this->eventDispatcher->dispatch($event);
        }

        return $lead;
    }
}