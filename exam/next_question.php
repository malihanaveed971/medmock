<?php
session_start();
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: " . url("auth/login.php"));
    exit();
}

$conn = getConnection();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $attempt_answer_id = (int)($_POST['attempt_answer_id'] ?? 0);
    $selected          = $_POST['answer'] ?? NULL;
    $current_index     = (int)($_POST['current_index'] ?? 0);

    if ($attempt_answer_id > 0 && $selected !== NULL) {
        // Fetch correct option
        $stmt = $conn->prepare("
            SELECT aa.question_id, m.correct_option
            FROM attempt_answers aa
            JOIN mcqs m ON aa.question_id = m.id
            WHERE aa.id = ?
        ");
        $stmt->execute([$attempt_answer_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $is_correct = ($selected === $row['correct_option']) ? 1 : 0;

            $update = $conn->prepare("
                UPDATE attempt_answers
                SET selected_option = ?, is_correct = ?
                WHERE id = ?
            ");
            $update->execute([$selected, $is_correct, $attempt_answer_id]);
        }
    }

    $_SESSION['current_question'] = $current_index + 1;
}

header("Location: question.php");
exit();