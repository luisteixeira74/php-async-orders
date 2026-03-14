#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Application\Bootstrap;

$environment = getenv('APP_ENV') ?: 'dev';

echo "Starting projection consumer...\n";

$bootstrap = new Bootstrap($environment);

$handler = $bootstrap->updateOrderProjectionHandler();

/**
 * Simulação de mensagem da fila
 */
$event = [
    'order_id' => 'example-order-id',
    'customer_id' => 1,
    'total' => 100.50
];

$handler->handle($event);

echo "Projection updated.\n";