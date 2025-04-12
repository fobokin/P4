<?php
include('../inc/db_connection.php');
include('../inc/functions.php');

// Check if the user is logged in as an admin
if (!is_logged_in() || !is_admin()) {
    redirect('../admin/login');
}

// Check if the student ID is provided in the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('manage_students');
    exit();
}

$student_id = $_GET['id'];
$error = '';
$success = '';
$student = null;

try {
    // Fetch the student details
    $stmt = $pdo->prepare("SELECT student_id, username, name, email FROM students WHERE student_id = :id");
    $stmt->bindParam(':id', $student_id);
    $stmt->execute();
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        $error = 'Student not found.';
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_student'])) {
        $updated_username = $_POST['updated_username'];
        $updated_name = $_POST['updated_name'];
        $updated_email = $_POST['updated_email'];
        $updated_password = $_POST['updated_password']; // Only update if provided

        if (empty($updated_username) || empty($updated_name)) {
            $error = 'Username and name are required.';
        } else {
            try {
                // Check if the username is already taken by another student
                $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM students WHERE username = :username AND student_id != :id");
                $stmt_check->bindParam(':username', $updated_username);
                $stmt_check->bindParam(':id', $student_id);
                $stmt_check->execute();
                if ($stmt_check->fetchColumn() > 0) {
                    $error = 'Username already exists for another student.';
                } else {
                    $sql_parts = ["username = :username", "name = :name", "email = :email"];
                    $params = [':username' => $updated_username, ':name' => $updated_name, ':email' => $updated_email, ':id' => $student_id];

                    if (!empty($updated_password)) {
                        $hashed_password = password_hash($updated_password, PASSWORD_DEFAULT);
                        $sql_parts[] = "password = :password";
                        $params[':password'] = $hashed_password;
                    }

                    $sql = "UPDATE students SET " . implode(", ", $sql_parts) . " WHERE student_id = :id";
                    $stmt_update = $pdo->prepare($sql);
                    $stmt_update->execute($params);

                    $success = 'Student information updated successfully. <a href="manage_students">Back to Manage Students</a>';
                }
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Student</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h2>Edit Student</h2>
        <p><a href="manage_students">Back to Manage Students</a></p>

        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php elseif ($student): ?>
            <form method="post">
                <input type="hidden" name="edit_student" value="1">
                <div class="form-group">
                    <label for="updated_username">Username:</label>
                    <input type="text" id="updated_username" name="updated_username" value="<?php echo htmlspecialchars($student['username']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="updated_password">New Password (optional):</label>
                    <input type="password" id="updated_password" name="updated_password">
                    <small>Leave blank to keep the current password.</small>
                </div>
                <div class="form-group">
                    <label for="updated_name">Name:</label>
                    <input type="text" id="updated_name" name="updated_name" value="<?php echo htmlspecialchars($student['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="updated_email">Email:</label>
                    <input type="email" id="updated_email" name="updated_email" value="<?php echo htmlspecialchars($student['email']); ?>">
                </div>
                <button type="submit">Update Student</button>
            </form>
        <?php else: ?>
            <p>Invalid student ID.</p>
        <?php endif; ?>
    </div>
</body>
</html>