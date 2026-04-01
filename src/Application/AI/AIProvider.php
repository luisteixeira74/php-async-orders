<?php

declare(strict_types=1);

namespace App\Application\AI;

interface AIProvider
{
    public function classify(string $message): array;
}