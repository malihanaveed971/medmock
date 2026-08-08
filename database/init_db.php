<?php
/**
 * MedMock Database Initializer & Seeder
 */

require_once __DIR__ . '/../config/database.php';

echo "<h2>MedMock Database Initializer</h2>";

try {
    // 1. Connect without db name to ensure DB exists
    $pdo = new PDO("mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p style='color:green;'>✓ Database '" . DB_NAME . "' ensured.</p>";

    // Reconnect with DB
    $pdo = getConnection();

    // Load schema
    $sql = file_get_contents(__DIR__ . '/schema.sql');
    $pdo->exec($sql);

    // Alter users table to add missing columns if pre-existing
    $colsUsers = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('role', $colsUsers)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN role ENUM('user', 'admin') DEFAULT 'user'");
    }
    if (!in_array('payment_status', $colsUsers)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN payment_status ENUM('unpaid', 'paid') DEFAULT 'unpaid'");
    }
    if (!in_array('test_credits', $colsUsers)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN test_credits INT DEFAULT 0");
    }
    if (!in_array('trx_id', $colsUsers)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN trx_id VARCHAR(100) DEFAULT NULL");
    }

    // Alter mcqs table to add missing columns if pre-existing
    $colsMcqs = $pdo->query("SHOW COLUMNS FROM mcqs")->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('option_e', $colsMcqs)) {
        $pdo->exec("ALTER TABLE mcqs ADD COLUMN option_e TEXT DEFAULT NULL AFTER option_d");
    }
    if (!in_array('explanation', $colsMcqs)) {
        $pdo->exec("ALTER TABLE mcqs ADD COLUMN explanation TEXT DEFAULT NULL");
    }
    if (!in_array('subject', $colsMcqs)) {
        $pdo->exec("ALTER TABLE mcqs ADD COLUMN subject VARCHAR(100) DEFAULT 'General Medicine'");
    }

    // Alter attempts table to add missing columns if pre-existing
    $colsAttempts = $pdo->query("SHOW COLUMNS FROM attempts")->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('status', $colsAttempts)) {
        $pdo->exec("ALTER TABLE attempts ADD COLUMN status ENUM('in_progress', 'completed') DEFAULT 'in_progress'");
    }

    echo "<p style='color:green;'>✓ Tables created and migration columns verified.</p>";

    // 3. Ensure Default Admin User exists
    $adminEmail = 'admin@medmock.com';
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$adminEmail]);

    if (!$stmt->fetch()) {
        $adminPass = password_hash('admin123', PASSWORD_DEFAULT);
        $insertAdmin = $pdo->prepare("
            INSERT INTO users (full_name, email, password, role, payment_status, test_credits)
            VALUES ('MedMock Admin', ?, ?, 'admin', 'paid', 9999)
        ");
        $insertAdmin->execute([$adminEmail, $adminPass]);
        echo "<p style='color:green;'>✓ Admin account initialized.</p>";
    } else {
        $updateAdmin = $pdo->prepare("UPDATE users SET role = 'admin', payment_status = 'paid', test_credits = 9999 WHERE email = ?");
        $updateAdmin->execute([$adminEmail]);
        echo "<p style='color:blue;'>ℹ Admin account updated.</p>";
    }

    echo "<h3>Setup Complete! <a href='../auth/login.php'>Go to Login Page</a></h3>";

} catch (Exception $e) {
    echo "<p style='color:red;'>Initialization Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
