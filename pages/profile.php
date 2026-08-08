<?php
session_start();
include("../includes/header.php");
include("../includes/navbar.php");
?>

<div class="container py-5">

<h2>My Profile</h2>

<p>Name: <?php echo htmlspecialchars($_SESSION['full_name']); ?></p>

<p>Email: <?php echo htmlspecialchars($_SESSION['email']); ?></p>

</div>

<?php include("../includes/footer.php"); ?>