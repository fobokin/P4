<?php
include('../inc/db_connection.php');
include('../inc/functions.php');

// Check if the user is logged in as an admin
if (!is_logged_in() || !is_admin()) {
    redirect('../admin/login');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $admin_id = $_SESSION['user_id']; // Get the ID of the logged-in admin

    if (empty($title)) {
        $error = 'Questionnaire title is required.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO questionnaires (title, description, created_by_admin_id) VALUES (:title, :description, :admin_id)");
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':admin_id', $admin_id);
            $stmt->execute();

            $success = 'Questionnaire created successfully! <a href="manage_questionnaires">Back to Manage Questionnaires</a>';
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add New Questionnaire</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h2>Add New Questionnaire</h2>
        <p><a href="manage_questionnaires">Back to Manage Questionnaires</a></p>

        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php else: ?>
            <form method="post">
                <div class="form-group">
                    <label for="title">Title:</label>
                    <input type="text" id="title" name="title" required>
                </div>
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description"></textarea>
                </div>
                <button type="submit">Create Questionnaire</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>