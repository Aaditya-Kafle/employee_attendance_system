<?php
// Check authentication
require_once '../includes/auth_check.php';

// Only admins can access this page
requireAdmin();

// Include database connection
require_once '../config/db.php';

// Set page title
$pageTitle = 'All Attendance Records - Employee Attendance System';

// Fetch all attendance records with employee names
try {
    $stmt = $pdo->query("
        SELECT a.*, e.first_name, e.last_name, e.department 
        FROM attendance a
        JOIN employees e ON a.employee_id = e.employee_id
        ORDER BY a.date DESC, e.first_name
    ");
    $attendance_records = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = "Error fetching attendance: " . $e->getMessage();
}

// Include header
include '../includes/header.php';
?>

<div class="card">
    <h2>All Attendance Records</h2>
    
    <?php if(isset($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <p><a href="mark_attendance.php" class="btn btn-success">✓ Mark Attendance</a></p>
    
    <?php if(count($attendance_records) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Employee</th>
                    <th>Department</th>
                    <th>Status</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($attendance_records as $att): ?>
                <tr>
                    <td><?php echo date('M d, Y', strtotime($att['date'])); ?></td>
                    <td><?php echo htmlspecialchars($att['first_name'] . ' ' . $att['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($att['department']); ?></td>
                    <td>
                        <?php 
                        $badgeClass = 'badge-success';
                        if($att['status'] == 'Absent') $badgeClass = 'badge-danger';
                        if($att['status'] == 'Late') $badgeClass = 'badge-warning';
                        if($att['status'] == 'Half-day') $badgeClass = 'badge-info';
                        ?>
                        <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($att['status']); ?></span>
                    </td>
                    <td><?php echo $att['check_in_time'] ? date('h:i A', strtotime($att['check_in_time'])) : '-'; ?></td>
                    <td><?php echo $att['check_out_time'] ? date('h:i A', strtotime($att['check_out_time'])) : '-'; ?></td>
                    <td><?php echo htmlspecialchars($att['notes'] ?: '-'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No attendance records found.</p>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>