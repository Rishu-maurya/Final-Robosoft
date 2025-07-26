<?php

require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Form fields
    $username = $_POST['username'];
    $gmail = $_POST['gmail'];
    $password = $_POST['password'];
    $phone = $_POST['phone'];
    $name = $_POST['name'];

    // Sanitize input (optional)
    $username = mysqli_real_escape_string($conn, $username);
    $gmail = mysqli_real_escape_string($conn, $gmail);
    $phone = mysqli_real_escape_string($conn, $phone);
    $name = mysqli_real_escape_string($conn, $name);

    // Hash password for security
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert query
    $sql = "INSERT INTO users (username, gmail, password, phone, name) VALUES ('$username', '$gmail', '$hashedPassword', '$phone', '$name')";

    if ($conn->query($sql) === TRUE) {
    // Redirect to login page after successful signup
    header("Location: index.html");
    exit(); // Always call exit after header redirect
} else {
    echo "Error: " . $conn->error;
}

}
?>
