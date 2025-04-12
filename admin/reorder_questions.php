<?php
include('../inc/db_connection.php');

// Check if the request is POST and if the necessary data is present
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['questionnaire_id']) && is_numeric($_POST['questionnaire_id']) && isset($_POST['question_id']) && is_numeric($_POST['question_id']) && isset($_POST['direction']) && in_array($_POST['direction'], ['up', 'down'])) {

    $questionnaire_id = $_POST['questionnaire_id'];
    $question_id = $_POST['question_id'];
    $direction = $_POST['direction'];

    try {
        // Get the current order of the question being moved
        $stmt_current = $pdo->prepare("SELECT `order` FROM questions WHERE question_id = :question_id AND questionnaire_id = :questionnaire_id");
        $stmt_current->bindParam(':question_id', $question_id);
        $stmt_current->bindParam(':questionnaire_id', $questionnaire_id);
        $stmt_current->execute();
        $current_order = $stmt_current->fetchColumn();

        if ($current_order !== false) {
            $target_order = ($direction === 'up') ? $current_order - 1 : $current_order + 1;

            // Find the target question to swap order with
            $stmt_target = $pdo->prepare("SELECT question_id FROM questions WHERE questionnaire_id = :questionnaire_id AND `order` = :target_order");
            $stmt_target->bindParam(':questionnaire_id', $questionnaire_id);
            $stmt_target->bindParam(':target_order', $target_order);
            $stmt_target->execute();
            $target_question_id = $stmt_target->fetchColumn();

            if ($target_question_id !== false) {
                // Perform the swap
                $pdo->beginTransaction();

                $stmt_update_current = $pdo->prepare("UPDATE questions SET `order` = :target_order WHERE question_id = :question_id");
                $stmt_update_current->bindParam(':target_order', $target_order);
                $stmt_update_current->bindParam(':question_id', $question_id);
                $stmt_update_current->execute();

                $stmt_update_target = $pdo->prepare("UPDATE questions SET `order` = :current_order WHERE question_id = :target_question_id");
                $stmt_update_target->bindParam(':current_order', $current_order);
                $stmt_update_target->bindParam(':target_question_id', $target_question_id);
                $stmt_update_target->execute();

                $pdo->commit();

                echo json_encode(['success' => true]);
                exit();
            } else {
                echo json_encode(['error' => 'Cannot move further in that direction.']);
                exit();
            }
        } else {
            echo json_encode(['error' => 'Question not found.']);
            exit();
        }

    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        exit();
    }
} else {
    echo json_encode(['error' => 'Invalid request.']);
    exit();
}
?>