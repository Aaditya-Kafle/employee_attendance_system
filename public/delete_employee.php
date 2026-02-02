<?php
require_once '../includes/auth_check.php';

requireAdmin();

require_once '../config/db.php';

$employee_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($employee_id <= 0){
    $_SESSION['error'] = "Invalid employee ID.";
    header('Location: employees.php');
    exit;
}


try {
    // Delete employee 
    $stmt = $pdo->prepare("DELETE FROM employees WHERE employee_id = ?");
    $stmt->execute([$employee_id]);
    
    if($stmt->rowCount() > 0){
        $_SESSION['success'] = "Employee deleted successfully!";
    } else {
        $_SESSION['error'] = "Employee not found.";
    }
} catch(PDOException $e) {
    $_SESSION['error'] = "Error deleting employee: " . $e->getMessage();
}

header('Location: employees.php');
exit;
?>