<?php

declare(strict_types=1);

namespace App\Application\Validation;

class LeadValidator
{
    public function validate(string $name, string $message): void
    {
        $this->validateMessage($message);
        $this->validateName($name);
    }

    private function validateMessage(string $message): void
    {
        $message = trim($message);

        if ($message === '') {
            throw new \InvalidArgumentException('Message is required');
        }

        if (strlen($message) < 5) {
            throw new \InvalidArgumentException('Message must be at least 5 characters');
        }

        if (strlen($message) > 1000) {
            throw new \InvalidArgumentException('Message too long');
        }
    }

    private function validateName(string $name): void
    {
        $name = trim($name);

        if ($name === '') {
            throw new \InvalidArgumentException('Name is required');
        }

        if (strlen($name) < 2) {
            throw new \InvalidArgumentException('Name too short');
        }
    }
}