<?php
session_start();
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: " . url("auth/login.php"));
    exit();
}

$conn = getConnection();
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT * FROM attempts
    WHERE user_id = ?
    ORDER BY id DESC
");
$stmt->execute([$user_id]);
$attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);

include("../includes/header.php");
include("../includes/navbar.php");
?>

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="bi bi-clock-history"></i> Mock Exam Attempt History</h2>
        <a href="<?php echo url('pages/dashboard.php'); ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Dashboard
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Attempt #</th>
                            <th>Date & Time</th>
                            <th>Total Questions</th>
                            <th>Correct Score</th>
                            <th>Percentage</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($attempts)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">You have not taken any mock tests yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($attempts as $att): 
                                $pct = ($att['total_questions'] > 0) ? round(($att['score'] / $att['total_questions']) * 100, 2) : 0;
                                $isPass = ($pct >= 70.0);
                            ?>
                                <tr>
                                    <td class="fw-bold">#<?php echo $att['id']; ?></td>
                                    <td><?php echo $att['start_time']; ?></td>
                                    <td><?php echo $att['total_questions']; ?></td>
                                    <td class="fw-bold text-primary"><?php echo $att['score']; ?> / <?php echo $att['total_questions']; ?></td>
                                    <td>
                                        <span class="badge <?php echo $isPass ? 'bg-success' : 'bg-danger'; ?> fs-6">
                                            <?php echo $pct; ?>%
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo ucfirst($att['status']); ?></span>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?php echo url('exam/results.php?attempt_id=' . $att['id']); ?>" class="btn btn-sm btn-outline-primary fw-bold">
                                            <i class="bi bi-eye-fill"></i> View Review
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php include("../includes/footer.php"); ?>