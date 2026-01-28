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

// Function to require admin access
function requireAdmin() {
    if(!isAdmin()) {
        // Not admin - redirect to dashboard with error
        $_SESSION['error'] = 'Access denied. Admin privileges required.';
        header('Location: index.php');
        exit;
    }
}
?>