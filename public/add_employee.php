<?php
// Check authentication
require_once '../includes/auth_check.php';

// Only admins can access this page
requireAdmin();

// Include database connection
require_once '../config/db.php';

// Set page title
$pageTitle = 'Add Employee - Employee Attendance System';

// Initialize variables
$error = "";
$success = "";

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    // Get form data
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $department = trim($_POST['department']);
    $position = trim($_POST['position']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    // Validate inputs
    if(empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($department) || empty($position) || empty($role)){
        $error = "Please fill in all required fields.";
    } 
    elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $error = "Invalid email format.";
    }
    elseif(strlen($password) < 6){
        $error = "Password must be at least 6 characters long.";
    }
    else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT employee_id FROM employees WHERE email = ?");
            $stmt->execute([$email]);
            
            if($stmt->fetch()){
                $error = "Email already exists.";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new employee
                $stmt = $pdo->prepare("
                    INSERT INTO employees (first_name, last_name, email, password, phone, department, position, role) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $first_name,
                    $last_name,
                    $email,
                    $hashed_password,
                    $phone,
                    $department,
                    $position,
                    $role
                ]);
                
                $_SESSION['success'] = "Employee added successfully!";
                header('Location: employees.php');
                exit;
            }
        } catch(PDOException $e) {
            $error = "Error adding employee: " . $e->getMessage();
        }
    }
}

// Include header
include '../includes/header.php';
?>

<div class="card">
    <h2>Add New Employee</h2>
    
    <?php if(!empty($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="form-group">
            <label>First Name *</label>
            <input type="text" name="first_name" required value="<?php echo isset($first_name) ? htmlspecialchars($first_name) : ''; ?>">
        </div>
        
        <div class="form-group">
            <label>Last Name *</label>
            <input type="text" name="last_name" required value="<?php echo isset($last_name) ? htmlspecialchars($last_name) : ''; ?>">
        </div>
        
        <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
        </div>
        
        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>">
        </div>
        
        <div class="form-group">
            <label>Department *</label>
            <select name="department" required>
                <option value="">Select Department</option>
                <option value="IT">IT</option>
                <option value="HR">HR</option>
                <option value="Finance">Finance</option>
                <option value="Marketing">Marketing</option>
                <option value="Sales">Sales</option>
                <option value="Operations">Operations</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Position *</label>
            <input type="text" name="position" required value="<?php echo isset($position) ? htmlspecialchars($position) : ''; ?>">
        </div>
        
        <div class="form-group">
            <label>Role *</label>
            <select name="role" required>
                <option value="">Select Role</option>
                <option value="Employee">Employee</option>
                <option value="Admin">Admin</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Password *</label>
            <input type="password" name="password" required minlength="6">
        </div>
        
        <button type="submit" class="btn">Add Employee</button>
        <a href="employees.php" class="btn btn-danger">Cancel</a>
    </form>
</div>

<?php include '../includes/footer.php'; ?>