<?php
session_start();
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: " . url("auth/login.php"));
    exit();
}

$conn = getConnection();

// Stats
$totalMcqs = (int)$conn->query("SELECT COUNT(*) FROM mcqs")->fetchColumn();
$totalUsers = (int)$conn->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$paidUsers = (int)$conn->query("SELECT COUNT(*) FROM users WHERE role = 'user' AND payment_status = 'paid'")->fetchColumn();
$totalAttempts = (int)$conn->query("SELECT COUNT(*) FROM attempts")->fetchColumn();

include("../includes/header.php");
include("../includes/navbar.php");
?>

<div class="container py-5">

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="p-4 bg-dark text-white rounded shadow d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h2 class="fw-bold mb-1"><i class="bi bi-shield-lock-fill"></i> Admin Control Panel</h2>
                    <p class="mb-0 text-white-50">Logged in as: <b>Administrator</b></p>
                </div>
                <a href="<?php echo url('auth/logout.php'); ?>" class="btn btn-outline-light">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Grid -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-primary text-white text-center p-3">
                <div class="card-body">
                    <h5 class="text-white-50">MCQs in Pool</h5>
                    <h2 class="display-5 fw-bold mb-0"><?php echo number_format($totalMcqs); ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-success text-white text-center p-3">
                <div class="card-body">
                    <h5 class="text-white-50">Paid Candidates</h5>
                    <h2 class="display-5 fw-bold mb-0"><?php echo number_format($paidUsers); ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-info text-white text-center p-3">
                <div class="card-body">
                    <h5 class="text-white-50">Total Candidates</h5>
                    <h2 class="display-5 fw-bold mb-0"><?php echo number_format($totalUsers); ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-secondary text-white text-center p-3">
                <div class="card-body">
                    <h5 class="text-white-50">Exam Attempts</h5>
                    <h2 class="display-5 fw-bold mb-0"><?php echo number_format($totalAttempts); ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <h3 class="fw-bold mb-3">Admin Actions & Tools</h3>
    <div class="row g-4">

        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100 text-center p-4">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <i class="bi bi-play-circle-fill fs-1 text-danger"></i>
                        <h4 class="mt-3 fw-bold">Test Exam Mode</h4>
                        <p class="text-muted small">Launch a mock practice test instantly without payment or credits.</p>
                    </div>
                    <a href="<?php echo url('exam/start_test.php'); ?>" class="btn btn-danger btn-lg w-100 fw-bold">
                        <i class="bi bi-play-fill"></i> Start Exam Mode
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100 text-center p-4">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <i class="bi bi-cloud-upload fs-1 text-primary"></i>
                        <h4 class="mt-3 fw-bold">Bulk Upload MCQs</h4>
                        <p class="text-muted small">Upload questions into the pool using CSV files.</p>
                    </div>
                    <a href="<?php echo url('admin/upload_mcqs.php'); ?>" class="btn btn-primary btn-lg w-100 fw-bold">
                        Upload CSV
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100 text-center p-4">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <i class="bi bi-journal-text fs-1 text-success"></i>
                        <h4 class="mt-3 fw-bold">Manage MCQ Bank</h4>
                        <p class="text-muted small">View, search, edit, or delete questions in the pool.</p>
                    </div>
                    <a href="<?php echo url('admin/manage_mcqs.php'); ?>" class="btn btn-success btn-lg w-100 fw-bold">
                        Question Pool
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100 text-center p-4">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <i class="bi bi-currency-dollar fs-1 text-warning"></i>
                        <h4 class="mt-3 fw-bold">Payments & TRX</h4>
                        <p class="text-muted small">View candidate payment history and approval requests.</p>
                    </div>
                    <a href="<?php echo url('admin/payments.php'); ?>" class="btn btn-warning btn-lg w-100 fw-bold">
                        View Payments
                    </a>
                </div>
            </div>
        </div>

    </div>

</div>

<?php include("../includes/footer.php"); ?>
