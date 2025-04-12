<?php
include('../inc/functions.php');

// Destroy the session
session_start();
session_unset();
session_destroy();

// Redirect to the student login page
redirect('login');
?>