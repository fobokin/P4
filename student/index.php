<?php
include('../inc/db_connection.php');
include('../inc/functions.php');

// Check if the user is logged in as a student
if (!is_logged_in() || !is_student()) {
    redirect('../student/login');
}

$student_id = $_SESSION['user_id'];
$student_name = $_SESSION['user_name'];
$error = '';
$assigned_questionnaires = [];

$submission_message = '';
if (isset($_GET['submission'])) {
    if ($_GET['submission'] === 'success') {
        $submission_message = '<div class="success">Questionnaire submitted successfully!</div>';
    } elseif ($_GET['submission'] === 'failed') {
        $submission_message = '<div class="error">Error submitting questionnaire. Please try again.</div>';
    }
}

try {
    // Fetch questionnaires assigned to the student along with their submission status
    $stmt = $pdo->prepare("
        SELECT
            q.questionnaire_id,
            q.title,
            q.description,
            COUNT(r.response_id) AS responses_count
        FROM questionnaires q
        JOIN student_questionnaire_assignments sqa ON q.questionnaire_id = sqa.questionnaire_id
        LEFT JOIN responses r ON sqa.student_id = r.student_id AND q.questionnaire_id = r.questionnaire_id
        WHERE sqa.student_id = :student_id
        GROUP BY q.questionnaire_id
        ORDER BY q.title ASC
    ");
    $stmt->bindParam(':student_id', $student_id);
    $stmt->execute();
    $assigned_questionnaires = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h2>Welcome, <?php echo htmlspecialchars($student_name); ?>!</h2>
        <p><a href="logout">Logout</a></p>

        <?php if ($submission_message): ?>
            <?php echo $submission_message; ?>
        <?php endif; ?>

        <h3>Available Questionnaires</h3>

        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (!empty($assigned_questionnaires)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($assigned_questionnaires as $questionnaire): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($questionnaire['title']); ?></td>
                            <td><?php echo htmlspecialchars($questionnaire['description']); ?></td>
                            <td>
                                <?php if ($questionnaire['responses_count'] > 0): ?>
                                    Completed
                                <?php else: ?>
                                    Not Started
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($questionnaire['responses_count'] == 0): ?>
                                    <a href="take_questionnaire?id=<?php echo $questionnaire['questionnaire_id']; ?>">Take Questionnaire</a>
                                <?php else: ?>
                                    Completed
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No questionnaires have been assigned to you yet.</p>
        <?php endif; ?>
    </div>
</body>
</html>