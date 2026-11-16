<?php
date_default_timezone_set('Asia/Manila');

define('APP_NAME', "XXL’s Salon");
define('APP_ENV', getenv('APP_ENV') ?: 'production');

if (APP_ENV === 'development') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

$baseUrl = getenv('BASE_URL');
if ($baseUrl === false || trim($baseUrl) === '') {
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
    $dir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
    $baseUrl = in_array($dir, ['', '.', '/'], true) ? '' : $dir;
}
define('BASE_URL', rtrim((string)$baseUrl, '/'));

$dbUrl = trim((string)(getenv('DATABASE_URL') ?: ''));
$db = [
    'driver' => strtolower(trim((string)(getenv('DB_DRIVER') ?: ''))),
    'host' => getenv('DB_HOST') ?: 'localhost',
    'port' => getenv('DB_PORT') ?: null,
    'name' => getenv('DB_NAME') ?: 'xxls_salon',
    'user' => getenv('DB_USER') ?: 'root',
    'pass' => getenv('DB_PASS') !== false ? getenv('DB_PASS') : '',
    'sslmode' => getenv('DB_SSLMODE') ?: '',
];

if ($dbUrl !== '') {
    $parsed = parse_url($dbUrl);
    if ($parsed === false || empty($parsed['scheme'])) {
        throw new RuntimeException('DATABASE_URL is not a valid connection URL.');
    }
    $scheme = strtolower($parsed['scheme']);
    $db['driver'] = in_array($scheme, ['postgres', 'postgresql', 'pgsql'], true) ? 'pgsql' : ($scheme === 'mysql' ? 'mysql' : $db['driver']);
    $db['host'] = $parsed['host'] ?? $db['host'];
    $db['port'] = $parsed['port'] ?? null;
    $db['name'] = isset($parsed['path']) ? rawurldecode(ltrim($parsed['path'], '/')) : $db['name'];
    $db['user'] = isset($parsed['user']) ? rawurldecode($parsed['user']) : $db['user'];
    $db['pass'] = isset($parsed['pass']) ? rawurldecode($parsed['pass']) : $db['pass'];
    parse_str($parsed['query'] ?? '', $query);
    if (isset($query['sslmode'])) $db['sslmode'] = (string)$query['sslmode'];
}

if ($db['driver'] === '') $db['driver'] = $dbUrl !== '' ? 'pgsql' : 'mysql';
if (!in_array($db['driver'], ['pgsql', 'mysql'], true)) {
    throw new RuntimeException('Unsupported database driver: ' . $db['driver']);
}
$db['port'] = (int)($db['port'] ?: ($db['driver'] === 'pgsql' ? 5432 : 3306));

define('DB_DRIVER', $db['driver']);
define('DB_HOST', $db['host']);
define('DB_PORT', $db['port']);
define('DB_NAME', $db['name']);
define('DB_USER', $db['user']);
define('DB_PASS', $db['pass']);
define('DB_SSLMODE', (string)$db['sslmode']);

require_once __DIR__ . '/includes/database.php';

if (session_status() === PHP_SESSION_NONE) {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https';
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    if (DB_DRIVER === 'mysql') {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $options[PDO::ATTR_EMULATE_PREPARES] = false;
    } else {
        $dsn = 'pgsql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME;
        if (DB_SSLMODE !== '') $dsn .= ';sslmode=' . DB_SSLMODE;
    }

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        db_bootstrap($pdo, DB_DRIVER);
    } catch (PDOException $ex) {
        error_log('Database error: ' . $ex->getMessage());
        http_response_code(500);
        exit('We could not connect to the database right now. Please try again later.');
    }

    return $pdo;
}
