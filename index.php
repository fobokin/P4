<?php include('inc/functions.php'); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Questionnaire System</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <style>
        body {
            font-family: sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 80%;
            max-width: 400px; /* Adjust max-width as needed */
        }

        h1 {
            color: #337ab7; /* A nice blue color */
            margin-bottom: 20px;
        }

        p {
            color: #555;
            margin-bottom: 15px;
        }

        p a {
            display: inline-block;
            padding: 10px 20px;
            text-decoration: none;
            background-color: #337ab7;
            color: #fff;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        p a:hover {
            background-color: #286090;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to the Student Questionnaire System</h1>
        <p>Please choose your login type:</p>
        <p><a href="student/login">Student Login</a></p>
        <p><a href="admin/login">Admin Login</a></p>
    </div>
</body>
</html>