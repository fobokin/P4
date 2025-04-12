<?php
include('../inc/db_connection.php');
include('../inc/functions.php');

// Check if the user is logged in as an admin
if (!is_logged_in() || !is_admin()) {
    redirect('../admin/login');
}

try {
    // Fetch all questionnaires from the database
    $stmt = $pdo->query("SELECT questionnaire_id, title, description, created_at FROM questionnaires ORDER BY created_at DESC");
    $questionnaires = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Questionnaires</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h2>Manage Questionnaires</h2>
        <p><a href="index">Back to Admin Dashboard</a> | <a href="add_questionnaire">Add New Questionnaire</a></p>

        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (!empty($questionnaires)): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($questionnaires as $questionnaire): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($questionnaire['questionnaire_id']); ?></td>
                            <td><?php echo htmlspecialchars($questionnaire['title']); ?></td>
                            <td><?php echo htmlspecialchars($questionnaire['description']); ?></td>
                            <td><?php echo htmlspecialchars($questionnaire['created_at']); ?></td>
                            <td>
                                <a href="edit_questionnaire?id=<?php echo $questionnaire['questionnaire_id']; ?>">Edit</a>
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