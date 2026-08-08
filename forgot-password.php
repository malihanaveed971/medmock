<?php
/**
 * MedMock - Forgot Password Page
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedMock - Forgot Password</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <h1>Forgot Password</h1>
    <p>Enter your email address to receive a password reset link.</p>
    <form action="auth/forgot_password_process.php" method="POST">
        <div>
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required>
        </div>
        <button type="submit">Send Reset Link</button>
        <p><a href="login.php">Back to Login</a></p>
    </form>
</body>
</html>

