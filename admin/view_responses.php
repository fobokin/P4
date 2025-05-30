<?php
include('../inc/db_connection.php');
include('../inc/functions.php');

// Check if the user is logged in as an admin
if (!is_logged_in() || !is_admin()) {
    redirect('../admin/login');
}

// Check if the questionnaire ID is provided in the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('reports');
    exit();
}

$questionnaire_id = $_GET['id'];
$error = '';
$questionnaire = null;
$questions = [];
$student_responses = [];

try {
    // Fetch the questionnaire title
    $stmt_q = $pdo->prepare("SELECT title FROM questionnaires WHERE questionnaire_id = :id");
    $stmt_q->bindParam(':id', $questionnaire_id);
    $stmt_q->execute();
    $questionnaire = $stmt_q->fetch(PDO::FETCH_ASSOC);

    if (!$questionnaire) {
        $error = 'Questionnaire not found.';
    } else {
        // Fetch the questions for this questionnaire
        $stmt_qs = $pdo->prepare("SELECT question_id, question_text, question_type FROM questions WHERE questionnaire_id = :id ORDER BY `order` ASC");
        $stmt_qs->bindParam(':id', $questionnaire_id);
        $stmt_qs->execute();
        $questions = $stmt_qs->fetchAll(PDO::FETCH_ASSOC);

        // Fetch responses grouped by student
        $stmt_responses = $pdo->prepare("
            SELECT
                s.name AS student_name,
                r.question_id,
                r.response_text
            FROM responses r
            JOIN students s ON r.student_id = s.student_id
            WHERE r.questionnaire_id = :questionnaire_id
            ORDER BY s.name ASC
        ");
        $stmt_responses->bindParam(':questionnaire_id', $questionnaire_id);
        $stmt_responses->execute();
        $responses_data = $stmt_responses->fetchAll(PDO::FETCH_ASSOC);

        // Organize responses by student and question
        foreach ($responses_data as $response) {
            $student_responses[$response['student_name']][$response['question_id']] = $response['response_text'];
        }
    }
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Responses</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
    <style>
        .student-response {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .question-response {
            margin-bottom: 10px;
        }

        .question-text {
            font-weight: bold;
        }
    </style>
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
            max-width: 800px;
            margin: 30px auto;
        }

        h2 {
            color: #337ab7;
            margin-bottom: 20px;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }

        p a {
            color: #007bff;
            text-decoration: none;
            transition: color 0.3s ease;
            margin-right: 15px;
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

        .student-response {
            margin-bottom: 25px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        .student-response h3 {
            margin-top: 0;
            color: #555;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .question-response {
            margin-bottom: 15px;
            padding: 10px;
            border-left: 3px solid #007bff;
            background-color: #fff;
            border-radius: 3px;
        }

        .question-text {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .response-text {
            color: #666;
        }

        .no-responses {
            color: #777;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Responses for: <?php echo htmlspecialchars($questionnaire['title'] ?? 'Questionnaire'); ?></h2>
        <p><a href="reports">Back to Reports</a></p>

        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php elseif (empty($questions)): ?>
            <p>This questionnaire has no questions.</p>
        <?php elseif (empty($student_responses)): ?>
            <p>No students have submitted responses for this questionnaire yet.</p>
        <?php else: ?>
            <?php foreach ($student_responses as $student_name => $responses): ?>
                <div class="student-response">
                    <h3><?php echo htmlspecialchars($student_name); ?></h3>
                    <?php foreach ($questions as $question): ?>
                        <div class="question-response">
                        <p class="question-text"><?php echo htmlspecialchars($question['question_text']); ?> (<?php echo htmlspecialchars(ucfirst($question['question_type'])); ?>)</p>
                            <p>
                                <?php
                                $response = $responses[$question['question_id']] ?? 'No response';
                                if ($question['question_type'] === 'checkbox') {
                                    echo htmlspecialchars(implode(', ', json_decode($response, true) ?? []));
                                } else {
                                    echo htmlspecialchars($response);
                                }
                                ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>