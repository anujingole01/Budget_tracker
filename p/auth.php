<?php
// auth.php

function require_auth() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (empty($_SESSION['user_id'])) {
        $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
        header("Location: login.php");
        exit();
    }
}

function redirect_if_logged_in() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!empty($_SESSION['user_id'])) {
        $redirect = $_SESSION['redirect_to'] ?? 'dashboard.php';
        unset($_SESSION['redirect_to']);
        header("Location: $redirect");
        exit();
    }
}
?>