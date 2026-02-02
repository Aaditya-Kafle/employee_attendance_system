<?php
// Start session if not already started
if(session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if(!isset($_SESSION['employee_id'])) {
    // Not logged in - redirect to login page
    header('Location: login.php');
    exit;
}

// Function to check if user is admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'Admin';
}

// Function to check if user is employee
function isEmployee() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'Employee';
}
function requireAdmin() {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
        $_SESSION['error'] = 'Access denied.';
        header('Location: index.php');
        exit;
    }
}
?>