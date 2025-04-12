<?php
include('../inc/db_connection.php');
include('../inc/functions.php');

// Check if the user is logged in as an admin
if (!is_logged_in() || !is_admin()) {
    redirect('../admin/login');
}

// Check if the questionnaire ID is provided in the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('manage_questionnaires');
    exit();
}

$questionnaire_id = $_GET['id'];
$error = '';
$success = '';
$questionnaire = null;
$questions = [];

// Handle question deletion (existing code)
if (isset($_GET['delete_question']) && is_numeric($_GET['delete_question'])) {
    $delete_question_id = $_GET['delete_question'];
    // ... (existing deletion code) ...
}

try {
    // Fetch the questionnaire details (existing code)
    $stmt = $pdo->prepare("SELECT title, description FROM questionnaires WHERE questionnaire_id = :id");
    $stmt->bindParam(':id', $questionnaire_id);
    $stmt->execute();
    $questionnaire = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$questionnaire) {
        $error = 'Questionnaire not found.';
    } else {
        // Fetch the questions for this questionnaire
        $stmt = $pdo->prepare("SELECT question_id, question_text, question_type, `order` FROM questions WHERE questionnaire_id = :id ORDER BY `order` ASC");
        $stmt->bindParam(':id', $questionnaire_id);
        $stmt->execute();
        $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Questionnaire</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
    <style>
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
            padding: 0;
            color: #555;
        }
        .reorder-button:hover {
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit Questionnaire</h2>
        <p><a href="manage_questionnaires">Back to Manage Questionnaires</a> | <a href="add_question?questionnaire_id=<?php echo $questionnaire_id; ?>">Add New Question</a></p>

        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if ($questionnaire): ?>
            <h3><?php echo htmlspecialchars($questionnaire['title']); ?></h3>
            <p><?php echo htmlspecialchars($questionnaire['description']); ?></p>

            <h4>Questions</h4>
            <?php if (!empty($questions)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Question Text</th>
                            <th>Type</th>
                            <th>Actions</th>
                            <th>Reorder</th>
                        </tr>
                    </thead>
                    <tbody id="questions-table-body">
                        <?php foreach ($questions as $question): ?>
                            <tr data-question-id="<?php echo $question['question_id']; ?>">
                                <td class="order-number"><?php echo htmlspecialchars($question['order']); ?></td>
                                <td><?php echo htmlspecialchars($question['question_text']); ?></td>
                                <td><?php echo htmlspecialchars($question['question_type']); ?></td>
                                <td>
                                    <a href="edit_question?id=<?php echo $question['question_id']; ?>">Edit</a> |
                                    <a href="edit_questionnaire?id=<?php echo $questionnaire_id; ?>&delete_question=<?php echo $question['question_id']; ?>" onclick="return confirm('Are you sure you want to delete this question?')">Delete</a>
                                </td>
                                <td class="reorder-controls">
                                    <button type="button" class="reorder-button move-up" data-question-id="<?php echo $question['question_id']; ?>">&#9650;</button>
                                    <button type="button" class="reorder-button move-down" data-question-id="<?php echo $question['question_id']; ?>">&#9660;</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No questions have been added to this questionnaire yet.</p>
            <?php endif; ?>

        <?php else: ?>
            <p>Invalid questionnaire ID.</p>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const moveUpButtons = document.querySelectorAll('.move-up');
            const moveDownButtons = document.querySelectorAll('.move-down');
            const questionsTableBody = document.getElementById('questions-table-body');

            function updateOrderNumbers() {
                const rows = questionsTableBody.querySelectorAll('tr');
                rows.forEach((row, index) => {
                    row.querySelector('.order-number').textContent = index + 1;
                });
            }

            function sendReorderRequest(questionId, direction) {
                fetch('reorder_questions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `questionnaire_id=<?php echo $questionnaire_id; ?>&question_id=${questionId}&direction=${direction}`,
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Re-fetch the questions and re-render the table
                        window.location.reload(); // Simple way to update the order
                    } else if (data.error) {
                        alert('Error reordering question: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while reordering.');
                });
            }

            moveUpButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const questionId = this.dataset.questionId;
                    sendReorderRequest(questionId, 'up');
                });
            });

            moveDownButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const questionId = this.dataset.questionId;
                    sendReorderRequest(questionId, 'down');
                });
            });

            // Initial update of order numbers based on the fetched order
            // updateOrderNumbers(); // Let the database order dictate initially
        });
    </script>
</body>
</html>