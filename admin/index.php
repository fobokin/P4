<?php
include('../inc/functions.php');

// Check if the user is logged in as an admin
if (!is_logged_in() || !is_admin()) {
    redirect('../admin/login');
}

$admin_id = $_SESSION['user_id'];
$admin_name = $_SESSION['user_name'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
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

        p {
            margin-bottom: 15px;
        }

        p a {
            text-decoration: none;
            color: #007bff;
            transition: color 0.3s ease;
        }

        p a:hover {
            color: #0056b3;
        }

        h3 {
            color: #555;
            margin-top: 25px;
            margin-bottom: 15px;
        }

        ul {
            list-style: none;
            padding: 0;
        }

        ul li {
            margin-bottom: 10px;
        }

        ul li a {
            display: block;
            padding: 12px 15px;
            text-decoration: none;
            background-color: #f8f9fa;
            color: #333;
            border: 1px solid #ddd;
            border-radius: 5px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        ul li a:hover {
            background-color: #e9ecef;
            color: #000;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Welcome, Administrator <?php echo htmlspecialchars($admin_name); ?>!</h2>
        <p><a href="logout">Logout</a></p>

        <h3>Admin Actions</h3>
        <ul>

            <li><a href="manage_questionnaires">Manage Questionnaires</a></li>
            <li><a href="manage_students">Manage Students</a></li>
            <li><a href="assign_questionnaires">Assign Questionnaires to Students</a></li>
            <li><a href="add_questionnaire">Add New Questionnaire</a></li>
            <li><a href="reports">View Reports</a></li>
            </ul>
    </div>
</body>
</html>