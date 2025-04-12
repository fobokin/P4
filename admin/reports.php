<?php
include('../inc/db_connection.php');
include('../inc/functions.php');

// Check if the user is logged in as an admin
if (!is_logged_in() || !is_admin()) {
    redirect('../admin/login');
}

$error = '';
$reports = [];

try {
    // Fetch all questionnaires and related report data
    $stmt = $pdo->query("
        SELECT
            q.questionnaire_id,
            q.title,
            COUNT(DISTINCT sqa.student_id) AS assigned_count,
            COUNT(DISTINCT r.student_id) AS completed_count
        FROM questionnaires q
        LEFT JOIN student_questionnaire_assignments sqa ON q.questionnaire_id = sqa.questionnaire_id
        LEFT JOIN responses r ON q.questionnaire_id = r.questionnaire_id
        GROUP BY q.questionnaire_id, q.title
        ORDER BY q.title ASC
    ");
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Reports</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h2>View Questionnaire Reports</h2>
        <p><a href="index">Back to Admin Dashboard</a></p>

        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (!empty($reports)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Questionnaire Title</th>
                        <th>Assigned To</th>
                        <th>Completed By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reports as $report): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($report['title']); ?></td>
                            <td><?php echo htmlspecialchars($report['assigned_count']); ?> student(s)</td>
                            <td><?php echo htmlspecialchars($report['completed_count']); ?> student(s)</td>
                            <td>
                                <a href="view_responses?id=<?php echo $report['questionnaire_id']; ?>">View Responses</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No questionnaires have been created yet.</p>
        <?php endif; ?>
    </div>
</body>
</html>