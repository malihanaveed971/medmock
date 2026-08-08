<?php

require_once __DIR__ . "/../config/database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $phone = trim($_POST['phone'] ?? '');
    $country = trim($_POST['country'] ?? '');

    $pdo = getConnection();

    // Check if email already exists
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);

    if ($check->rowCount() > 0) {
        die("Email already registered.");
    }

    // Insert user
    $stmt = $pdo->prepare("
        INSERT INTO users
        (full_name, email, password, phone, country)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $full_name,
        $email,
        $password,
        $phone,
        $country
    ]);

    header("Location: " . url("auth/login.php?registered=1"));
    exit();
}
?>