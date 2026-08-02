<?php
session_start();
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: " . url("auth/login.php"));
    exit();
}

$conn = getConnection();
$user_id = $_SESSION['user_id'];

// Fetch user data
$stmt = $conn->prepare("SELECT payment_status, test_credits, role FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header("Location: " . url("auth/login.php"));
    exit();
}

if (($user['role'] ?? '') === 'admin') {
    header("Location: " . url("admin/dashboard.php"));
    exit();
}

$credits = (int)($user['test_credits'] ?? 0);
$hasCredits = ($credits > 0);

include __DIR__ . "/../includes/header.php";
include __DIR__ . "/../includes/navbar.php";
?>

<div class="container py-5">

    <!-- Welcome Banner -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="p-4 p-md-5 bg-primary text-white rounded shadow d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h2>
                        Welcome, <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Candidate'); ?> 👋
                    </h2>
                    <p class="mb-0 fs-5">
                        FCPS Part-II Specialized Medical Mock Examination Portal
                    </p>
                </div>
                <div>
                    <?php if ($hasCredits): ?>
                        <span class="badge bg-success fs-5 p-3 shadow-sm">
                            <i class="bi bi-ticket-perforated-fill me-1"></i> <?php echo $credits; ?> Attempt Credit(s) Available
                        </span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark fs-6 p-2 shadow-sm">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> 0 Attempt Credits (PKR 950 / Attempt)
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert for 0 Credits -->
    <?php if (!$hasCredits): ?>
        <div class="alert alert-warning alert-dismissible fade show p-4 shadow-sm border-warning" role="alert">
            <h4 class="alert-heading fw-bold"><i class="bi bi-lock-fill"></i> Test Fee: PKR 950 Per Mock Exam Attempt</h4>
            <p>You currently have 0 exam credits. Pay PKR 950 via EasyPaisa to get 1 attempt credit for a 200 MCQ randomized exam.</p>
            <hr>
            <a href="<?php echo url('pages/payment.php'); ?>" class="btn btn-warning fw-bold px-4">
                <i class="bi bi-wallet2"></i> Pay PKR 950 via EasyPaisa Now
            </a>
        </div>
    <?php endif; ?>

    <!-- Action Cards -->
    <div class="row g-4">

        <!-- Practice Test -->
        <div class="col-md-4">
            <div class="card shadow-sm text-center h-100 border-0">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="p-3 bg-primary-subtle rounded-circle d-inline-block mb-3">
                            <i class="bi bi-journal-text fs-1 text-primary"></i>
                        </div>
                        <h4 class="card-title fw-bold">FCPS Part-II Mock Exam</h4>
                        <p class="card-text text-muted">Generate a randomized 200 MCQ timed exam (Fee: PKR 950 / attempt).</p>
                    </div>

                    <div class="mt-4">
                        <?php if ($hasCredits): ?>
                            <a href="<?php echo url('exam/start_test.php'); ?>" class="btn btn-primary btn-lg w-100 fw-bold">
                                <i class="bi bi-play-circle"></i> Start Mock Test (1 Credit)
                            </a>
                        <?php else: ?>
                            <a href="<?php echo url('pages/payment.php'); ?>" class="btn btn-warning btn-lg w-100 fw-bold">
                                <i class="bi bi-wallet2"></i> Pay PKR 950 to Unlock Test
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- EasyPaisa Payment Status / Action -->
        <div class="col-md-4">
            <div class="card shadow-sm text-center h-100 border-0">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="p-3 bg-success-subtle rounded-circle d-inline-block mb-3">
                            <i class="bi bi-credit-card-2-front fs-1 text-success"></i>
                        </div>
                        <h4 class="card-title fw-bold">Buy Test Credits</h4>
                        <p class="card-text text-muted">Pay PKR 950 via EasyPaisa & enter TRX ID to instantly add attempt credits.</p>
                    </div>

                    <div class="mt-4">
                        <a href="<?php echo url('pages/payment.php'); ?>" class="btn btn-outline-success btn-lg w-100 fw-bold">
                            <i class="bi bi-plus-circle"></i> Buy Credits (PKR 950)
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- History -->
        <div class="col-md-4">
            <div class="card shadow-sm text-center h-100 border-0">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="p-3 bg-info-subtle rounded-circle d-inline-block mb-3">
                            <i class="bi bi-clock-history fs-1 text-info"></i>
                        </div>
                        <h4 class="card-title fw-bold">Attempt History</h4>
                        <p class="card-text text-muted">Review past mock test scores, percentages, and wrong answer explanations.</p>
                    </div>

                    <div class="mt-4">
                        <a href="<?php echo url('pages/history.php'); ?>" class="btn btn-outline-info btn-lg w-100 fw-bold">
                            <i class="bi bi-list-check"></i> View History
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

<?php include __DIR__ . "/../includes/footer.php"; ?>