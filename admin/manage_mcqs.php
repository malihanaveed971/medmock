<?php
session_start();
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: " . url("auth/login.php"));
    exit();
}

$conn = getConnection();

$msg = "";
$error = "";

// Handle Delete
if (isset($_GET['delete_id'])) {
    $delId = (int)$_GET['delete_id'];
    $stmtDel = $conn->prepare("DELETE FROM mcqs WHERE id = ?");
    $stmtDel->execute([$delId]);
    $msg = "Question #$delId successfully deleted.";
}

// Handle Add Single Question
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'add_mcq') {
    $question       = trim($_POST['question']);
    $option_a       = trim($_POST['option_a']);
    $option_b       = trim($_POST['option_b']);
    $option_c       = trim($_POST['option_c']);
    $option_d       = trim($_POST['option_d']);
    $option_e       = trim($_POST['option_e'] ?? '');
    $correct_option = strtoupper(trim($_POST['correct_option']));
    $explanation    = trim($_POST['explanation']);
    $subject        = trim($_POST['subject']);

    if (!empty($question) && !empty($option_a) && !empty($option_b) && in_array($correct_option, ['A','B','C','D','E'])) {
        $stmtAdd = $conn->prepare("
            INSERT INTO mcqs (question, option_a, option_b, option_c, option_d, option_e, correct_option, explanation, subject)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmtAdd->execute([
            $question, $option_a, $option_b, $option_c, $option_d, $option_e ?: NULL, $correct_option, $explanation, $subject ?: 'General Medicine'
        ]);
        $msg = "New MCQ added to the pool successfully!";
    } else {
        $error = "Please fill in all required question fields and select a valid correct option.";
    }
}

// Search filter
$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    $stmt = $conn->prepare("SELECT * FROM mcqs WHERE question LIKE ? OR subject LIKE ? ORDER BY id DESC LIMIT 100");
    $stmt->execute(["%$search%", "%$search%"]);
} else {
    $stmt = $conn->query("SELECT * FROM mcqs ORDER BY id DESC LIMIT 100");
}
$mcqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalInPool = (int)$conn->query("SELECT COUNT(*) FROM mcqs")->fetchColumn();

include("../includes/header.php");
include("../includes/navbar.php");
?>

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="fw-bold mb-1"><i class="bi bi-journal-text"></i> Question Bank Pool</h2>
            <p class="text-muted mb-0">Total Questions in Pool: <b><?php echo number_format($totalInPool); ?></b></p>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addMcqModal">
                <i class="bi bi-plus-circle"></i> Add Single MCQ
            </button>
            <a href="<?php echo url('admin/upload_mcqs.php'); ?>" class="btn btn-primary">
                <i class="bi bi-cloud-upload"></i> Bulk CSV Upload
            </a>
            <a href="<?php echo url('admin/dashboard.php'); ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Admin Dashboard
            </a>
        </div>
    </div>

    <?php if (!empty($msg)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Search Box -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="manage_mcqs.php" class="row g-2">
                <div class="col-md-10">
                    <input type="text" name="search" class="form-control form-control-lg"
                           placeholder="Search questions by keyword or specialty subject..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="bi bi-search"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Questions Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th>Question Statement</th>
                            <th style="width: 150px;">Subject</th>
                            <th style="width: 80px;" class="text-center">Answer</th>
                            <th style="width: 100px;" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($mcqs)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No questions found in pool.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($mcqs as $m): ?>
                                <tr>
                                    <td class="fw-bold">#<?php echo $m['id']; ?></td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?php echo htmlspecialchars($m['question']); ?></div>
                                        <div class="small text-muted mt-1">
                                            <b>A:</b> <?php echo htmlspecialchars($m['option_a']); ?> |
                                            <b>B:</b> <?php echo htmlspecialchars($m['option_b']); ?> |
                                            <b>C:</b> <?php echo htmlspecialchars($m['option_c']); ?> |
                                            <b>D:</b> <?php echo htmlspecialchars($m['option_d']); ?>
                                            <?php if (!empty($m['option_e'])): ?>
                                                | <b>E:</b> <?php echo htmlspecialchars($m['option_e']); ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($m['subject']); ?></span></td>
                                    <td class="text-center"><span class="badge bg-success fs-6"><?php echo $m['correct_option']; ?></span></td>
                                    <td class="text-center">
                                        <a href="manage_mcqs.php?delete_id=<?php echo $m['id']; ?>"
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Are you sure you want to delete this question?');">
                                            <i class="bi bi-trash"></i>
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

<!-- Modal to Add Single MCQ -->
<div class="modal fade" id="addMcqModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="manage_mcqs.php">
                <input type="hidden" name="action" value="add_mcq">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Add Single MCQ</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Question Statement</label>
                        <textarea name="question" class="form-control" rows="3" required></textarea>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Option A</label>
                            <input type="text" name="option_a" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Option B</label>
                            <input type="text" name="option_b" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Option C</label>
                            <input type="text" name="option_c" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Option D</label>
                            <input type="text" name="option_d" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Option E (Optional)</label>
                            <input type="text" name="option_e" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-success">Correct Option</label>
                            <select name="correct_option" class="form-select fw-bold" required>
                                <option value="A">Option A</option>
                                <option value="B">Option B</option>
                                <option value="C">Option C</option>
                                <option value="D">Option D</option>
                                <option value="E">Option E</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Subject / Specialty</label>
                        <input type="text" name="subject" class="form-control" placeholder="e.g. Cardiology" value="General Medicine">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Explanation / Rationale</label>
                        <textarea name="explanation" class="form-control" rows="2" placeholder="Detailed explanation for answer choice"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Save MCQ to Pool</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>
