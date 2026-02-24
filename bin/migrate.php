<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Application\Bootstrap;
use App\Infrastructure\Persistence\ConnectionFactory;

(new Bootstrap(getenv('APP_ENV') ?: 'prod'));

$pdo = ConnectionFactory::create();

$migrationsPath = __DIR__ . '/../database/migrations';

$files = glob($migrationsPath . '/*.sql');

if (!$files) {
    echo "No migration files found.\n";
    exit(0);
}

foreach ($files as $file) {
    echo "Running migration: " . basename($file) . "\n";

    $sql = file_get_contents($file);
    $pdo->exec($sql);
}

echo "Migrations executed successfully.\n";