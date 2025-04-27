<?php
include('../inc/db_connection.php');
include('../inc/functions.php');

// Check if the user is logged in as an admin
if (!is_logged_in() || !is_admin()) {
    redirect('../admin/login');
}

$error = '';
$success = '';
$students = [];

try {
    // Fetch all students from the database
    $stmt = $pdo->query("SELECT student_id, username, name, email, created_at FROM students ORDER BY name ASC");
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

// Handle adding new student (basic implementation on the same page for now)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $new_username = $_POST['new_username'];
    $new_password = $_POST['new_password'];
    $new_name = $_POST['new_name'];
    $new_email = $_POST['new_email'];

    if (empty($new_username) || empty($new_password) || empty($new_name)) {
        $error = 'Username, password, and name are required for a new student.';
    } else {
        try {
            // Check if the username already exists
            $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM students WHERE username = :username");
            $stmt_check->bindParam(':username', $new_username);
            $stmt_check->execute();
            if ($stmt_check->fetchColumn() > 0) {
                $error = 'Username already exists.';
            } else {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt_insert = $pdo->prepare("INSERT INTO students (username, password, name, email) VALUES (:username, :password, :name, :email)");
                $stmt_insert->bindParam(':username', $new_username);
                $stmt_insert->bindParam(':password', $hashed_password);
                $stmt_insert->bindParam(':name', $new_name);
                $stmt_insert->bindParam(':email', $new_email);
                $stmt_insert->execute();
                $success = 'New student added successfully. <a href="manage_students">Refresh</a>';
                // Clear the form fields after successful addition
                $_POST = [];
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Students</title>
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
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        h2 {
            color: #337ab7;
            margin-bottom: 10px;
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
            margin-top: 0;
            margin-bottom: 15px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
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
            color: #007bff;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        td a:hover {
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

        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }

        .form-group input[type="text"],
        .form-group input[type="password"],
        .form-group input[type="email"] {
            width: calc(100% - 12px);
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        .form-group small {
            display: block;
            margin-top: 5px;
            color: #777;
        }

        button[type="submit"] {
            background-color: #28a745; /* Green for Add */
            color: #fff;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            width: 150px; /* Adjust button width */
        }

        button[type="submit"]:hover {
            background-color: #218838;
        }

        .no-data {
            color: #777;
            font-style: italic;
        }

        /* Style for the Add New Student form */
        #add-student-form {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            border: 1px solid #eee;
        }

        #add-student-form h3 {
            margin-top: 0;
            color: #333;
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Manage Students</h2>
        <p><a href="index">Back to Admin Dashboard</a></p>

        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>

        <h3>Current Students</h3>
        <?php if (!empty($students)): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                            <td><?php echo htmlspecialchars($student['username']); ?></td>
                            <td><?php echo htmlspecialchars($student['name']); ?></td>
                            <td><?php echo htmlspecialchars($student['email']); ?></td>
                            <td><?php echo htmlspecialchars($student['created_at']); ?></td>
                            <td>
                                <a href="edit_student?id=<?php echo $student['student_id']; ?>">Edit</a>
                                </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No students have been added yet.</p>
        <?php endif; ?>

        <h3>Add New Student</h3>
        <form method="post">
            <input type="hidden" name="add_student" value="1">
            <div class="form-group">
                <label for="new_username">Username:</label>
                <input type="text" id="new_username" name="new_username" required>
            </div>
            <div class="form-group">
                <label for="new_password">Password:</label>
                <input type="password" id="new_password" name="new_password" required>
                <small>The password will be hashed before storing.</small>
            </div>
            <div class="form-group">
                <label for="new_name">Name:</label>
                <input type="text" id="new_name" name="new_name" required>
            </div>
            <div class="form-group">
                <label for="new_email">Email:</label>
                <input type="email" id="new_email" name="new_email">
            </div>
            <button type="submit">Add Student</button>
        </form>
    </div>
</body>
</html>