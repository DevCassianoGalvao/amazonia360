<?php
function env_load(string $path): void {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        [$key, $val] = array_map('trim', explode('=', $line, 2));
        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $val;
            putenv("$key=$val");
        }
    }
}

env_load(__DIR__ . '/.env');

function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;

    $host  = $_ENV['DB_HOST']  ?? 'localhost';
    $name  = $_ENV['DB_NAME']  ?? '';
    $user  = $_ENV['DB_USER']  ?? '';
    $pass  = $_ENV['DB_PASS']  ?? '';

    $pdo = new PDO(
        "mysql:host=$host;dbname=$name;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    return $pdo;
}

function table(): string {
    return $_ENV['DB_TABLE'] ?? 'avaliacoes';
}
