<?php

include('../inc/db_connection.php');

include('../inc/functions.php');



// Check if the user is logged in as a student

if (!is_logged_in() || !is_student()) {

    redirect('../student/login');

}



// Check if the questionnaire ID is provided in the URL

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    redirect('index'); // Redirect to student dashboard if no valid ID

    exit();

}



$questionnaire_id = $_GET['id'];

$student_id = $_SESSION['user_id'];

$error = '';

$questionnaire = null;

$questions = [];



try {

    // Check if the questionnaire is assigned to the student

    $stmt_check_assignment = $pdo->prepare("SELECT COUNT(*) FROM student_questionnaire_assignments WHERE student_id = :student_id AND questionnaire_id = :questionnaire_id");

    $stmt_check_assignment->bindParam(':student_id', $student_id);

    $stmt_check_assignment->bindParam(':questionnaire_id', $questionnaire_id);

    $stmt_check_assignment->execute();



    if ($stmt_check_assignment->fetchColumn() == 0) {

        $error = 'This questionnaire is not assigned to you.';

    } else {

        // Fetch the questionnaire details

        $stmt_q = $pdo->prepare("SELECT title, description FROM questionnaires WHERE questionnaire_id = :id");

        $stmt_q->bindParam(':id', $questionnaire_id);

        $stmt_q->execute();

        $questionnaire = $stmt_q->fetch(PDO::FETCH_ASSOC);



        if (!$questionnaire) {

            $error = 'Questionnaire not found.';

        } else {

            // Fetch the questions for this questionnaire

            $stmt_qs = $pdo->prepare("SELECT question_id, question_text, question_type, options FROM questions WHERE questionnaire_id = :id ORDER BY `order` ASC");

            $stmt_qs->bindParam(':id', $questionnaire_id);

            $stmt_qs->execute();

            $questions = $stmt_qs->fetchAll(PDO::FETCH_ASSOC);

        }

    }

} catch (PDOException $e) {

    $error = "Database error: " . $e->getMessage();

}

?>



<!DOCTYPE html>

<html>

<head>

    <title>Take Questionnaire</title>

    <link rel="stylesheet" type="text/css" href="../css/style.css">
    <style>
        body {
            font-family: sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            max-width: 700px;
            margin: 30px auto;
        }

        h2 {
            color: #337ab7;
            margin-bottom: 10px;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }

        p {
            color: #555;
            margin-bottom: 20px;
        }

        p a {
            color: #007bff;
            text-decoration: none;
            transition: color 0.3s ease;
            margin-top: 20px;
            display: block;
        }

        p a:hover {
            color: #0056b3;
        }

        .error {
            background-color: #fdecea;
            color: #d9534f;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #d9534f;
        }

        .question {
            background-color: #f9f9f9;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .question h3 {
            color: #333;
            margin-top: 0;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }

        .question textarea {
            width: calc(100% - 12px);
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            margin-top: 10px;
        }

        .question input[type="radio"],
        .question input[type="checkbox"] {
            margin-right: 8px;
            margin-top: 8px;
        }

        .question label {
            display: inline-block;
            margin-right: 15px;
            color: #555;
        }

        .question div {
            margin-bottom: 8px;
        }

        button[type="submit"] {
            background-color: #007bff; /* Blue for submit */
            color: #fff;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            width: 150px;
        }

        button[type="submit"]:hover {
            background-color: #0056b3;
        }

        .no-questions {
            color: #777;
            font-style: italic;
        }
    </style>

</head>

<body>

    <div class="container">

        <h2><?php echo htmlspecialchars($questionnaire['title'] ?? 'Questionnaire'); ?></h2>

        <p><?php echo htmlspecialchars($questionnaire['description'] ?? ''); ?></p>



        <?php if ($error): ?>

            <div class="error"><?php echo $error; ?></div>

        <?php elseif (empty($questions)): ?>

            <p>This questionnaire has no questions.</p>

        <?php else: ?>

            <form method="post" action="submit_questionnaire.php">

                <input type="hidden" name="questionnaire_id" value="<?php echo $questionnaire_id; ?>">

                <?php foreach ($questions as $index => $question): ?>

                    <div class="question">

                        <h3><?php echo ($index + 1) . ". " . htmlspecialchars($question['question_text']); ?></h3>

                        <?php if ($question['question_type'] === 'text'): ?>

                            <textarea name="response[<?php echo $question['question_id']; ?>]"></textarea>

                        <?php elseif ($question['question_type'] === 'radio'): ?>

                            <?php

                            $options = json_decode($question['options'], true);

                            if (is_array($options)):

                                foreach ($options as $option): ?>

                                    <div>

                                        <input type="radio" name="response[<?php echo $question['question_id']; ?>]" value="<?php echo htmlspecialchars($option); ?>" required>

                                        <label><?php echo htmlspecialchars($option); ?></label>

                                    </div>

                                <?php endforeach;

                            endif;

                            ?>

                        <?php elseif ($question['question_type'] === 'checkbox'): ?>

                            <?php

                            $options = json_decode($question['options'], true);

                            if (is_array($options)):

                                foreach ($options as $option): ?>

                                    <div>

                                        <input type="checkbox" name="response[<?php echo $question['question_id']; ?>][]" value="<?php echo htmlspecialchars($option); ?>">

                                        <label><?php echo htmlspecialchars($option); ?></label>

                                    </div>

                                <?php endforeach;

                            endif;

                            ?>

                        <?php endif; ?>

                    </div>

                <?php endforeach; ?>

                <button type="submit">Submit Questionnaire</button>

            </form>

        <?php endif; ?>



        <p><a href="index">Back to Dashboard</a></p>

    </div>

</body>

</html>