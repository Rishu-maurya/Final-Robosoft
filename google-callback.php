<?php

session_start();

file_put_contents('log.txt', "Callback Hit\n", FILE_APPEND);


// Show all errors during development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Required libraries
require_once 'vendor/autoload.php';
session_start();
require 'db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Google\Service\Oauth2;

// --- Google OAuth Setup ---
$client = new Google_Client();
$client->setClientId('');
$client->setClientSecret('');
$client->setRedirectUri('http://localhost/robosoft/google-callback.php');
$client->addScope("email");
$client->addScope("profile");

// --- Helper Functions ---
function generateRandomUsername($name) {
    $cleanName = preg_replace('/\s+/', '', strtolower($name));
    return $cleanName . rand(100, 999);
}

function generateRandomPassword($length = 8) {
    return bin2hex(random_bytes($length / 2)); // Generates a password with $length characters
}

// --- OAuth Code Handling ---
if (!isset($_GET['code'])) {
    die("Authorization code missing.");
}

$token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

if (isset($token['error'])) {
    die("Google OAuth Error: " . htmlspecialchars($token['error']) . " - " . htmlspecialchars($token['error_description']));
}

$client->setAccessToken($token['access_token']);
$oauth = new Oauth2($client);
$user_info = $oauth->userinfo->get();

$gmail = $conn->real_escape_string($user_info->email);
$name = $conn->real_escape_string($user_info->name);
$phone = NULL;
$username = generateRandomUsername($name);
$password_plain = generateRandomPassword(10);
$password_hash = password_hash($password_plain, PASSWORD_DEFAULT);

// --- Check if user already exists ---
$stmt = $conn->prepare("SELECT * FROM users WHERE gmail = ?");
$stmt->bind_param("s", $gmail);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $_SESSION['user_gmail'] = $gmail;
    $_SESSION['user_name'] = $name;
    header("Location: dashboard.php");
    exit();
} else {
    // --- Insert New User ---
    $stmt = $conn->prepare("INSERT INTO users (username, gmail, password, phone, name) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $gmail, $password_hash, $phone, $name);

    if ($stmt->execute()) {
        // --- Send Welcome Email ---
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'krishab394@gmail.com';
            $mail->Password   = 'cqnh ujqi pczu quge'; // 🔐 Use App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('krishab394@gmail.com', 'Rishab');
            $mail->addAddress($gmail, $name);
            $mail->isHTML(false);
            $mail->Subject = 'Your Account Details';
            $mail->Body    = "Hello $name,\n\nYour account has been created.\nUsername: $username\nPassword: $password_plain\n\nYou can change your password after login.\n\nThanks!";

            $mail->send();
        } catch (Exception $e) {
            error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }

        $_SESSION['user_gmail'] = $gmail;
        $_SESSION['user_name'] = $name;
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Database Error: " . $conn->error;
    }
}
?>
