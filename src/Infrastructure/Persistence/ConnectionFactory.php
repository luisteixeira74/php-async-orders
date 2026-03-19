<?php

namespace App\Infrastructure\Persistence;

use PDO;

class ConnectionFactory
{
    public static function create(): PDO
    {
        $driver = getenv('DB_DRIVER') ?: 'pgsql';
        $host   = getenv('DB_HOST') ?: 'postgres';
        $port   = getenv('DB_PORT') ?: 5432;
        $db     = getenv('DB_DATABASE') ?: 'async_orders';
        $user   = getenv('DB_USERNAME') ?: 'postgres';
        $pass   = getenv('DB_PASSWORD') ?: 'postgres';

        $dsn = sprintf('%s:host=%s;port=%s;dbname=%s', $driver, $host, $port, $db);

        $pdo = new PDO(
            $dsn,
            $user,
            $pass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );

        return $pdo;
    }
}