<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
$role = $_SESSION['role'] ?? 'user';
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">

        <a class="navbar-brand fw-bold fs-4" href="<?php echo url('index.php'); ?>">
            🩺 MedMock FCPS-II
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbar">
            <ul class="navbar-nav ms-auto align-items-center">

                <li class="nav-item">
                    <a class="nav-link" href="<?php echo url('index.php'); ?>">Home</a>
                </li>

                <?php if (isset($_SESSION['user_id'])): ?>

                    <?php if ($role === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link fw-bold" href="<?php echo url('admin/dashboard.php'); ?>">
                                <i class="bi bi-speedometer2"></i> Admin Panel
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-warning fw-bold" href="<?php echo url('exam/start_test.php'); ?>">
                                <i class="bi bi-play-circle-fill"></i> Test Exam Mode
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo url('admin/upload_mcqs.php'); ?>">Upload MCQs</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo url('admin/manage_mcqs.php'); ?>">Question Pool</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link fw-bold" href="<?php echo url('pages/dashboard.php'); ?>">
                                <i class="bi bi-grid-1x2-fill"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo url('pages/payment.php'); ?>">EasyPaisa Payment</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo url('pages/history.php'); ?>">Attempt History</a>
                        </li>
                    <?php endif; ?>

                    <li class="nav-item ms-lg-3">
                        <a class="btn btn-outline-danger btn-sm fw-bold px-3 py-2" href="<?php echo url('auth/logout.php'); ?>">
                            <i class="bi bi-box-arrow-right me-1"></i> Logout (<?php echo htmlspecialchars($_SESSION['full_name']); ?>)
                        </a>
                    </li>

                <?php else: ?>

                    <li class="nav-item">
                        <a class="nav-link fw-semibold px-3" href="<?php echo url('auth/login.php'); ?>">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Login
                        </a>
                    </li>

                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-primary btn-sm text-white fw-bold px-4 py-2 shadow-sm" href="<?php echo url('auth/register.php'); ?>">
                            <i class="bi bi-person-plus-fill me-1"></i> Register
                        </a>
                    </li>

                <?php endif; ?>

            </ul>
        </div>
    </div>
</nav>