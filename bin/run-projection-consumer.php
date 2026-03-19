#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Application\Bootstrap;
use App\Application\Consumer\OrderProjectionConsumer;

$environment = getenv('APP_ENV') ?: 'dev';

echo "Starting projection consumer...\n";
echo "Environment: {$environment}\n";

$bootstrap = new Bootstrap($environment);

$consumer = new OrderProjectionConsumer(
    $bootstrap->updateOrderProjectionHandler(),
    $bootstrap->orderRepository()
);

try {
    $consumer->consume();

    echo "Projection updated successfully.\n";

} catch (\Throwable $e) {
    fwrite(STDERR, "Error while processing projection: {$e->getMessage()}\n");
    exit(1);
}