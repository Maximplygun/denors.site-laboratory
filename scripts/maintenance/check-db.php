<?php
declare(strict_types=1);

// Read-only CLI diagnostic. Credentials are supplied by the SSH session, not Git.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$host = getenv('DENORS_DB_HOST') ?: '';
$name = getenv('DENORS_DB_NAME') ?: '';
$user = getenv('DENORS_DB_USER') ?: '';
$password = getenv('DENORS_DB_PASSWORD');
if (!preg_match('/\A[a-zA-Z0-9.-]+\z/', $host)
    || !preg_match('/\A[a-zA-Z0-9_]+\z/', $name)
    || $user === '' || $password === false || $password === '') {
    fwrite(STDERR, "Missing or invalid DB parameters; no connection attempted.\n");
    exit(1);
}

try {
    $pdo = new PDO("mysql:host={$host};dbname={$name};charset=utf8mb4", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 10,
    ]);
    unset($password);
    $info = $pdo->query('SELECT VERSION() AS version, @@character_set_connection AS charset, @@collation_database AS collation')->fetch(PDO::FETCH_ASSOC);
    $engines = $pdo->query('SHOW ENGINES')->fetchAll(PDO::FETCH_ASSOC);
    $innodb = false;
    foreach ($engines as $engine) {
        if (strcasecmp($engine['Engine'], 'InnoDB') === 0
            && in_array(strtoupper($engine['Support']), ['YES', 'DEFAULT'], true)) {
            $innodb = true;
        }
    }
    $pdo->beginTransaction();
    $pdo->query('SELECT 1')->fetchColumn();
    $pdo->rollBack();
    $tables = $pdo->query('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()')->fetchColumn();
    echo 'PDO connection: OK', PHP_EOL;
    echo 'Server: ', $info['version'], PHP_EOL;
    echo 'Connection charset: ', $info['charset'], PHP_EOL;
    echo 'Database collation: ', $info['collation'], PHP_EOL;
    echo 'InnoDB: ', $innodb ? 'OK' : 'UNAVAILABLE', PHP_EOL;
    echo 'Transaction begin/rollback: OK', PHP_EOL;
    echo 'Existing tables: ', $tables, PHP_EOL;
    exit($innodb && $info['charset'] === 'utf8mb4' ? 0 : 1);
} catch (PDOException $exception) {
    // Driver error text may contain connection details. Print only SQLSTATE.
    fwrite(STDERR, 'DB check failed; SQLSTATE: ' . $exception->getCode() . PHP_EOL);
    exit(1);
}
