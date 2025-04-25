<?php
// auth-check.php
function require_auth() {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php?error=unauthorized");
        exit();
    }
}

function redirect_if_logged_in() {
    session_start();
    if (isset($_SESSION['user_id'])) {
        header("Location: dashboard.php");
        exit();
    }
}
?>