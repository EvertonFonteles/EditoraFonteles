<?php
declare(strict_types=1);

function database_config(): array
{
    $file = __DIR__ . '/../config/local.php';
    return require is_file($file) ? $file : __DIR__ . '/../config/database.example.php';
}

function db(): PDO
{
    static $connection = null;
    if ($connection === null) {
        $config = database_config();
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $config['host'], $config['port'], $config['database']);
        $connection = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
    return $connection;
}
