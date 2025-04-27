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
            max-width: 900px;
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

        h3 {
            color: #555;
            margin-top: 25px;
            margin-bottom: 15px;
        }

        h4 {
            color: #777;
            margin-top: 20px;
            margin-bottom: 10px;
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

        td {
            padding: 12px;
        }

        td.order-number {
            width: 5%;
            text-align: center;
        }

        td.actions a {
            color: #007bff;
            text-decoration: none;
            margin-right: 10px;
            transition: color 0.3s ease;
        }

        td.actions a:hover {
            color: #d9534f; /* Example: red for delete */
        }

        td.reorder-controls {
            width: 10%;
            text-align: center;
        }

        .reorder-controls {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .reorder-button {
            cursor: pointer;
            margin: 5px 0;
            font-size: 1.2em;
            border: none;
            background: none;
            padding: 5px;
            color: #555;
            transition: color 0.3s ease;
        }

        .reorder-button:hover {
            color: #007bff;
        }

        .error {
            background-color: #fdecea;
            color: #d9534f;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #d9534f;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }

        #questions-table-body tr:nth-child(even) {
            background-color: #f8f9fa;
        }
    </style>
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