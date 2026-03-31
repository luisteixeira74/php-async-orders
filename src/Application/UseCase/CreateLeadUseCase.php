<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Domain\Entity\Lead;
use App\Domain\Repository\LeadRepository;
use App\Application\Event\EventDispatcherInterface;
use App\Application\Validation\LeadValidator;

class CreateLeadUseCase
{
    public function __construct(
        private LeadRepository $leadRepository,
        private EventDispatcherInterface $eventDispatcher,
        private LeadValidator $validator
    ) {}

    public function execute(string $name, string $message): Lead
    {
        // valida dados
        $this->validator->validate($name, $message);

        // cria entidade
        $lead = Lead::create($name, $message);

        // salva
        $this->leadRepository->save($lead);

        // dispara eventos
        foreach ($lead->pullDomainEvents() as $event) {
            $this->eventDispatcher->dispatch($event);
        }

        return $lead;
    }

    public function createLeadUseCase(): CreateLeadUseCase
    {
        return new CreateLeadUseCase(
            $this->leadRepository,
            $this->eventDispatcher,
            $this->validator
        );
    }
}