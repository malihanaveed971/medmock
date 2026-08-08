<?php
session_start();
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: " . url("auth/login.php"));
    exit();
}

$conn = getConnection();
$user_id = $_SESSION['user_id'];

// Get user payment status & credits
$stmt = $conn->prepare("SELECT payment_status, test_credits, trx_id FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);

$msg = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $trx_id = trim($_POST['trx_id'] ?? '');

    if (empty($trx_id)) {
        $error = "Please enter a valid EasyPaisa Transaction ID (TRX ID).";
    } else {
        try {
            // Automated Payment Approval: Save payment record of PKR 950 and grant +1 attempt credit
            $stmtPay = $conn->prepare("
                INSERT INTO payments (user_id, amount, trx_id, payment_method, status)
                VALUES (?, 950.00, ?, 'EasyPaisa', 'approved')
            ");
            $stmtPay->execute([$user_id, $trx_id]);

            // Increment test credits by 1
            $stmtUser = $conn->prepare("
                UPDATE users
                SET payment_status = 'paid', test_credits = test_credits + 1, trx_id = ?
                WHERE id = ?
            ");
            $stmtUser->execute([$trx_id, $user_id]);

            // Refresh user info
            $stmtRef = $conn->prepare("SELECT payment_status, test_credits, trx_id FROM users WHERE id = ?");
            $stmtRef->execute([$user_id]);
            $userData = $stmtRef->fetch(PDO::FETCH_ASSOC);

            $_SESSION['payment_status'] = 'paid';
            $_SESSION['test_credits']   = $userData['test_credits'];

            $msg = "Payment of PKR 950 verified! You have been granted 1 Mock Test Attempt credit (Total Credits: " . $userData['test_credits'] . ").";
        } catch (Exception $e) {
            $error = "Error processing payment: " . $e->getMessage();
        }
    }
}

include("../includes/header.php");
include("../includes/navbar.php");
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-primary text-white py-3">
                    <h4 class="mb-0"><i class="bi bi-wallet2"></i> EasyPaisa Test Fee Payment (PKR 950 / Attempt)</h4>
                </div>

                <div class="card-body p-4">

                    <?php if (!empty($msg)): ?>
                        <div class="alert alert-success d-flex align-items-center mb-4">
                            <i class="bi bi-check-circle-fill fs-3 me-3"></i>
                            <div>
                                <h5>Payment Activated!</h5>
                                <p class="mb-0"><?php echo htmlspecialchars($msg); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger mb-4">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Current Credit Balance Banner -->
                    <div class="bg-light p-3 rounded border mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <span class="text-muted fw-semibold">Available Mock Test Credits:</span>
                            <h3 class="fw-bold mb-0 text-primary"><?php echo (int)($userData['test_credits'] ?? 0); ?> Attempt Credit(s)</h3>
                        </div>
                        <?php if (($userData['test_credits'] ?? 0) > 0): ?>
                            <a href="<?php echo url('exam/start_test.php'); ?>" class="btn btn-success fw-bold px-4">
                                <i class="bi bi-play-circle-fill"></i> Start Mock Exam Now
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- Instructions -->
                    <div class="alert alert-info border-info mb-4">
                        <h5 class="fw-bold"><i class="bi bi-info-circle"></i> EasyPaisa Payment Instructions:</h5>
                        <ol class="mb-0 ps-3">
                            <li>Open your <b>EasyPaisa Mobile App</b> or dial <b>*786#</b>.</li>
                            <li>Send <b>PKR 950</b> (Test Attempt Fee) to:
                                <ul>
                                    <li><b>Account Title:</b> MedMock FCPS Prep</li>
                                    <li><b>Account / Mobile Number:</b> 0345-6591558 (03456591558)</li>
                                </ul>
                            </li>
                            <li>Copy the <b>Transaction ID (TRX ID)</b> received in your EasyPaisa SMS/Receipt.</li>
                            <li>Enter your TRX ID below to instantly activate 1 test attempt!</li>
                        </ol>
                    </div>

                    <!-- Submission Form -->
                    <form method="POST" action="payment.php" class="bg-light p-4 rounded border">
                        <div class="mb-3">
                            <label for="trx_id" class="form-label fw-bold">EasyPaisa Transaction ID (TRX ID)</label>
                            <input type="text" class="form-control form-control-lg" id="trx_id" name="trx_id"
                                   placeholder="e.g. 104928374928" required>
                            <div class="form-text">Amount per attempt: <b>PKR 950</b>. Instant automated credit allocation upon submission.</div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold">
                            <i class="bi bi-shield-check"></i> Submit TRX ID & Get 1 Attempt Credit (PKR 950)
                        </button>
                    </form>

                </div>
            </div>

            <div class="text-center">
                <a href="<?php echo url('pages/dashboard.php'); ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Return to Candidate Dashboard
                </a>
            </div>

        </div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
