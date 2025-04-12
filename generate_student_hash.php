<?php
$password = 'student_password'; // **Replace this with the actual password you want for the student**
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

echo "Generated Hashed Password:\n";
echo $hashedPassword . "\n";
echo "\n";
echo "You can now copy this hashed password and use it in your SQL INSERT statement.";
?>