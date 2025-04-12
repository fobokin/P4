<?php
$password = 'admin_password'; // **Replace with the desired admin password**
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

echo "Generated Hashed Admin Password:\n";
echo $hashedPassword . "\n";
echo "\n";
echo "Copy this hash for your SQL INSERT statement for the administrators table.";
?>