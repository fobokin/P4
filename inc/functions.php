<?php
session_start();

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_student() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'student';
}

function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function display_error($message) {
    echo '<div class="error">' . htmlspecialchars($message) . '</div>';
}

function display_success($message) {
    echo '<div class="success">' . htmlspecialchars($message) . '</div>';
}

// Add more utility functions here as needed
?>