<?php
// includes/auth.php
// No session_start() here - handled by config.php

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isLabIncharge() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'lab_incharge';
}

function getLabId() {
    return isset($_SESSION['lab_id']) ? $_SESSION['lab_id'] : null;
}

function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header("Location: ../login.php");
        exit();
    }
}

function redirectIfNotAdmin() {
    if (!isAdmin()) {
        header("Location: ../dashboard.php");
        exit();
    }
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header("Location: dashboard.php");
        exit();
    }
}

function requireLabIncharge() {
    requireLogin();
    if (!isLabIncharge()) {
        header("Location: dashboard.php");
        exit();
    }
}
?>