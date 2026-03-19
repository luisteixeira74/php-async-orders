<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Application\Bootstrap;

$env = getenv('APP_ENV') ?: 'prod';
echo "Environment: {$env}\n";

// Inicializa o Bootstrap e a conexão
$bootstrap = new Bootstrap($env);
$pdo = $bootstrap->getConnection();

try {
    $pdo->query('SELECT 1');
    echo "Database connection OK\n";
} catch (\PDOException $e) {
    echo "Database connection FAILED\n";
    echo $e->getMessage() . "\n";
    exit(1);
}

// Caminho das migrations
$migrationsPath = __DIR__ . '/../database/migrations';
$files = glob($migrationsPath . '/*.sql');

if (!$files) {
    echo "No migration files found.\n";
    exit(0);
}

// Executa cada migration
foreach ($files as $file) {
    echo "Running migration: " . basename($file) . "\n";
    try {
        $sql = file_get_contents($file);
        $pdo->exec($sql);
    } catch (\Throwable $e) {
        echo "Error in migration: " . basename($file) . "\n";
        echo $e->getMessage() . "\n";
        exit(1);
    }
}

echo "Migrations executed successfully.\n";