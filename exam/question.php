<?php
session_start();
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: " . url("auth/login.php"));
    exit();
}

if (!isset($_SESSION['attempt_id'])) {
    header("Location: start_test.php");
    exit();
}

$conn = getConnection();

$attempt_id = $_SESSION['attempt_id'];

// Check attempt start time and compute seconds left (200 minutes = 12000s)
$stmtTime = $conn->prepare("SELECT start_time, TIMESTAMPDIFF(SECOND, start_time, NOW()) AS elapsed FROM attempts WHERE id = ?");
$stmtTime->execute([$attempt_id]);
$attemptInfo = $stmtTime->fetch(PDO::FETCH_ASSOC);

if (!$attemptInfo) {
    header("Location: start_test.php");
    exit();
}

$totalTestTime = 12000; // 200 minutes
$elapsed = (int)($attemptInfo['elapsed'] ?? 0);
$seconds_left = max(0, $totalTestTime - $elapsed);

if ($seconds_left <= 0) {
    header("Location: results.php?timeout=1");
    exit();
}

// Fetch all questions for this attempt in locked sequential order
$stmt = $conn->prepare("
    SELECT aa.id AS attempt_answer_id,
           aa.question_number,
           aa.selected_option,
           aa.is_review,
           m.id AS mcq_id,
           m.question,
           m.option_a,
           m.option_b,
           m.option_c,
           m.option_d,
           m.option_e,
           m.subject
    FROM attempt_answers aa
    JOIN mcqs m ON aa.question_id = m.id
    WHERE aa.attempt_id = ?
    ORDER BY aa.question_number ASC
");
$stmt->execute([$attempt_id]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalQuestions = count($questions);

// Determine current question index
if (isset($_GET['q'])) {
    $current = (int)$_GET['q'];
    if ($current < 0) $current = 0;
    if ($current >= $totalQuestions) $current = $totalQuestions - 1;
    $_SESSION['current_question'] = $current;
} else {
    $current = $_SESSION['current_question'] ?? 0;
}

if ($current >= $totalQuestions) {
    header("Location: results.php");
    exit();
}

$q = $questions[$current];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FCPS Mock Exam | Question <?php echo $current + 1; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/exam.css">
</head>
<body class="bg-light">

<?php include("exam_header.php"); ?>

<div class="container-fluid px-lg-5 py-3">
    <div class="row g-4">

        <!-- LEFT: Question Main Area -->
        <div class="col-lg-8 col-xl-9">
            <div class="card shadow-sm border-0">
                
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <span class="badge bg-primary fs-6">Question <?php echo $current + 1; ?> of <?php echo $totalQuestions; ?></span>
                    <span class="badge bg-secondary"><?php echo htmlspecialchars($q['subject']); ?></span>
                </div>

                <div class="card-body p-4">
                    
                    <!-- Question Statement -->
                    <h4 class="lh-base fw-bold mb-4 text-dark">
                        <?php echo htmlspecialchars($q['question']); ?>
                    </h4>

                    <form id="questionForm" method="POST" action="next_question.php">
                        <input type="hidden" id="attempt_answer_id" name="attempt_answer_id" value="<?php echo $q['attempt_answer_id']; ?>">
                        <input type="hidden" name="current_index" value="<?php echo $current; ?>">

                        <!-- OPTIONS A-E -->
                        <div class="options-group mb-4">

                            <?php 
                            $options = [
                                'A' => $q['option_a'],
                                'B' => $q['option_b'],
                                'C' => $q['option_c'],
                                'D' => $q['option_d']
                            ];
                            if (!empty($q['option_e'])) {
                                $options['E'] = $q['option_e'];
                            }

                            foreach ($options as $key => $optVal): 
                                $isChecked = ($q['selected_option'] === $key) ? 'checked' : '';
                            ?>
                                <label class="card mb-3 p-3 border option-card cursor-pointer <?php echo $isChecked ? 'border-primary bg-primary-subtle' : ''; ?>" style="cursor: pointer;">
                                    <div class="d-flex align-items-center">
                                        <input class="form-check-input me-3 fs-5 option-radio" type="radio" 
                                               name="answer" value="<?php echo $key; ?>" <?php echo $isChecked; ?>>
                                        <div class="fs-5">
                                            <span class="fw-bold me-2"><?php echo $key; ?>.</span>
                                            <span><?php echo htmlspecialchars($optVal); ?></span>
                                        </div>
                                    </div>
                                </label>
                            <?php endforeach; ?>

                        </div>

                        <!-- Action Bar -->
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-3 border-top">
                            <div>
                                <button type="button" id="btnReview" class="btn <?php echo ($q['is_review'] == 1) ? 'btn-warning' : 'btn-outline-warning text-dark'; ?> fw-bold">
                                    <i class="bi bi-bookmark-star-fill"></i> 
                                    <span id="reviewText"><?php echo ($q['is_review'] == 1) ? 'Marked for Review' : 'Mark for Review'; ?></span>
                                </button>
                            </div>

                            <div class="d-flex gap-2">
                                <?php if ($current > 0): ?>
                                    <a href="question.php?q=<?php echo $current - 1; ?>" class="btn btn-outline-secondary btn-lg px-4">
                                        <i class="bi bi-chevron-left"></i> Previous
                                    </a>
                                <?php endif; ?>

                                <?php if ($current + 1 < $totalQuestions): ?>
                                    <button type="submit" name="direction" value="next" class="btn btn-primary btn-lg px-4 fw-bold">
                                        Next <i class="bi bi-chevron-right"></i>
                                    </button>
                                <?php else: ?>
                                    <a href="results.php" class="btn btn-success btn-lg px-4 fw-bold" onclick="return confirm('Submit exam final answers?');">
                                        Finish & Submit Test <i class="bi bi-check-circle-fill"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>

                    </form>

                </div>
            </div>
        </div>

        <!-- RIGHT: Question Palette Sidebar -->
        <div class="col-lg-4 col-xl-3">
            <?php include("exam_sidebar.php"); ?>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/timer.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const attemptAnswerId = document.getElementById("attempt_answer_id").value;
    const radios = document.querySelectorAll(".option-radio");
    const btnReview = document.getElementById("btnReview");
    let isReviewState = <?php echo ($q['is_review'] == 1) ? '1' : '0'; ?>;

    // Radio click styling & AJAX save
    radios.forEach(radio => {
        radio.addEventListener("change", function () {
            document.querySelectorAll(".option-card").forEach(card => {
                card.classList.remove("border-primary", "bg-primary-subtle");
            });

            if (this.checked) {
                this.closest(".option-card").classList.add("border-primary", "bg-primary-subtle");

                // Auto save answer via AJAX
                let formData = new FormData();
                formData.append("attempt_answer_id", attemptAnswerId);
                formData.append("answer", this.value);

                fetch("save_answer.php", {
                    method: "POST",
                    body: formData
                });
            }
        });
    });

    // Review Button click handler
    btnReview.addEventListener("click", function () {
        isReviewState = (isReviewState === 1) ? 0 : 1;
        const reviewText = document.getElementById("reviewText");

        if (isReviewState === 1) {
            btnReview.className = "btn btn-warning fw-bold";
            reviewText.innerText = "Marked for Review";
        } else {
            btnReview.className = "btn btn-outline-warning text-dark fw-bold";
            reviewText.innerText = "Mark for Review";
        }

        let formData = new FormData();
        formData.append("attempt_answer_id", attemptAnswerId);
        formData.append("is_review", isReviewState);

        fetch("save_answer.php", {
            method: "POST",
            body: formData
        });
    });
});
</script>

</body>
</html>