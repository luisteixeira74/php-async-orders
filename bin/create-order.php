#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Application\Bootstrap;

$environment = getenv('APP_ENV') ?: 'dev';

if ($argc < 3) {
    fwrite(STDERR, "Usage: php bin/create-order.php <customerId> <value>\n");
    exit(1);
}

$customerId = $argv[1];          // manter string
$value      = (float) $argv[2];

$bootstrap = new Bootstrap($environment);
$useCase   = $bootstrap->createOrderUseCase();

try {
    $orderId = $useCase->execute($customerId, $value);

    echo "Order created successfully.\n";
    echo "Order ID: {$orderId}\n";
    echo "Environment: {$environment}\n";

} catch (\Throwable $e) {
    fwrite(STDERR, "Error: {$e->getMessage()}\n");
    exit(1);
}
