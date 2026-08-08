<?php
session_start();
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: " . url("auth/login.php"));
    exit();
}

$attempt_id = $_SESSION['attempt_id'] ?? ($_GET['attempt_id'] ?? 0);

if (!$attempt_id) {
    header("Location: " . url("pages/dashboard.php"));
    exit();
}

$conn = getConnection();

// Mark attempt as completed
$updateAttemptStatus = $conn->prepare("UPDATE attempts SET status = 'completed', end_time = NOW() WHERE id = ?");
$updateAttemptStatus->execute([$attempt_id]);

// Fetch all questions and user answers
$stmt = $conn->prepare("
    SELECT
        aa.question_number,
        aa.selected_option,
        aa.is_correct,
        m.id AS mcq_id,
        m.question,
        m.option_a,
        m.option_b,
        m.option_c,
        m.option_d,
        m.option_e,
        m.correct_option,
        m.explanation,
        m.subject
    FROM attempt_answers aa
    JOIN mcqs m ON aa.question_id = m.id
    WHERE aa.attempt_id = ?
    ORDER BY aa.question_number ASC
");
$stmt->execute([$attempt_id]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalQuestions = count($questions);
$correctCount = 0;
$wrongCount = 0;
$unansweredCount = 0;

foreach ($questions as $q) {
    if ($q['is_correct'] === 1) {
        $correctCount++;
    } elseif ($q['is_correct'] === 0) {
        $wrongCount++;
    } else {
        $unansweredCount++;
    }
}

$percentage = ($totalQuestions > 0) ? round(($correctCount / $totalQuestions) * 100, 2) : 0;
$isPass = ($percentage >= 70.0);

// Update score in attempts table
$updateScore = $conn->prepare("UPDATE attempts SET score = ? WHERE id = ?");
$updateScore->execute([$correctCount, $attempt_id]);

include("../includes/header.php");
include("../includes/navbar.php");
?>

<div class="container py-5">

    <!-- Top Score Summary Banner -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="p-4 p-md-5 rounded shadow text-white d-flex justify-content-between align-items-center flex-wrap gap-3 <?php echo $isPass ? 'bg-success' : 'bg-danger'; ?>">
                <div>
                    <h2 class="display-6 fw-bold mb-1">
                        <?php echo $isPass ? '🎉 Congratulations! Passed' : '⚠️ Exam Result: Needs Improvement'; ?>
                    </h2>
                    <p class="mb-0 fs-5">
                        FCPS Part-II Mock Examination Score Summary
                    </p>
                </div>
                <div class="text-center bg-white text-dark p-3 rounded shadow-sm" style="min-width: 160px;">
                    <h5 class="text-muted mb-0">Percentage</h5>
                    <h2 class="fw-bold display-6 mb-0 <?php echo $isPass ? 'text-success' : 'text-danger'; ?>"><?php echo $percentage; ?>%</h2>
                    <span class="badge <?php echo $isPass ? 'bg-success' : 'bg-danger'; ?> fs-6 mt-1"><?php echo $isPass ? 'PASS' : 'FAIL'; ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center p-3">
                <div class="card-body">
                    <h6 class="text-muted">Total Questions</h6>
                    <h2 class="fw-bold mb-0 text-dark"><?php echo $totalQuestions; ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center p-3 border-bottom border-success border-4">
                <div class="card-body">
                    <h6 class="text-success fw-bold">Correct Answers</h6>
                    <h2 class="fw-bold mb-0 text-success"><?php echo $correctCount; ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center p-3 border-bottom border-danger border-4">
                <div class="card-body">
                    <h6 class="text-danger fw-bold">Incorrect Answers</h6>
                    <h2 class="fw-bold mb-0 text-danger"><?php echo $wrongCount; ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 text-center p-3 border-bottom border-warning border-4">
                <div class="card-body">
                    <h6 class="text-warning fw-bold">Unanswered</h6>
                    <h2 class="fw-bold mb-0 text-dark"><?php echo $unansweredCount; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Review Header & Filter Pills -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h3 class="fw-bold mb-0"><i class="bi bi-file-earmark-check-fill"></i> Question Breakdown & Explanations</h3>

        <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-primary active filter-btn" data-filter="all">All (<?php echo $totalQuestions; ?>)</button>
            <button type="button" class="btn btn-outline-danger filter-btn" data-filter="wrong">Incorrect Only (<?php echo $wrongCount; ?>)</button>
            <button type="button" class="btn btn-outline-success filter-btn" data-filter="correct">Correct Only (<?php echo $correctCount; ?>)</button>
        </div>
    </div>

    <!-- Question Cards List -->
    <div id="questionList">
        <?php foreach ($questions as $q): 
            $statusClass = "wrong";
            $badgeBg = "bg-danger";
            $statusLabel = "Incorrect";

            if ($q['is_correct'] === 1) {
                $statusClass = "correct";
                $badgeBg = "bg-success";
                $statusLabel = "Correct";
            } elseif ($q['is_correct'] === NULL) {
                $statusClass = "unanswered";
                $badgeBg = "bg-secondary";
                $statusLabel = "Unanswered";
            }
        ?>
            <div class="card shadow-sm border-0 mb-4 question-item <?php echo $statusClass; ?>">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="fw-bold text-dark me-2">Q<?php echo $q['question_number']; ?>.</span>
                        <span class="badge bg-info text-dark"><?php echo htmlspecialchars($q['subject']); ?></span>
                    </div>
                    <span class="badge <?php echo $badgeBg; ?> fs-6 p-2"><?php echo $statusLabel; ?></span>
                </div>

                <div class="card-body p-4">
                    <h5 class="lh-base fw-bold mb-4 text-dark"><?php echo htmlspecialchars($q['question']); ?></h5>

                    <div class="row g-3 mb-3">
                        <?php 
                        $opts = [
                            'A' => $q['option_a'],
                            'B' => $q['option_b'],
                            'C' => $q['option_c'],
                            'D' => $q['option_d']
                        ];
                        if (!empty($q['option_e'])) $opts['E'] = $q['option_e'];

                        foreach ($opts as $key => $val):
                            $boxClass = "border bg-light text-dark";
                            $icon = "";

                            if ($key === $q['correct_option']) {
                                $boxClass = "border-success bg-success-subtle text-success fw-bold";
                                $icon = '<i class="bi bi-check-circle-fill text-success float-end fs-5"></i>';
                            }
                            if ($key === $q['selected_option'] && $key !== $q['correct_option']) {
                                $boxClass = "border-danger bg-danger-subtle text-danger fw-bold";
                                $icon = '<i class="bi bi-x-circle-fill text-danger float-end fs-5"></i>';
                            }
                        ?>
                            <div class="col-md-6">
                                <div class="p-3 rounded <?php echo $boxClass; ?>">
                                    <?php echo $icon; ?>
                                    <b><?php echo $key; ?>.</b> <?php echo htmlspecialchars($val); ?>
                                    <?php if ($key === $q['selected_option']): ?>
                                        <span class="badge bg-dark ms-2">Your Answer</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if (!empty($q['explanation'])): ?>
                        <div class="alert alert-info border-info mt-3 mb-0">
                            <h6 class="fw-bold mb-1"><i class="bi bi-lightbulb-fill text-warning"></i> Answer Explanation:</h6>
                            <p class="mb-0 text-dark"><?php echo htmlspecialchars($q['explanation']); ?></p>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Bottom Actions -->
    <div class="text-center mt-5">
        <a href="<?php echo url('exam/start_test.php'); ?>" class="btn btn-primary btn-lg px-4 me-2 fw-bold">
            <i class="bi bi-arrow-repeat"></i> Take Another Mock Exam
        </a>
        <a href="<?php echo url('pages/dashboard.php'); ?>" class="btn btn-outline-secondary btn-lg px-4">
            <i class="bi bi-house-door"></i> Back to Dashboard
        </a>
    </div>

</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const filterBtns = document.querySelectorAll(".filter-btn");
    const questionItems = document.querySelectorAll(".question-item");

    filterBtns.forEach(btn => {
        btn.addEventListener("click", function () {
            filterBtns.forEach(b => b.classList.remove("active"));
            this.classList.add("active");

            const filter = this.getAttribute("data-filter");

            questionItems.forEach(item => {
                if (filter === "all") {
                    item.style.display = "block";
                } else if (filter === "wrong" && item.classList.contains("wrong")) {
                    item.style.display = "block";
                } else if (filter === "correct" && item.classList.contains("correct")) {
                    item.style.display = "block";
                } else {
                    item.style.display = "none";
                }
            });
        });
    });
});
</script>

<?php include("../includes/footer.php"); ?>