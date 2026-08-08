<?php
// generate_test.php
// Creates a new test attempt: picks 200 random MCQs from the bank,
// shuffles their order, and locks them into this attempt.

session_start();
require_once __DIR__ . '/config/db_connect.php';

// 1. Must be logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$user_id = $_SESSION['user_id'];

// 2. TODO (payment step, coming next): check the user has an unused
//    paid credit before letting them generate a test. For now this
//    is open so we can test the engine itself.
//
// if (!userHasPaidCredit($user_id)) {
//     header('Location: payment.php');
//     exit;
// }

const QUESTIONS_PER_TEST = 200;

try {
    // 3. Make sure we actually have enough questions in the bank
    $countStmt = $pdo->query("SELECT COUNT(*) AS total FROM mcqs");
    $totalMcqs = (int) $countStmt->fetch()['total'];

    if ($totalMcqs < QUESTIONS_PER_TEST) {
        die("Not enough questions in the bank yet. Need " . QUESTIONS_PER_TEST .
            ", currently have $totalMcqs.");
    }

    $pdo->beginTransaction();

    // 4. Pick 200 random question IDs directly in MySQL
    //    (fine at this scale - 2000 rows - no performance concern)
    $stmt = $pdo->query("SELECT id FROM mcqs ORDER BY RAND() LIMIT " . QUESTIONS_PER_TEST);
    $questionIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // 5. Shuffle again in PHP and assign a display order 1..200
    shuffle($questionIds);

    // 6. Create the test_attempts row
    $insertAttempt = $pdo->prepare(
        "INSERT INTO test_attempts
            (user_id, total_questions, correct_answers, wrong_answers, started_at)
         VALUES (:user_id, :total, 0, 0, NOW())"
    );
    $insertAttempt->execute([
        ':user_id' => $user_id,
        ':total'   => QUESTIONS_PER_TEST,
    ]);
    $attemptId = $pdo->lastInsertId();

    // 7. Insert all 200 questions into user_answers with their locked-in order
    $insertAnswer = $pdo->prepare(
        "INSERT INTO user_answers (attempt_id, mcq_id, question_order, selected_option)
         VALUES (:attempt_id, :mcq_id, :question_order, NULL)"
    );

    foreach ($questionIds as $index => $mcqId) {
        $insertAnswer->execute([
            ':attempt_id'     => $attemptId,
            ':mcq_id'         => $mcqId,
            ':question_order' => $index + 1, // 1-based order
        ]);
    }

    $pdo->commit();

    // 8. Send the user into the test
    header("Location: take_test.php?attempt_id=$attemptId");
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // Log $e->getMessage() somewhere in real production; don't show raw errors to users
    die("Something went wrong while creating your test. Please try again.");
}