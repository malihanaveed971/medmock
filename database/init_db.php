<?php
/**
 * MedMock Database Initializer & Seeder
 * Production & Local Compatible
 */

require_once __DIR__ . '/../config/database.php';

echo "<h2>MedMock Database Initializer</h2>";

try {
    // 1. Connect directly to target database
    $pdo = getConnection();
    echo "<p style='color:green;'>✓ Connected to database: '" . DB_NAME . "'</p>";

    // 2. Create Tables from schema safely
    $tables = [
        "users" => "CREATE TABLE IF NOT EXISTS `users` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `full_name` VARCHAR(150) NOT NULL,
          `email` VARCHAR(150) NOT NULL UNIQUE,
          `password` VARCHAR(255) NOT NULL,
          `phone` VARCHAR(30) DEFAULT NULL,
          `country` VARCHAR(50) DEFAULT 'Pakistan',
          `role` ENUM('user', 'admin') DEFAULT 'user',
          `payment_status` ENUM('unpaid', 'paid') DEFAULT 'unpaid',
          `test_credits` INT DEFAULT 0,
          `trx_id` VARCHAR(100) DEFAULT NULL,
          `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "mcqs" => "CREATE TABLE IF NOT EXISTS `mcqs` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `question` TEXT NOT NULL,
          `option_a` TEXT NOT NULL,
          `option_b` TEXT NOT NULL,
          `option_c` TEXT NOT NULL,
          `option_d` TEXT NOT NULL,
          `option_e` TEXT DEFAULT NULL,
          `correct_option` ENUM('A', 'B', 'C', 'D', 'E') NOT NULL,
          `explanation` TEXT DEFAULT NULL,
          `subject` VARCHAR(100) DEFAULT 'General Medicine',
          `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "payments" => "CREATE TABLE IF NOT EXISTS `payments` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `user_id` INT NOT NULL,
          `amount` DECIMAL(10,2) DEFAULT 950.00,
          `trx_id` VARCHAR(100) NOT NULL,
          `payment_method` VARCHAR(50) DEFAULT 'EasyPaisa',
          `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'approved',
          `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "attempts" => "CREATE TABLE IF NOT EXISTS `attempts` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `user_id` INT NOT NULL,
          `score` INT DEFAULT 0,
          `total_questions` INT DEFAULT 200,
          `status` ENUM('in_progress', 'completed') DEFAULT 'in_progress',
          `start_time` DATETIME DEFAULT CURRENT_TIMESTAMP,
          `end_time` DATETIME DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "attempt_answers" => "CREATE TABLE IF NOT EXISTS `attempt_answers` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `attempt_id` INT NOT NULL,
          `question_id` INT NOT NULL,
          `question_number` INT NOT NULL,
          `selected_option` ENUM('A', 'B', 'C', 'D', 'E') DEFAULT NULL,
          `is_correct` TINYINT(1) DEFAULT NULL,
          `is_review` TINYINT(1) DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ];

    foreach ($tables as $name => $sql) {
        $pdo->exec($sql);
        echo "<p style='color:green;'>✓ Table '{$name}' created/verified.</p>";
    }

    // 3. Ensure Columns exist on pre-existing users table
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

    // Ensure Default Admin User exists
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
        echo "<p style='color:green;'>✓ Admin account created (admin@medmock.com / admin123).</p>";
    } else {
        $updateAdmin = $pdo->prepare("UPDATE users SET role = 'admin', payment_status = 'paid', test_credits = 9999 WHERE email = ?");
        $updateAdmin->execute([$adminEmail]);
        echo "<p style='color:blue;'>ℹ Admin account updated.</p>";
    }

    echo "<h3>Setup Complete! <a href='../auth/login.php'>Go to Login Page</a></h3>";

} catch (Exception $e) {
    echo "<p style='color:red;'>Initialization Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

