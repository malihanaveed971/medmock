<?php
/**
 * MedMock - Database Configuration & Helpers
 * Dynamic Environment Switch (Local XAMPP vs InfinityFree Production)
 */

$hostHeader = $_SERVER['HTTP_HOST'] ?? '';
$isLocal = ($hostHeader === 'localhost' || strpos($hostHeader, '127.0.0.1') !== false || strpos($hostHeader, 'localhost:') === 0);

if ($isLocal) {
    // Local Development (XAMPP)
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'medmock');
    define('DB_USER', 'root');
    define('DB_PASS', '');
} else {
    // InfinityFree Production Database Credentials
    define('DB_HOST', 'sql303.infinityfree.com');
    define('DB_NAME', 'if0_42547635_medmock');
    define('DB_USER', 'if0_42547635');
    define('DB_PASS', 'Malihanaveed');
}

define('DB_CHARSET', 'utf8mb4');

// Dynamic Base URL Detection
if (!defined('BASE_URL')) {
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    if (strpos($scriptName, '/medmock') === 0) {
        define('BASE_URL', '/medmock');
    } else {
        define('BASE_URL', '');
    }
}

/**
 * Get PDO database connection
 * 
 * @return PDO
 */
function getConnection() {
    static $pdo = null;

    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Database Connection Failed: " . $e->getMessage());
        }
    }

    return $pdo;
}

/**
 * Helper to generate correct URL paths dynamically
 * 
 * @param string $path
 * @return string
 */
function url($path = '') {
    $path = ltrim($path, '/');
    return BASE_URL . '/' . $path;
}