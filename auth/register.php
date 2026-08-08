<?php
require_once __DIR__ . "/../config/database.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Register | MedMock</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
background:#f4f8fb;
}

.card{
border:none;
border-radius:15px;
box-shadow:0 10px 30px rgba(0,0,0,.1);
}

.btn-primary{
background:#0d6efd;
}

</style>

</head>

<body>

<div class="container py-5">

<div class="row justify-content-center">

<div class="col-md-6">

<div class="card">

<div class="card-body p-4">

<h2 class="text-center mb-4">
Create Your MedMock Account
</h2>

<form action="<?php echo url('auth/register_process.php'); ?>" method="POST">

<div class="mb-3">

<label>Full Name</label>

<input
type="text"
name="full_name"
class="form-control"
required>

</div>

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

<div class="mb-3">

<label>Phone</label>

<input
type="text"
name="phone"
class="form-control">

</div>

<div class="mb-3">

<label>Country</label>

<input
type="text"
name="country"
class="form-control">

</div>

<button class="btn btn-primary w-100 mb-3">

Create Account

</button>

<div class="text-center">
    Already have an account? <a href="<?php echo url('auth/login.php'); ?>" class="text-decoration-none fw-semibold">Login here</a>
</div>

</form>

</div>

</div>

</div>

</div>

</div>

</body>

</html>