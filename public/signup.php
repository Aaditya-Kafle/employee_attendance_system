<?php
session_start();

if(isset($_SESSION['employee_id'])){
    header('Location: index.php');
    exit;
}

require_once '../config/db.php';
$error = "";


if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $department = trim($_POST['department']);
    $position = trim($_POST['position']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];
    
 
    if(empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($department) || empty($position) || empty($role)){
        $error = "Please fill in all required fields.";
    } 
    elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $error = "Invalid email format.";
    }
    elseif(strlen($password) < 6){
        $error = "Password must be at least 6 characters long.";
    }
    elseif($password !== $confirm_password){
        $error = "Passwords do not match.";
    }
    elseif($role != 'Admin' && $role != 'Employee'){
        $error = "Invalid role selected.";
    }
    else {
        try {
            //  if email already exists
            $stmt = $pdo->prepare("SELECT employee_id FROM employees WHERE email = ?");
            $stmt->execute([$email]);
            
            if($stmt->fetch()){
                $error = "Email already registered. Please use a different email.";
            } else {
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
                
                $_SESSION['signup_success']= "Registration successful! You can now login.";
                header('Location: login.php');
                exit;
            }
        } catch(PDOException $e) {
            $error = "Registration error. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign Up - Employee Attendance System</title>
</head>
<body>
    <div class="signup-content">
        <h1>Employee Attendance System</h1>
        <h2>Create Account</h2>
        
        <?php if(!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if(!empty($success)): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">

            <div class="form-row">
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="first_name" required 
                           value="<?php echo isset($first_name) ? htmlspecialchars($first_name) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="last_name" required 
                           value="<?php echo isset($last_name) ? htmlspecialchars($last_name) : ''; ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required 
                           value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" 
                           value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>">
                </div>
            </div>
            
    
            <div class="form-row">
                <div class="form-group">
                    <label>Department</label>
                    <select name="department" required>
                        <option value="">Select Department</option>
                        <option value="IT" <?php echo (isset($department) && $department == 'IT') ? 'selected' : ''; ?>>IT</option>
                        <option value="HR" <?php echo (isset($department) && $department == 'HR') ? 'selected' : ''; ?>>HR</option>
                        <option value="Finance" <?php echo (isset($department) && $department == 'Finance') ? 'selected' : ''; ?>>Finance</option>
                        <option value="Marketing" <?php echo (isset($department) && $department == 'Marketing') ? 'selected' : ''; ?>>Marketing</option>
                        <option value="Sales" <?php echo (isset($department) && $department == 'Sales') ? 'selected' : ''; ?>>Sales</option>
                        <option value="Operations" <?php echo (isset($department) && $department == 'Administration') ? 'selected' : ''; ?>>Administration</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Position</label>
                    <input type="text" name="position" required 
                           value="<?php echo isset($position) ? htmlspecialchars($position) : ''; ?>">
                </div>
            </div>
            
            
            <div class="role-selection">
                <label>Select Role</label>
                <div class="role-options">
                    <div class="role-option">
                        <label>
                            <input type="radio" name="role" value="Employee" required checked>
                            <strong>Employee</strong>
                            <div class="role-description">
                                Can mark attendance, apply for leaves, view own attendance records
                            </div>
                        </label>
                    </div>
                    
                    <div class="role-option">
                        <label>
                            <input type="radio" name="role" value="Admin" required>
                            <strong>Admin</strong>
                            <div class="role-description">
                                Full access: manage employees, approve leaves, view all employee records
                            </div>
                        </label>
                    </div>
                </div>
            </div>
            
           
            <div class="form-row">
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required minlength="6">
                </div>
                
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" required minlength="6">
                </div>
            </div>
            
            <button type="submit">Create Account</button>
        </form>
        
        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>
</body>
</html>