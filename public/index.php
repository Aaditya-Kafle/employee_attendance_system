<?php
require_once '../includes/auth_check.php';

require_once '../config/db.php';

$pageTitle = 'Dashboard';

include '../includes/header.php';

$isAdmin = isAdmin();
$currentEmployeeId = $_SESSION['employee_id'];

// Fetch data from database based on role
try {
    if ($isAdmin) {
        // Admin stats
        $stmt = $pdo->query("SELECT COUNT(*) AS total FROM attendance");
        $totalAttendance = $stmt->fetch()['total'];

        $stmt = $pdo->query("SELECT COUNT(*) AS total FROM leave_requests WHERE status = 'Pending'");
        $pendingLeaves = $stmt->fetch()['total'];
    } else {
        // Employee stats
        $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM attendance WHERE employee_id = ?");
        $stmt->execute([$currentEmployeeId]);
        $myAttendance = $stmt->fetch()['total'];

        $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM leave_requests WHERE employee_id = ?");
        $stmt->execute([$currentEmployeeId]);
        $myLeaves = $stmt->fetch()['total'];

        $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM leave_requests WHERE employee_id = ? AND status = 'Pending'");
        $stmt->execute([$currentEmployeeId]);
        $myPendingLeaves = $stmt->fetch()['total'];
    }
} catch (PDOException $e) {
    $error = "Error fetching statistics: " . $e->getMessage();
}
?>

<div class="card">
    <h2>Dashboard</h2>

    <?php if (isset($error)): ?>
        <div class="error-msg"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($isAdmin): ?>
        <h3>Admin Overview</h3>
        <p><strong>Total Attendance Records:</strong> <?= $totalAttendance ?? 0; ?></p>
        <p><strong>Pending Leave Requests:</strong> <?= $pendingLeaves ?? 0; ?></p>
    <?php else: ?>
        <h3>My Overview</h3>
        <p><strong>My Attendance Records:</strong> <?= $myAttendance; ?></p>
        <p><strong>My Leave Requests:</strong> <?= $myLeaves; ?></p>
        <p><strong>My Pending Leaves:</strong> <?= $myPendingLeaves; ?></p>
    <?php endif; ?>
</div>

<div class="card">
    <h2>Actions</h2>

    <?php if ($isAdmin): ?>
        <a href="employees.php" class="btn">Manage Employees</a>
        <a href="add_employee.php" class="btn">Add New Employee</a>
        <a href="attendance.php" class="btn">View All Attendance</a>
        <a href="mark_attendance.php" class="btn">Mark Attendance</a>
        <a href="leave_requests.php" class="btn">Manage Leave Requests</a>
        <a href="search.php" class="btn">Search Records</a>
    <?php else: ?>
        <a href="mark_attendance.php" class="btn">Mark My Attendance</a>
        <a href="apply_leave.php" class="btn">Apply for Leave</a>
    <?php endif; ?>
</div>

<?php if ($isAdmin): ?>
    <!-- Admin: Pending Leave Requests -->
    <div class="card">
        <h2>Pending Leave Requests</h2>

        <?php
        try {
            $stmt = $pdo->query("
                SELECT lr.leave_type, lr.start_date, lr.end_date, lr.total_days,
                       e.first_name, e.last_name
                FROM leave_requests lr
                JOIN employees e ON lr.employee_id = e.employee_id
                WHERE lr.status = 'Pending'
                ORDER BY lr.request_date DESC
                LIMIT 5
            ");
            $pendingRequests = $stmt->fetchAll();

            if ($pendingRequests):
        ?>
            <table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Type</th>
                        <th>Dates</th>
                        <th>Days</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingRequests as $req): ?>
                        <tr>
                            <td><?= htmlspecialchars($req['first_name'] . ' ' . $req['last_name']); ?></td>
                            <td>
                                <span class="badge pending"><?= htmlspecialchars($req['leave_type']); ?></span>
                            </td>
                            <td><?= date('M d', strtotime($req['start_date'])); ?> - <?= date('M d', strtotime($req['end_date'])); ?></td>
                            <td><?= htmlspecialchars($req['total_days']); ?> days</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <p class="text-center">
                <a href="leave_requests.php" class="btn">Manage All Requests</a>
            </p>

        <?php else: ?>
            <p>No pending leave requests.</p>
        <?php
            endif;
        } catch (PDOException $e) {
            echo '<div class="error-msg">' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        ?>
    </div>

<?php else: ?>
    <!-- Employee: Recent Attendance -->
    <div class="card">
        <h2>My Recent Attendance</h2>

        <?php
        try {
            $stmt = $pdo->prepare("
                SELECT date, status, check_in_time, check_out_time
                FROM attendance
                WHERE employee_id = ?
                ORDER BY date DESC
                LIMIT 5
            ");
            $stmt->execute([$currentEmployeeId]);
            $records = $stmt->fetchAll();

            if ($records):
        ?>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $att): ?>
                        <?php $statusClass = strtolower(str_replace(' ', '-', $att['status'])); ?>
                        <tr>
                            <td><?= date('M d, Y', strtotime($att['date'])); ?></td>
                            <td>
                                <span class="badge <?= $statusClass ?>"><?= htmlspecialchars($att['status']); ?></span>
                            </td>
                            <td><?= $att['check_in_time'] ? date('h:i A', strtotime($att['check_in_time'])) : '-'; ?></td>
                            <td><?= $att['check_out_time'] ? date('h:i A', strtotime($att['check_out_time'])) : '-'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php else: ?>
            <p>No attendance records found.</p>
        <?php
            endif;
        } catch (PDOException $e) {
            echo '<div class="error-msg">' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        ?>
    </div>

    <!-- Employee: Leave Requests -->
    <div class="card">
        <h2>My Leave Requests</h2>

        <?php
        try {
            $stmt = $pdo->prepare("
                SELECT leave_type, start_date, end_date, total_days, status
                FROM leave_requests
                WHERE employee_id = ?
                ORDER BY request_date DESC
                LIMIT 5
            ");
            $stmt->execute([$currentEmployeeId]);
            $leaves = $stmt->fetchAll();

            if ($leaves):
        ?>
            <table>
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Dates</th>
                        <th>Days</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($leaves as $leave): ?>
                        <?php $statusClass = strtolower(str_replace(' ', '-', $leave['status'])); ?>
                        <tr>
                            <td><?= htmlspecialchars($leave['leave_type']); ?></td>
                            <td><?= date('M d', strtotime($leave['start_date'])); ?> - <?= date('M d', strtotime($leave['end_date'])); ?></td>
                            <td><?= htmlspecialchars($leave['total_days']); ?> days</td>
                            <td>
                                <span class="badge <?= $statusClass ?>"><?= htmlspecialchars($leave['status']); ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php else: ?>
            <p>No leave requests found.</p>
        <?php
            endif;
        } catch (PDOException $e) {
            echo '<div class="error-msg">' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        ?>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>