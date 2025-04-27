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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        thead {
            background-color: #f8f9fa;
            border-bottom: 2px solid #eee;
        }

        th {
            padding: 12px;
            text-align: left;
            color: #555;
        }

        tbody tr {
            border-bottom: 1px solid #eee;
        }

        tbody tr:last-child {
            border-bottom: none;
        }

        tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        td {
            padding: 12px;
        }

        td a {
            color: #28a745; /* Green for view responses */
            text-decoration: none;
            transition: color 0.3s ease;
            border: 1px solid #28a745;
            padding: 8px 12px;
            border-radius: 5px;
            display: inline-block;
            font-size: 0.9em;
        }

        td a:hover {
            background-color: #28a745;
            color: #fff;
        }

        .error {
            background-color: #fdecea;
            color: #d9534f;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #d9534f;
        }

        .no-data {
            color: #777;
            font-style: italic;
        }
    </style>
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