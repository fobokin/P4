<?php
include('../inc/db_connection.php');
include('../inc/functions.php');

// Check if the user is logged in as an admin
if (!is_logged_in() || !is_admin()) {
    redirect('../admin/login');
}

// Check if the questionnaire ID is provided in the URL
if (!isset($_GET['questionnaire_id']) || !is_numeric($_GET['questionnaire_id'])) {
    redirect('manage_questionnaires');
    exit();
}

$questionnaire_id = $_GET['questionnaire_id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question_text = $_POST['question_text'];
    $question_type = 'text'; // Force the question type to 'text'
    $options = ''; // Set options to an empty string for Text Area

    if (empty($question_text)) {
        $error = 'Question text is required.';
    } else {
        try {
            // Get the next available order for the questions in this questionnaire
            $stmt_order = $pdo->prepare("SELECT MAX(`order`) AS max_order FROM questions WHERE questionnaire_id = :questionnaire_id");
            $stmt_order->bindParam(':questionnaire_id', $questionnaire_id);
            $stmt_order->execute();
            $result_order = $stmt_order->fetch(PDO::FETCH_ASSOC);
            $next_order = ($result_order['max_order'] !== null) ? $result_order['max_order'] + 1 : 1;

            $stmt = $pdo->prepare("INSERT INTO questions (questionnaire_id, question_text, question_type, options, `order`) VALUES (:questionnaire_id, :question_text, :question_type, :options, :order)");
            $stmt->bindParam(':questionnaire_id', $questionnaire_id);
            $stmt->bindParam(':question_text', $question_text);
            $stmt->bindParam(':question_type', $question_type);
            $stmt->bindParam(':options', $options);
            $stmt->bindParam(':order', $next_order, PDO::PARAM_INT);
            $stmt->execute();

            $success = 'Text Area question added successfully! <a href="edit_questionnaire?id=' . $questionnaire_id . '">Back to Edit Questionnaire</a>';
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add New Text Area Question</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h2>Add New Text Area Question</h2>
        <p><a href="edit_questionnaire?id=<?php echo $questionnaire_id; ?>">Back to Edit Questionnaire</a></p>

        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php else: ?>
            <form method="post">
                <div class="form-group">
                    <label for="question_text">Question Text:</label>
                    <textarea id="question_text" name="question_text" required></textarea>
                </div>
                <input type="hidden" name="question_type" value="text">
                <button type="submit">Add Question</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>