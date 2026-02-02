<?php

require_once '../includes/auth_check.php';

requireAdmin();

require_once '../config/db.php';


$pageTitle = 'All Attendance Records';

// Fetch attendance records
try {
    $stmt = $pdo->query("
        SELECT a.*, e.first_name, e.last_name, e.department
        FROM attendance a
        JOIN employees e ON a.employee_id = e.employee_id
        ORDER BY a.date DESC, e.first_name
    ");
    $attendance_records = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error fetching attendance records.';
}

// Include header
include '../includes/header.php';
?>

<div class="card">
    <h2>All Attendance Records</h2>

    <?php if (isset($error)): ?>
        <div class="error-msg">
            <?= htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['success'])): ?>
        <div class="success-msg">
            <?= htmlspecialchars($_GET['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($attendance_records)): ?>
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
                <?php foreach ($attendance_records as $att): ?>
                    <?php
                        $statusClass = strtolower(str_replace(' ', '-', $att['status']));
                    ?>
                    <tr>
                        <td><?= date('M d, Y', strtotime($att['date'])); ?></td>
                        <td><?= htmlspecialchars($att['first_name'] . ' ' . $att['last_name']); ?></td>
                        <td><?= htmlspecialchars($att['department']); ?></td>
                        <td>
                            <span class="badge <?= $statusClass ?>">
                                <?= htmlspecialchars($att['status']); ?>
                            </span>
                        </td>
                        <td>
                            <?= $att['check_in_time']
                                ? date('h:i A', strtotime($att['check_in_time']))
                                : '-' ?>
                        </td>
                        <td>
                            <?= $att['check_out_time']
                                ? date('h:i A', strtotime($att['check_out_time']))
                                : '-' ?>
                        </td>
                        <td><?= htmlspecialchars($att['notes'] ?: '-'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No attendance records found.</p>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
