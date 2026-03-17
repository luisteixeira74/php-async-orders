<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use App\Infrastructure\Persistence\ConnectionFactory;

header('Content-Type: application/json');

try {
    $pdo = ConnectionFactory::create();

    $stmt = $pdo->query("
        SELECT 
            order_id,
            customer_id,
            total,
            status,
            created_at
        FROM order_projection
        ORDER BY created_at DESC
    ");

    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($orders);

} catch (\Throwable $e) {
    http_response_code(500);

    echo json_encode([
        'error' => $e->getMessage()
    ]);
}