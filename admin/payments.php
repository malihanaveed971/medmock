<?php
session_start();
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: " . url("auth/login.php"));
    exit();
}

$conn = getConnection();

$stmt = $conn->query("
    SELECT p.*, u.full_name, u.email, u.phone
    FROM payments p
    JOIN users u ON p.user_id = u.id
    ORDER BY p.id DESC
");
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

include("../includes/header.php");
include("../includes/navbar.php");
?>

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="bi bi-currency-dollar"></i> EasyPaisa Payment Records</h2>
        <a href="<?php echo url('admin/dashboard.php'); ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Admin Dashboard
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Candidate</th>
                            <th>Email</th>
                            <th>TRX ID</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($payments)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No payment records found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($payments as $p): ?>
                                <tr>
                                    <td><?php echo $p['id']; ?></td>
                                    <td class="fw-bold"><?php echo htmlspecialchars($p['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($p['email']); ?></td>
                                    <td><span class="badge bg-light text-dark border border-secondary fs-6"><?php echo htmlspecialchars($p['trx_id']); ?></span></td>
                                    <td>PKR <?php echo number_format($p['amount'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($p['payment_method']); ?></td>
                                    <td><span class="badge bg-success">Approved</span></td>
                                    <td class="small text-muted"><?php echo $p['created_at']; ?></td>
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
