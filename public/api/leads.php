<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Application\Bootstrap;

$bootstrap = new Bootstrap();

$useCase = $bootstrap->createLeadUseCase();

$data = json_decode(file_get_contents('php://input'), true);

$name = $data['name'] ?? 'Anonimo';
$message = $data['message'] ?? '';

try {
    $lead = $useCase->execute($name, $message);

    echo json_encode([
        'id' => $lead->getId()
    ]);
} catch (\InvalidArgumentException $e) {
    http_response_code(400);

    echo json_encode([
        'error' => $e->getMessage()
    ]);
}