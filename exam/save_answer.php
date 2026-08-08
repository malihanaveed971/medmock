<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['attempt_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized session']);
    exit();
}

require_once("../config/database.php");
$conn = getConnection();

$attempt_id        = $_SESSION['attempt_id'];
$attempt_answer_id = (int)($_POST['attempt_answer_id'] ?? 0);
$selected_option   = $_POST['answer'] ?? NULL;
$is_review         = isset($_POST['is_review']) ? (int)$_POST['is_review'] : NULL;

if (!$attempt_answer_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

// Fetch correct answer for comparison
$stmt = $conn->prepare("
    SELECT aa.question_id, m.correct_option
    FROM attempt_answers aa
    JOIN mcqs m ON aa.question_id = m.id
    WHERE aa.id = ? AND aa.attempt_id = ?
");
$stmt->execute([$attempt_answer_id, $attempt_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    echo json_encode(['success' => false, 'message' => 'Attempt question not found']);
    exit();
}

$is_correct = NULL;
if ($selected_option !== NULL && in_array($selected_option, ['A','B','C','D','E'])) {
    $is_correct = ($selected_option === $row['correct_option']) ? 1 : 0;
}

if ($is_review !== NULL) {
    $update = $conn->prepare("UPDATE attempt_answers SET is_review = ? WHERE id = ? AND attempt_id = ?");
    $update->execute([$is_review, $attempt_answer_id, $attempt_id]);
}

if ($selected_option !== NULL) {
    $update = $conn->prepare("UPDATE attempt_answers SET selected_option = ?, is_correct = ? WHERE id = ? AND attempt_id = ?");
    $update->execute([$selected_option, $is_correct, $attempt_answer_id, $attempt_id]);
}

echo json_encode(['success' => true, 'is_correct' => $is_correct]);
