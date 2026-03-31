<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Application\Bootstrap;
use App\Application\UseCase\CreateLeadUseCase;

$container = Bootstrap::create();

$useCase = $container->get(CreateLeadUseCase::class);

$name = $argv[1] ?? 'Anonimo';
$message = $argv[2] ?? 'quero saber mais';

$lead = $useCase->execute($name, $message);

echo "Lead criado: " . $lead->getId() . PHP_EOL;