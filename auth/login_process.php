<?php
session_start();
require_once __DIR__ . "/../config/database.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        header("Location: " . url("auth/login.php?error=empty"));
        exit();
    }

    $pdo = getConnection();

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {

        $_SESSION['user_id']        = $user['id'];
        $_SESSION['full_name']      = $user['full_name'];
        $_SESSION['email']          = $user['email'];
        $_SESSION['role']           = $user['role'];
        $_SESSION['payment_status'] = $user['payment_status'];

        if ($user['role'] === 'admin') {
            header("Location: " . url("admin/dashboard.php"));
            exit();
        } else {
            header("Location: " . url("pages/dashboard.php"));
            exit();
        }

    } else {
        header("Location: " . url("auth/login.php?error=invalid"));
        exit();
    }
} else {
    header("Location: " . url("auth/login.php"));
    exit();
}