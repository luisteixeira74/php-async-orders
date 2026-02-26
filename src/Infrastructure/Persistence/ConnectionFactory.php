<?php

namespace App\Infrastructure\Persistence;

use PDO;

class ConnectionFactory
{
    public static function create(): PDO
    {
        $dsn = sprintf(
            '%s:host=%s;port=%s;dbname=%s',
            $_ENV['DB_DRIVER'],
            $_ENV['DB_HOST'],
            $_ENV['DB_PORT'],
            $_ENV['DB_DATABASE']
        );

        $pdo = new PDO(
            $dsn,
            $_ENV['DB_USERNAME'],
            $_ENV['DB_PASSWORD']
        );

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }
}