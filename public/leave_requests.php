<?php
require_once '../includes/auth_check.php';
requireAdmin();
require_once '../config/db.php';

$pageTitle = 'Manage Leave Requests';

// Handle approve/reject actions safely
$leave_id = (int)($_GET['id'] ?? 0);
$action   = $_GET['action'] ?? '';

if ($leave_id > 0 && in_array($action, ['approve', 'reject'])) {
    $status      = ($action === 'approve') ? 'Approved' : 'Rejected';
    $approved_by = ($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? '');

    try {
        $stmt = $pdo->prepare("
            UPDATE leave_requests
            SET status = ?, approved_by = ?, approved_date = CURDATE()
            WHERE leave_id = ?
        ");
        $stmt->execute([$status, $approved_by, $leave_id]);

        $_SESSION['success'] = "Leave request {$status} successfully.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Failed to update leave request.";
    }

    header('Location: leave_requests.php');
    exit;
}

// Fetch leave requests
try {
    $stmt = $pdo->query("
        SELECT lr.*, e.first_name, e.last_name, e.department
        FROM leave_requests lr
        JOIN employees e ON lr.employee_id = e.employee_id
        ORDER BY
            CASE lr.status
                WHEN 'Pending' THEN 1
                WHEN 'Approved' THEN 2
                WHEN 'Rejected' THEN 3
            END,
            lr.request_date DESC
    ");
    $leave_requests = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Unable to load leave requests.";
}

include '../includes/header.php';
?>

<div class="card">
    <h2>Manage Leave Requests</h2>

    <!-- Messages -->
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="success-msg"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="error-msg"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="error-msg"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Table -->
    <?php if (!empty($leave_requests)): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Employee</th>
                    <th>Department</th>
                    <th>Type</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Days</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
            <?php foreach ($leave_requests as $lr): ?>
                <tr>
                    <td><?= $lr['leave_id']; ?></td>
                    <td><?= htmlspecialchars($lr['first_name'].' '.$lr['last_name']); ?></td>
                    <td><?= htmlspecialchars($lr['department']); ?></td>
                    <td><?= htmlspecialchars($lr['leave_type']); ?></td>
                    <td><?= date('M d, Y', strtotime($lr['start_date'])); ?></td>
                    <td><?= date('M d, Y', strtotime($lr['end_date'])); ?></td>
                    <td><?= (int)$lr['total_days']; ?></td>
                    <td><?= htmlspecialchars($lr['reason']); ?></td>

                    <td>
                        <?php
                        $statusClass = match($lr['status'] ?? '') {
                            'Approved' => 'badge-success',
                            'Rejected' => 'badge-danger',
                            default => 'badge-pending',
                        };
                        ?>
                        <span class="badge"><?= htmlspecialchars($lr['status'] ?? 'Pending'); ?></span>
                    </td>

                    <td>
                        <?php if (($lr['status'] ?? '') === 'Pending'): ?>
                            <a class="btn" href="?action=approve&id=<?= $lr['leave_id']; ?>">Approve</a>
                            <a class="btn" href="?action=reject&id=<?= $lr['leave_id']; ?>">Reject</a>
                        <?php else: ?>
                            <span class="text-muted"><?= htmlspecialchars($lr['approved_by'] ?? '-'); ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No leave requests found.</p>
    <?php endif; ?>
</div>
 <a href="index.php" class="btn">Back to Dashboard</a>

<?php include '../includes/footer.php'; ?>
