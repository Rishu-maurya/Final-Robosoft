<?php
require_once 'vendor/autoload.php'; // Composer autoload file

$client = new Google_Client();
$client->setClientId(' ');
$client->setClientSecret(' ');
$client->setRedirectUri('http://localhost/robosoft/google-callback.php');
$client->addScope("email");
$client->addScope("profile");

$authUrl = $client->createAuthUrl();
header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
exit();

?>