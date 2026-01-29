<?php
// Check authentication
require_once '../includes/auth_check.php';

// Only admins can access this page
requireAdmin();

// Include database connection
require_once '../config/db.php';

// Set page title
$pageTitle = "Manage Employees";

// Include header
include '../includes/header.php';

// Fetch all employees
try {
    $stmt = $pdo->query("SELECT employee_id, first_name, last_name, email, phone, department, position, role FROM employees ORDER BY employee_id DESC");
    $employees = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = "Error fetching employees: " . $e->getMessage();
}
?>

<div class="card">
    <h2>Manage Employees</h2>
    
    <?php if(isset($_SESSION['success'])): ?>
        <div><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    
    <?php if(isset($_SESSION['error'])): ?>
        <div class><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    
    <?php if(isset($error)): ?>
        <div><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <p><a href="add_employee.php" class="btn">Add New Employee</a></p>
    
    <?php if(count($employees) > 0): ?>
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
                    <td><?php echo htmlspecialchars($emp['employee_id']); ?></td>
                    <td><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($emp['email']); ?></td>
                    <td><?php echo htmlspecialchars($emp['phone']); ?></td>
                    <td><?php echo htmlspecialchars($emp['department']); ?></td>
                    <td><?php echo htmlspecialchars($emp['position']); ?></td>
                    <td><span class="badge badge-info"><?php echo htmlspecialchars($emp['role']); ?></span></td>
                    <td>
                        <a href="edit_employee.php?id=<?php echo $emp['employee_id']; ?>" class="btn btn-small">Edit</a>
                        <a href="delete_employee.php?id=<?php echo $emp['employee_id']; ?>" class="btn btn-small btn-danger" onclick="return confirmDelete('Are you sure you want to delete this employee?')">Delete</a>
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