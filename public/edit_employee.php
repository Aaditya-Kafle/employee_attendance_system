<?php
// Check authentication
require_once '../includes/auth_check.php';

// Only admins can access this page
requireAdmin();

// Include database connection
require_once '../config/db.php';

// Set page title
$pageTitle = 'Edit Employee';

// Get employee ID from URL
$employee_id = (int) ($_GET['id'] ?? 0);


if($employee_id <= 0){
    $_SESSION['error'] = "Invalid employee ID.";
    header('Location: employees.php');
    exit;
}

// Initialize variables
$error = "";

// Fetch employee data
try {
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE employee_id = ?");
    $stmt->execute([$employee_id]);
    $employee = $stmt->fetch();
    
    if(!$employee){
        $_SESSION['error'] = "Employee not found.";
        header('Location: employees.php');
        exit;
    }
} catch(PDOException $e) {
    $_SESSION['error'] = "Error fetching employee data.";
    header('Location: employees.php');
    exit;
}

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST'){
     if (
        empty($_POST['csrf']) ||
        !hash_equals($_SESSION['csrf'], $_POST['csrf'])
    ) {
        http_response_code(403);
        exit('Invalid CSRF token');
    }
    // Get form data
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $department = trim($_POST['department']);
    $position = trim($_POST['position']);
    $role = $_POST['role'];
    $password = $_POST['password'];
    
    // Validate inputs
    if(empty($first_name) || empty($last_name) || empty($email) || empty($department) || empty($position) || empty($role)){
        $error = "Please fill in all required fields.";
    } 
    elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $error = "Invalid email format.";
    }
    elseif(!empty($password) && strlen($password) < 6){
        $error = "Password must be at least 6 characters long.";
    }
    else {
        try {
            // Check if email exists for other employees
            $stmt = $pdo->prepare("SELECT employee_id FROM employees WHERE email = ? AND employee_id != ?");
            $stmt->execute([$email, $employee_id]);
            
            if($stmt->fetch()){
                $error = "Email already exists for another employee.";
            } else {
                // Update employee
                if(!empty($password)){
                    // Update with new password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("
                        UPDATE employees 
                        SET first_name = ?, last_name = ?, email = ?, password = ?, phone = ?, department = ?, position = ?, role = ?
                        WHERE employee_id = ?
                    ");
                    $stmt->execute([$first_name, $last_name, $email, $hashed_password, $phone, $department, $position, $role, $employee_id]);
                } else {
                    // Update without changing password
                    $stmt = $pdo->prepare("
                        UPDATE employees 
                        SET first_name = ?, last_name = ?, email = ?, phone = ?, department = ?, position = ?, role = ?
                        WHERE employee_id = ?
                    ");
                    $stmt->execute([$first_name, $last_name, $email, $phone, $department, $position, $role, $employee_id]);
                }
                
                $_SESSION['success'] = "Employee updated successfully!";
                header('Location: employees.php');
                exit;
            }
        } catch(PDOException $e) {
            $error = "Error updating employee: " . $e->getMessage();
        }
    }
} else {
    // Pre-fill form with existing data
    $first_name = $employee['first_name'];
    $last_name = $employee['last_name'];
    $email = $employee['email'];
    $phone = $employee['phone'];
    $department = $employee['department'];
    $position = $employee['position'];
    $role = $employee['role'];
}

// Include header
include '../includes/header.php';
?>

<div class="card">
    <h2>Edit Employee</h2>
    
    <?php if(!empty($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
        <div class="form-group">
            <label>First Name *</label>
            <input type="text" name="first_name" required value="<?php echo htmlspecialchars($first_name); ?>">
        </div>
        
        <div class="form-group">
            <label>Last Name *</label>
            <input type="text" name="last_name" required value="<?php echo htmlspecialchars($last_name); ?>">
        </div>
        
        <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" required value="<?php echo htmlspecialchars($email); ?>">
        </div>
        
        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
        </div>
        
        <div class="form-group">
            <label>Department *</label>
            <select name="department" required>
                <option value="">Select Department</option>
                <option value="IT" <?php echo $department == 'IT' ? 'selected' : ''; ?>>IT</option>
                <option value="HR" <?php echo $department == 'HR' ? 'selected' : ''; ?>>HR</option>
                <option value="Finance" <?php echo $department == 'Finance' ? 'selected' : ''; ?>>Finance</option>
                <option value="Marketing" <?php echo $department == 'Marketing' ? 'selected' : ''; ?>>Marketing</option>
                <option value="Sales" <?php echo $department == 'Sales' ? 'selected' : ''; ?>>Sales</option>
                <option value="Operations" <?php echo $department == 'Operations' ? 'selected' : ''; ?>>Operations</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Position *</label>
            <input type="text" name="position" required value="<?php echo htmlspecialchars($position); ?>">
        </div>
        
        <div class="form-group">
            <label>Role *</label>
            <select name="role" required>
                <option value="Employee" <?php echo $role == 'Employee' ? 'selected' : ''; ?>>Employee</option>
                <option value="Admin" <?php echo $role == 'Admin' ? 'selected' : ''; ?>>Admin</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>New Password (leave blank to keep current)</label>
            <input type="password" name="password" minlength="6">
        </div>
        
        <button type="submit" class="btn">Update Employee</button>
        <a href="employees.php" class="btn btn-danger">Cancel</a>
    </form>
</div>

<?php include '../includes/footer.php'; ?>