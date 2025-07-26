<?php
session_start();

// Redirect if user is not logged in via normal login or Google login
if (!isset($_SESSION['user_id']) && !isset($_SESSION['gmail'])) {
    header("Location: index.html"); // or login.php
    exit();
}

// Identify user type
$name = "";
$email = "";

if (isset($_SESSION['user_id'])) {
    // Normal login
    $name = $_SESSION['username'];
    $email = $_SESSION['email'] ?? "Not Available";
} elseif (isset($_SESSION['gmail'])) {
    // Google login
    $name = $_SESSION['name'] ?? "Google User";
    $email = $_SESSION['gmail'];
}
?>