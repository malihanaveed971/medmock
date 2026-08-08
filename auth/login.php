<?php
require_once __DIR__ . "/../config/database.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Login | MedMock</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body style="background:#f4f8fb;">

<div class="container mt-5">

<div class="row justify-content-center">

<div class="col-md-5">

<div class="card shadow">

<div class="card-body">

<h3 class="text-center mb-4">

MedMock Login

</h3>

<?php

if(isset($_GET['registered'])){
    echo "<div class='alert alert-success'>Registration Successful! Please Login.</div>";
}

if(isset($_GET['error'])){
    echo "<div class='alert alert-danger'>Invalid Email or Password.</div>";
}

?>

<form action="<?php echo url('auth/login_process.php'); ?>" method="POST">

<div class="mb-3">

<label>Email</label>

<input
type="email"
name="email"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Password</label>

<input
type="password"
name="password"
class="form-control"
required>

</div>

<button class="btn btn-primary w-100 mb-3">

Login

</button>

<div class="text-center">
    Don't have an account? <a href="<?php echo url('auth/register.php'); ?>" class="text-decoration-none fw-semibold">Register here</a>
</div>

</form>

</div>

</div>

</div>

</div>

</div>

</body>

</html>