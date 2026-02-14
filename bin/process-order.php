#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Application\Bootstrap;
use App\Domain\Exception\OrderNotFoundException;
use App\Domain\Exception\InvalidOrderStateException;

$environment = getenv('APP_ENV') ?: 'dev';

if ($argc < 2) {
    fwrite(STDERR, "Usage: php bin/process-order.php <orderId>\n");
    exit(1);
}

$orderId = $argv[1];

$bootstrap = new Bootstrap($environment);
$useCase   = $bootstrap->processOrderUseCase();

try {
    $order = $useCase->execute($orderId);

    echo "Order {$orderId} is now in PROCESSING state.\n";
    echo "Environment: {$environment}\n";

} catch (OrderNotFoundException $e) {
    fwrite(STDERR, "Order not found: {$orderId}\n");
    exit(1);

} catch (InvalidOrderStateException $e) {
    fwrite(STDERR, "Invalid order state transition.\n");
    exit(1);

} catch (\Throwable $e) {
    fwrite(STDERR, "Unexpected error: {$e->getMessage()}\n");
    exit(1);
}
