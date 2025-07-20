<?php
$config = require(__DIR__ . '/../config/config.php');

$host = $config['DB_HOST'];
$db   = $config['DB_NAME'];
$user = $config['DB_USER'];
$pass = $config['DB_PASS'];

$conn = new mysqli($host, $user, $pass, $db);

// Set charset (optional but good practice)
$conn->set_charset('utf8mb4');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
