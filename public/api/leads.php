<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Application\Bootstrap;

$bootstrap = new Bootstrap();

$useCase = $bootstrap->createLeadUseCase();

$data = json_decode(file_get_contents('php://input'), true);

$name = $data['name'] ?? 'Anonimo';
$message = $data['message'] ?? '';

$lead = $useCase->execute($name, $message);

echo json_encode([
    'id' => $lead->getId()
]);