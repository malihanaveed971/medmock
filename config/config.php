<?php
/**
 * MedMock - Configuration File
 */

// =========================
// Error Reporting
// =========================
error_reporting(E_ALL);
ini_set('display_errors', 1);

// =========================
// Site Configuration
// =========================
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? 80) == 443;
$protocol = $isHttps ? 'https://' : 'http://';
$hostHeader = $_SERVER['HTTP_HOST'] ?? 'localhost';

define('SITE_NAME', 'MedMock');
define('SITE_URL', $protocol . $hostHeader);
define('SITE_EMAIL', 'noreply@medmock.com');

// =========================
// Path Configuration
// =========================
define('ROOT_PATH', dirname(__DIR__) . '/');
define('INCLUDES_PATH', ROOT_PATH . 'includes/');
define('ASSETS_PATH', ROOT_PATH . 'assets/');

// =========================
// Session Configuration
// =========================
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', $isHttps ? 1 : 0);

// =========================
// Time Zone
// =========================
date_default_timezone_set('Asia/Karachi');

// =========================
// Start Session
// =========================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}