#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Application\Bootstrap;
use App\Application\Consumer\OrderProjectionConsumer;

$environment = getenv('APP_ENV') ?: 'dev';

echo "Starting projection consumer...\n";

$bootstrap = new Bootstrap($environment);

$handler = $bootstrap->updateOrderProjectionHandler();

$consumer = new OrderProjectionConsumer($handler);

/**
 * Simulação de mensagem da fila
 */
$event = [
    'order_id' => 'example-order-id',
    'customer_id' => 1,
    'total' => 100.50
];

$consumer->consume($event);

echo "Projection updated.\n";
