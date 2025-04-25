<?php
// Only start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 86400,
        'secure'   => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "finance_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>