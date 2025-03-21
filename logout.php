<?php
session_start();

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Clear user cookies
setcookie('user_id', '', time() - 3600, '/');
setcookie('user_type', '', time() - 3600, '/');

// Destroy the session
session_destroy();

// Redirect to home page
header("Location: index.php");
exit();
?> 