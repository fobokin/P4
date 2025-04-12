<?php
include('../inc/db_connection.php');
include('../inc/functions.php');

// Check if the user is logged in as an admin
if (!is_logged_in() || !is_admin()) {
    redirect('../admin/login');
}

if (isset($_GET['id']) && is_numeric($_GET['id']) && isset($_GET['questionnaire_id']) && is_numeric($_GET['questionnaire_id'])) {
    $question_id = $_GET['id'];
    $questionnaire_id = $_GET['questionnaire_id'];

    try {
        // Prepare and execute the delete query
        $stmt = $pdo->prepare("DELETE FROM questions WHERE question_id = :id");
        $stmt->bindParam(':id', $question_id);
        $stmt->execute();

        // Optionally, you might want to reorder the remaining questions
        // or update the order numbers in the database here.

        // Redirect back to the edit questionnaire page with a success message
        header("Location: edit_questionnaire?id=" . $questionnaire_id . "&delete_success=1");
        exit();

    } catch (PDOException $e) {
        // Handle database errors
        header("Location: edit_questionnaire?id=" . $questionnaire_id . "&delete_error=" . urlencode("Database error: " . $e->getMessage()));
        exit();
    }

} else {
    // If the question ID or questionnaire ID is missing or invalid
    header("Location: manage_questionnaires?error=" . urlencode("Invalid request to delete question."));
    exit();
}
?>