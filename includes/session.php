<?php
// Enhanced session security configuration
ini_set('session.use_strict_mode', 1);

// Secure session cookie parameters
session_set_cookie_params([
    'lifetime' => 0,           // Session cookie (expires when browser closes)
    'path' => '/',             // Available across entire site
    'domain' => '',            // Current domain
    'secure' => false,         // Set to true if using HTTPS
    'httponly' => true,        // Prevents JavaScript access to session cookie
    'samesite' => 'Lax'        // CSRF protection
]);

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Session timeout configuration 
$session_timeout = 600; 

// Check if session timeout is set
if (isset($_SESSION['last_activity'])) {
    // Calculate session lifetime
    $elapsed_time = time() - $_SESSION['last_activity'];
    
    // If session has expired
    if ($elapsed_time > $session_timeout) {
        // Destroy session
        session_unset();
        session_destroy();
        
        // Start new session for timeout message
        session_start();
        $_SESSION['timeout_message'] = "Your session has expired due to inactivity. Please login again.";
        header('Location: login.php');
        exit;
    }
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();

// Session hijacking prevention - regenerate session ID periodically
if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} else if (time() - $_SESSION['created'] > 600) {
    // Regenerate session ID every 10 minutes
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['employee_id']);
}

// Helper function to get current user ID
function getCurrentUserId() {
    return $_SESSION['employee_id'] ?? null;
}

// Helper function to get current user name
function getCurrentUserName() {
    if (isset($_SESSION['first_name']) && isset($_SESSION['last_name'])) {
        return $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
    }
    return null;
}

// Helper function to get current user role
function getCurrentUserRole() {
    return $_SESSION['role'] ?? null;
}

// Helper function to check if current user is admin
function isCurrentUserAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'Admin';
}

// Helper function to check if current user is employee
function isCurrentUserEmployee() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'Employee';
}
?>