<?php
require_once '../includes/auth_check.php';
requireAdmin();
require_once '../config/db.php';

$pageTitle = "Manage Employees";

// Fetch all employees
try {
    $stmt = $pdo->query("
        SELECT employee_id, first_name, last_name, email, phone, department, position, role 
        FROM employees 
        ORDER BY employee_id DESC
    ");
    $employees = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = "Error fetching employees: " . $e->getMessage();
}

include '../includes/header.php';
?>

<div class="card">
    <h2>Manage Employees</h2>

    <!-- Success/Error Messages -->
    <?php if(!empty($_SESSION['success'])): ?>
        <div class="success-msg"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <?php if(!empty($_SESSION['error'])): ?>
        <div class="error-msg"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <?php if(!empty($error)): ?>
        <div class="error-msg"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Employees Table -->
    <?php if(!empty($employees)): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Department</th>
                    <th>Position</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($employees as $emp): ?>
                <tr>
                    <td><?= htmlspecialchars($emp['employee_id']); ?></td>
                    <td><?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></td>
                    <td><?= htmlspecialchars($emp['email']); ?></td>
                    <td><?= htmlspecialchars($emp['phone'] ?? '-'); ?></td>
                    <td><?= htmlspecialchars($emp['department'] ?? '-'); ?></td>
                    <td><?= htmlspecialchars($emp['position'] ?? '-'); ?></td>
                    <td><span class="badge badge-info"><?= htmlspecialchars($emp['role'] ?? 'Employee'); ?></span></td>
                    <td>
                        <a href="edit_employee.php?id=<?= $emp['employee_id']; ?>" class="btn">Edit</a>
                        <a href="delete_employee.php?id=<?= $emp['employee_id']; ?>" class="btn" 
                           onclick="return confirmDelete('Are you sure you want to delete this employee?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No employees found.</p>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
