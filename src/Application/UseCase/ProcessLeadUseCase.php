<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Domain\Repository\LeadRepository;
use App\Application\AI\AIProvider;

class ProcessLeadUseCase
{
    public function __construct(
        private LeadRepository $leadRepository,
        private AIProvider $aiProvider
    ) {}

    public function execute(string $leadId): void
    {
        $lead = $this->leadRepository->findById($leadId);

        if (!$lead) {
            throw new \RuntimeException("Lead not found");
        }

        // chama IA
        $result = $this->aiProvider->classify($lead->getMessage());

        // valida retorno básico
        $intent = $result['intent'] ?? 'unknown';
        $priority = $result['priority'] ?? 'low';
        $score = $result['score'] ?? 0;
        $action = $result['suggested_action'] ?? 'review';

        // atualiza lead (enriquecimento)
        $lead->classify(
            intent: $intent,
            priority: $priority,
            score: (float) $score,
            suggestedAction: $action
        );

        // persiste
        $this->leadRepository->save($lead);
    }
}