<?php
/**
 * MedMock - Configuration File
 * 
 * Central configuration settings for the application.
 */

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Site Configuration
define('SITE_NAME', 'MedMock');
define('SITE_URL', 'http://localhost/medmock');
define('SITE_EMAIL', 'noreply@medmock.com');

// Path Configuration
define('ROOT_PATH', dirname(__DIR__) . '/');
define('INCLUDES_PATH', ROOT_PATH . 'includes/');
define('ASSETS_PATH', ROOT_PATH . 'assets/');

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS

// Date/Time Configuration
date_default_timezone_set('Asia/Kolkata');

// Start Session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

