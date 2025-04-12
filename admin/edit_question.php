<?php

include('../inc/db_connection.php');

include('../inc/functions.php');



// Check if the user is logged in as an admin

if (!is_logged_in() || !is_admin()) {

    redirect('../admin/login');

}



// Check if the question ID is provided in the URL

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    redirect('manage_questionnaires');

    exit();

}



$question_id = $_GET['id'];

$error = '';

$success = '';

$question = null;



try {

    // Fetch the question details

    $stmt = $pdo->prepare("SELECT questionnaire_id, question_text, question_type, options FROM questions WHERE question_id = :id");

    $stmt->bindParam(':id', $question_id);

    $stmt->execute();

    $question = $stmt->fetch(PDO::FETCH_ASSOC);



    if (!$question) {

        $error = 'Question not found.';

    }



    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $question_text = $_POST['question_text'];

        $question_type = $_POST['question_type'];

        $options = isset($_POST['options']) ? json_encode(explode("\n", $_POST['options'])) : null;

        $questionnaire_id = $question['questionnaire_id']; // Keep track of the questionnaire ID for redirection



        if (empty($question_text)) {

            $error = 'Question text is required.';

        } else {

            $stmt_update = $pdo->prepare("UPDATE questions SET question_text = :question_text, question_type = :question_type, options = :options WHERE question_id = :id");

            $stmt_update->bindParam(':question_text', $question_text);

            $stmt_update->bindParam(':question_type', $question_type);

            $stmt_update->bindParam(':options', $options);

            $stmt_update->bindParam(':id', $question_id);

            $stmt_update->execute();



            $success = 'Question updated successfully! <a href="edit_questionnaire?id=' . $questionnaire_id . '">Back to Edit Questionnaire</a>';

        }

    }

} catch (PDOException $e) {

    $error = "Database error: " . $e->getMessage();

}

?>



<!DOCTYPE html>

<html>

<head>

    <title>Edit Question</title>

    <link rel="stylesheet" type="text/css" href="../css/style.css">

</head>

<body>

    <div class="container">

        <h2>Edit Question</h2>

        <p><a href="edit_questionnaire?id=<?php echo $question['questionnaire_id'] ?? ''; ?>">Back to Edit Questionnaire</a></p>



        <?php if ($error): ?>

            <div class="error"><?php echo $error; ?></div>

        <?php endif; ?>



        <?php if ($success): ?>

            <div class="success"><?php echo $success; ?></div>

        <?php elseif ($question): ?>

            <form method="post">

                <div class="form-group">

                    <label for="question_text">Question Text:</label>

                    <textarea id="question_text" name="question_text" required><?php echo htmlspecialchars($question['question_text']); ?></textarea>

                </div>

                <div class="form-group">

                    <label for="question_type">Question Type:</label>

                    <select id="question_type" name="question_type">

                        <option value="text" <?php if ($question['question_type'] === 'text') echo 'selected'; ?>>Text Area</option>

                        <option value="radio" <?php if ($question['question_type'] === 'radio') echo 'selected'; ?>>Multiple Choice (Radio)</option>

                        <option value="checkbox" <?php if ($question['question_type'] === 'checkbox') echo 'selected'; ?>>Multiple Choice (Checkbox)</option>

                    </select>

                </div>

                <div id="options-group" class="form-group" style="display: <?php echo ($question['question_type'] === 'radio' || $question['question_type'] === 'checkbox') ? 'block' : 'none'; ?>;">

                    <label for="options">Options (one per line):</label>

                    <textarea id="options" name="options"><?php echo htmlspecialchars(implode("\n", json_decode($question['options'] ?? '[]', true))); ?></textarea>

                    <small>Enter each option on a new line for Radio and Checkbox questions.</small>

                </div>

                <button type="submit">Update Question</button>

            </form>

        <?php else: ?>

            <p>Invalid question ID.</p>

        <?php endif; ?>

    </div>



    <script>

        const questionTypeSelect = document.getElementById('question_type');

        const optionsGroup = document.getElementById('options-group');



        questionTypeSelect.addEventListener('change', function() {

            if (this.value === 'radio' || this.value === 'checkbox') {

                optionsGroup.style.display = 'block';

            } else {

                optionsGroup.style.display = 'none';

            }

        });

    </script>

</body>

</html>