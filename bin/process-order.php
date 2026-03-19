#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Application\Bootstrap;
use App\Domain\Exception\OrderNotFoundException;
use App\Domain\Exception\InvalidOrderStateException;

$environment = getenv('APP_ENV') ?: 'dev';

$bootstrap = new Bootstrap($environment);
$useCase   = $bootstrap->processOrderUseCase();

// Pega todos os orderIds passados como parâmetro
$orderIds = array_slice($argv, 1);

if (empty($orderIds)) {
    echo "No orderId passed. Processing all pending orders...\n";

    // Busca todas as orders pendentes via UseCase
    $pendingOrders = $useCase->getPendingOrders();

    if (empty($pendingOrders)) {
        echo "No pending orders found.\n";
        exit(0);
    }

    // Extrai os IDs das orders pendentes
    $orderIds = array_map(fn($order) => $order->getId(), $pendingOrders);
}

foreach ($orderIds as $orderId) {
    try {
        $useCase->execute($orderId);
        echo "Order {$orderId} is now in PROCESSING state.\n";
        echo "Environment: {$environment}\n";
    } catch (OrderNotFoundException $e) {
        fwrite(STDERR, "Order not found: {$orderId}\n");
    } catch (InvalidOrderStateException $e) {
        fwrite(STDERR, "Invalid order state transition for order: {$orderId}\n");
    } catch (\Throwable $e) {
        fwrite(STDERR, "Unexpected error for order {$orderId}: {$e->getMessage()}\n");
    }
}