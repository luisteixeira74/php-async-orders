<?php

declare(strict_types=1);

namespace App\Infrastructure\AI;

use App\Application\AI\AIProvider;

class OpenAIProvider implements AIProvider
{
    public function __construct(
        private string $apiKey,
        private string $model = 'gpt-4o-mini'
    ) {}

    public function classify(string $message): array
    {
        $response = $this->callOpenAI($message);

        return $this->parseResponse($response);
    }

    private function callOpenAI(string $message): string
    {
        $url = "https://api.openai.com/v1/chat/completions";

        $payload = [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a classification system. Return ONLY valid JSON with keys: intent, priority, score, suggested_action.'
                ],
                [
                    'role' => 'user',
                    'content' => $message
                ]
            ],
            'temperature' => 0
        ];

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
        ]);

        $result = curl_exec($ch);

        if ($result === false) {
            throw new \RuntimeException('OpenAI request failed: ' . curl_error($ch));
        }

        curl_close($ch);

        return $result;
    }

    private function parseResponse(string $response): array
    {
        $data = json_decode($response, true);

        $content = $data['choices'][0]['message']['content'] ?? null;

        if (!$content) {
            throw new \RuntimeException('Invalid AI response');
        }

        $json = json_decode($content, true);

        if (!$json) {
            throw new \RuntimeException('AI did not return valid JSON');
        }

        return [
            'intent' => $json['intent'] ?? 'unknown',
            'priority' => $json['priority'] ?? 'low',
            'score' => (float) ($json['score'] ?? 0),
            'suggested_action' => $json['suggested_action'] ?? 'review'
        ];
    }
}