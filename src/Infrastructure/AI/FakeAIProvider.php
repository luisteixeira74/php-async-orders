<?php

declare(strict_types=1);

namespace App\Infrastructure\AI;

use App\Application\AI\AIProvider;

class FakeAIProvider implements AIProvider
{
    public function classify(string $message): array
    {
        return [
            'intent' => 'purchase',
            'priority' => 'high',
            'score' => 0.9,
            'suggested_action' => 'contact immediately'
        ];
    }
}