<?php

require_once '../includes/auth_check.php';
require_once '../config/db.php';
requireAdmin();

$pageTitle = 'Edit Employee';
$employee_id = (int) ($_GET['id'] ?? 0);

if ($employee_id <= 0) {
    $_SESSION['error'] = "Invalid employee ID.";
    header('Location: employees.php');
    exit;
}

$error = "";

// Fetch employee data
try {
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE employee_id = ?");
    $stmt->execute([$employee_id]);
    $employee = $stmt->fetch();

    if (!$employee) {
        $_SESSION['error'] = "Employee not found.";
        header('Location: employees.php');
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Error fetching employee data.";
    header('Location: employees.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (empty($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'])) {
        http_response_code(403);
        exit('Invalid CSRF token');
    }

    // Use ?? '' instead of isset
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $role = $_POST['role'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validate inputs
    if (empty($first_name) || empty($last_name) || empty($email) || empty($department) || empty($position) || empty($role)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (!empty($password) && strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        try {
            // Check if email exists for other employees
            $stmt = $pdo->prepare("SELECT employee_id FROM employees WHERE email = ? AND employee_id != ?");
            $stmt->execute([$email, $employee_id]);

            if ($stmt->fetch()) {
                $error = "Email already exists for another employee.";
            } else {
                // Update employee
                if (!empty($password)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("
                        UPDATE employees 
                        SET first_name = ?, last_name = ?, email = ?, password = ?, phone = ?, department = ?, position = ?, role = ?
                        WHERE employee_id = ?
                    ");
                    $stmt->execute([$first_name, $last_name, $email, $hashed_password, $phone, $department, $position, $role, $employee_id]);
                } else {
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
        } catch (PDOException $e) {
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

include '../includes/header.php';
?>

<div class="card">
    <h2>Edit Employee</h2>

    <?php if(!empty($error)): ?>
        <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?? '' ?>">
        
        <div class="form-group">
            <label>First Name *</label>
            <input type="text" name="first_name" required value="<?= htmlspecialchars($first_name) ?>">

            <label>Last Name *</label>
            <input type="text" name="last_name" required value="<?= htmlspecialchars($last_name) ?>">

            <label>Email *</label>
            <input type="email" name="email" required value="<?= htmlspecialchars($email) ?>">

            <label>Phone</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($phone) ?>">

            <label>Department *</label>
            <select name="department" required>
                <option value="">Select Department</option>
                <?php
                $departments = ['IT', 'HR', 'Finance', 'Marketing', 'Sales', 'Operations'];
                foreach ($departments as $dep) {
                    $selected = ($department == $dep) ? 'selected' : '';
                    echo "<option value=\"$dep\" $selected>$dep</option>";
                }
                ?>
            </select>

            <label>Position *</label>
            <input type="text" name="position" required value="<?= htmlspecialchars($position) ?>">

            <label>Role *</label>
            <select name="role" required>
                <option value="Employee" <?= $role == 'Employee' ? 'selected' : '' ?>>Employee</option>
                <option value="Admin" <?= $role == 'Admin' ? 'selected' : '' ?>>Admin</option>
            </select>

            <label>New Password</label>
            <input type="password" name="password" minlength="6">

            <button type="submit" class="btn">Update Employee</button>
            <a href="employees.php" class="btn">Cancel</a>
             <a href="index.php" class="btn">Back to Dashboard</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
