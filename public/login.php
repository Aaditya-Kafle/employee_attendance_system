<?php
session_start();

// If logged in redirect to dashboard
if(isset($_SESSION['employee_id'])){
    header('Location: index.php');
    exit;
}

require_once '../config/db.php';
$error = "";
$success = "";

// Check for signup success message
if(isset($_SESSION['signup_success'])){
    $success = $_SESSION['signup_success'];
    unset($_SESSION['signup_success']);
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if(empty($email) || empty($password)){
        $error = "Please enter both email and password";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT employee_id, first_name, last_name, email, password, role FROM employees WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            // Check if user exists
            if($user) {
                // Verify password
                if(password_verify($password, $user['password'])){
                    $_SESSION['employee_id'] = $user['employee_id'];
                    $_SESSION['first_name'] = $user['first_name'];
                    $_SESSION['last_name'] = $user['last_name'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    
                    header('Location: index.php');
                    exit;
                } else {
                    $error = "Invalid email or password";
                }
            } else {
                $error = "Invalid email or password";
            }
        } catch(PDOException $e) {
            $error = "Login error. Please try again";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Employee Attendance System</title>
</head>
<body>
    <div class="login-content">
        <h1>Employee Attendance System</h1>
        <h2>Login</h2>
        
        <?php if(!empty($success)): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if(!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="form-content">
            <form method="POST" action="">
                <label>Email:</label>
                <input type="email" name="email" required>
                
                <label>Password:</label>
                <input type="password" name="password" required>
                
                <button type="submit">Login</button>
            </form>
        </div>
        
        <div class="login-link">
            Don't have an account? <a href="signup.php">Sign up here</a>
        </div>
    </div>
</body>
</html>