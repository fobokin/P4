<?php
include('../inc/db_connection.php');
include('../inc/functions.php');

// Check if the user is logged in as an admin
if (!is_logged_in() || !is_admin()) {
    redirect('../admin/login');
}

$error = '';
$success = '';
$questionnaires = [];
$students = [];

try {
    // Fetch all questionnaires
    $stmt_q = $pdo->query("SELECT questionnaire_id, title FROM questionnaires ORDER BY title ASC");
    $questionnaires = $stmt_q->fetchAll(PDO::FETCH_ASSOC);

    // Fetch all students
    $stmt_s = $pdo->query("SELECT student_id, name FROM students ORDER BY name ASC");
    $students = $stmt_s->fetchAll(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign'])) {
        if (isset($_POST['questionnaires']) && is_array($_POST['questionnaires']) && isset($_POST['students']) && is_array($_POST['students'])) {
            $assigned_count = 0;
            try {
                $pdo->beginTransaction();
                foreach ($_POST['students'] as $student_id) {
                    foreach ($_POST['questionnaires'] as $questionnaire_id) {
                        // Check if the assignment already exists
                        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM student_questionnaire_assignments WHERE student_id = :student_id AND questionnaire_id = :questionnaire_id");
                        $stmt_check->bindParam(':student_id', $student_id);
                        $stmt_check->bindParam(':questionnaire_id', $questionnaire_id);
                        $stmt_check->execute();
                        if ($stmt_check->fetchColumn() == 0) {
                            // Assign the questionnaire to the student
                            $stmt_assign = $pdo->prepare("INSERT INTO student_questionnaire_assignments (student_id, questionnaire_id, assigned_at) VALUES (:student_id, :questionnaire_id, NOW())");
                            $stmt_assign->bindParam(':student_id', $student_id);
                            $stmt_assign->bindParam(':questionnaire_id', $questionnaire_id);
                            $stmt_assign->execute();
                            $assigned_count++;
                        }
                    }
                }
                $pdo->commit();
                if ($assigned_count > 0) {
                    $success = "Successfully assigned $assigned_count questionnaires to students.";
                } else {
                    $success = "No new assignments were made (some or all assignments may already exist).";
                }
            } catch (PDOException $e) {
                $pdo->rollBack();
                $error = "Database error: " . $e->getMessage();
            }
        } else {
            $error = 'Please select at least one questionnaire and one student.';
        }
    }

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assign Questionnaires</title>
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
            max-width: 600px;
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

        .section {
            margin-bottom: 25px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        .section h3 {
            margin-top: 0;
            color: #555;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .checkbox-list {
            max-height: 150px;
            overflow-y: auto;
            border: 1px solid #eee;
            padding: 10px;
            border-radius: 3px;
            background-color: #fff;
        }

        .checkbox-list label {
            display: block;
            margin-bottom: 8px;
            color: #333;
        }

        .checkbox-list label input[type="checkbox"] {
            margin-right: 8px;
        }

        button[type="submit"] {
            background-color: #007bff; /* Blue for assign */
            color: #fff;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            width: 100%;
        }

        button[type="submit"]:hover {
            background-color: #0056b3;
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

        .no-data {
            color: #777;
            font-style: italic;
        }
    </style>
    <style>
        .section {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .section h3 {
            margin-top: 0;
        }

        .checkbox-list {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #eee;
            padding: 10px;
            border-radius: 3px;
        }

        .checkbox-list label {
            display: block;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Assign Questionnaires to Students</h2>
        <p><a href="index">Back to Admin Dashboard</a></p>

        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="section">
                <h3>Select Questionnaires</h3>
                <?php if (!empty($questionnaires)): ?>
                    <div class="checkbox-list">
                        <?php foreach ($questionnaires as $questionnaire): ?>
                            <label>
                                <input type="checkbox" name="questionnaires[]" value="<?php echo $questionnaire['questionnaire_id']; ?>">
                                <?php echo htmlspecialchars($questionnaire['title']); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No questionnaires available.</p>
                <?php endif; ?>
            </div>

            <div class="section">
                <h3>Select Students</h3>
                <?php if (!empty($students)): ?>
                    <div class="checkbox-list">
                        <?php foreach ($students as $student): ?>
                            <label>
                                <input type="checkbox" name="students[]" value="<?php echo $student['student_id']; ?>">
                                <?php echo htmlspecialchars($student['name']); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No students available.</p>
                <?php endif; ?>
            </div>

            <button type="submit" name="assign">Assign Selected</button>
        </form>
    </div>
</body>
</html>