<?php
include('../inc/db_connection.php');
include('../inc/functions.php');

// Check if the user is logged in as a student
if (!is_logged_in() || !is_student()) {
    redirect('../student/login');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['questionnaire_id'])) {
    $student_id = $_SESSION['user_id'];
    $questionnaire_id = $_POST['questionnaire_id'];
    $responses = $_POST['response'] ?? [];
    $submission_successful = true;

    try {
        $pdo->beginTransaction();

        foreach ($responses as $question_id => $answer) {
            $answer_text = '';
            if (is_array($answer)) {
                $answer_text = json_encode($answer); // Handle multiple checkbox answers
            } else {
                $answer_text = $answer; // Handle text, radio, and single-choice
            }

            $stmt = $pdo->prepare("INSERT INTO responses (student_id, questionnaire_id, question_id, response_text, submitted_at) VALUES (:student_id, :questionnaire_id, :question_id, :response_text, NOW())");
            $stmt->bindParam(':student_id', $student_id);
            $stmt->bindParam(':questionnaire_id', $questionnaire_id);
            $stmt->bindParam(':question_id', $question_id);
            $stmt->bindParam(':response_text', $answer_text);
            $stmt->execute();
        }

        $pdo->commit();
        $success = 'Questionnaire submitted successfully!';
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Database error: " . $e->getMessage();
        $submission_successful = false;
    }

    // Redirect to a confirmation page or back to the dashboard with a message
    header("Location: index?submission=" . ($submission_successful ? 'success' : 'failed'));
    exit();

} else {
    // If accessed directly without POST data
    redirect('index');
    exit();
}
?>