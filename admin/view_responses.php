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