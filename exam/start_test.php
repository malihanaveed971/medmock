<?php
session_start();
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: " . url("auth/login.php"));
    exit();
}

$conn = getConnection();
$user_id = $_SESSION['user_id'];

// Check candidate test credits
$stmtUser = $conn->prepare("SELECT test_credits, role FROM users WHERE id = ?");
$stmtUser->execute([$user_id]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

$credits = (int)($user['test_credits'] ?? 0);
$isAdmin = ($user['role'] === 'admin');

if ($credits <= 0 && !$isAdmin) {
    header("Location: " . url("pages/payment.php?error=no_credits"));
    exit();
}

const MAX_QUESTIONS_PER_TEST = 200;

try {
    // Clear previous exam session variables
    unset($_SESSION['attempt_id']);
    unset($_SESSION['current_question']);

    // Count total MCQs in question pool
    $totalAvailable = (int)$conn->query("SELECT COUNT(*) FROM mcqs")->fetchColumn();

    if ($totalAvailable == 0) {
        die("No questions found in the question bank pool. Please ask the admin to upload questions.");
    }

    $questionsToLoad = min(MAX_QUESTIONS_PER_TEST, $totalAvailable);

    $conn->beginTransaction();

    // Deduct 1 credit for regular candidate (admins have unlimited testing)
    if (!$isAdmin) {
        $stmtDeduct = $conn->prepare("
            UPDATE users
            SET test_credits = test_credits - 1,
                payment_status = IF(test_credits - 1 > 0, 'paid', 'unpaid')
            WHERE id = ?
        ");
        $stmtDeduct->execute([$user_id]);
    }

    // Create new test attempt
    $stmtAttempt = $conn->prepare("
        INSERT INTO attempts (user_id, score, total_questions, status, start_time)
        VALUES (?, 0, ?, 'in_progress', NOW())
    ");
    $stmtAttempt->execute([$user_id, $questionsToLoad]);
    $attempt_id = $conn->lastInsertId();

    // Dynamically pick $questionsToLoad MCQs balanced proportionally across ALL subjects/topics
    $subjectCounts = $conn->query("
        SELECT subject, COUNT(*) as total_count 
        FROM mcqs 
        WHERE subject IS NOT NULL AND TRIM(subject) != '' 
        GROUP BY subject
    ")->fetchAll(PDO::FETCH_ASSOC);

    $questionIds = [];

    if (!empty($subjectCounts)) {
        $numSubjects = count($subjectCounts);
        $baseQuotaPerSubject = (int)floor($questionsToLoad / $numSubjects);
        if ($baseQuotaPerSubject < 1) $baseQuotaPerSubject = 1;

        $subjectQuotas = [];
        $remainingNeeded = $questionsToLoad;

        // Step 1: Assign base quotas per subject
        foreach ($subjectCounts as $s) {
            $subj = $s['subject'];
            $available = (int)$s['total_count'];
            $take = min($baseQuotaPerSubject, $available);
            $subjectQuotas[$subj] = $take;
            $remainingNeeded -= $take;
        }

        // Step 2: Distribute remaining quota to subjects with surplus questions
        if ($remainingNeeded > 0) {
            foreach ($subjectCounts as $s) {
                if ($remainingNeeded <= 0) break;
                $subj = $s['subject'];
                $available = (int)$s['total_count'];
                $alreadyTaken = $subjectQuotas[$subj] ?? 0;
                $surplus = $available - $alreadyTaken;
                if ($surplus > 0) {
                    $add = min($remainingNeeded, $surplus);
                    $subjectQuotas[$subj] += $add;
                    $remainingNeeded -= $add;
                }
            }
        }

        // Step 3: Fetch question IDs per subject quota
        $stmtSubjMcqs = $conn->prepare("
            SELECT id FROM mcqs
            WHERE subject = ?
            ORDER BY RAND()
            LIMIT ?
        ");

        foreach ($subjectQuotas as $subj => $quota) {
            if ($quota > 0) {
                $stmtSubjMcqs->bindValue(1, $subj, PDO::PARAM_STR);
                $stmtSubjMcqs->bindValue(2, (int)$quota, PDO::PARAM_INT);
                $stmtSubjMcqs->execute();
                $ids = $stmtSubjMcqs->fetchAll(PDO::FETCH_COLUMN);
                $questionIds = array_merge($questionIds, $ids);
            }
        }
    }

    // Step 4: Fallback if any unallocated questions remain or no subjects defined
    if (count($questionIds) < $questionsToLoad) {
        $needed = $questionsToLoad - count($questionIds);
        $excludeClause = "";
        if (!empty($questionIds)) {
            $inList = implode(',', array_map('intval', $questionIds));
            $excludeClause = "WHERE id NOT IN ($inList)";
        }
        $stmtFallback = $conn->query("
            SELECT id FROM mcqs
            $excludeClause
            ORDER BY RAND()
            LIMIT $needed
        ");
        $fallbackIds = $stmtFallback->fetchAll(PDO::FETCH_COLUMN);
        $questionIds = array_merge($questionIds, $fallbackIds);
    }

    // Shuffle final combined question list for random distribution of topics
    shuffle($questionIds);

    // Lock selected questions for this specific candidate attempt
    $stmtInsertAnswer = $conn->prepare("
        INSERT INTO attempt_answers (attempt_id, question_id, question_number, selected_option, is_correct, is_review)
        VALUES (?, ?, ?, NULL, NULL, 0)
    ");

    $qNum = 1;
    foreach ($questionIds as $qid) {
        $stmtInsertAnswer->execute([$attempt_id, $qid, $qNum]);
        $qNum++;
    }

    $conn->commit();

    $_SESSION['attempt_id'] = $attempt_id;
    $_SESSION['current_question'] = 0;

    header("Location: question.php");
    exit();

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    die("Error starting mock test: " . htmlspecialchars($e->getMessage()));
}