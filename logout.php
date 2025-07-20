<?php
require __DIR__ . '/includes/session.php';

// printing errors for debugging
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// ini_set('log_errors', 1);
// ini_set('error_log', __DIR__ . '/php-error.log');

// Unset all session variables
$_SESSION = [];

// If session uses cookies, delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Destroy old session
session_destroy();
session_write_close();

// Start fresh session and regenerate session ID
session_start();
session_regenerate_id(true);

//set logout success message
$_SESSION['success_msg'] = "You have logged out successfully.";

// Redirect to login page
header("Location: /login.php");
exit;
