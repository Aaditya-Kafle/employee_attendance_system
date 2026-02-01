<?php
// Check authentication
require_once '../includes/auth_check.php';

// Only admins can access this page
requireAdmin();

// Include database connection
require_once '../config/db.php';

// Set page title
$pageTitle = 'Manage Leave Requests';

// Handle approve/reject actions
if (!empty($_GET['action']) && !empty($_GET['id'])) {
    $leave_id = (int)$_GET['id'];
    $action = $_GET['action'];
    if (in_array($action, ['approve', 'reject'])) {
        $status = $action === 'approve' ? 'Approved' : 'Rejected';
        $approved_by = $_SESSION['first_name'] . ' ' . $_SESSION['last_name'];
        
        try {
            $stmt = $pdo->prepare("
                UPDATE leave_requests 
                SET status = ?, approved_by = ?, approved_date = CURDATE() 
                WHERE leave_id = ?
            ");
            $stmt->execute([$status, $approved_by, $leave_id]);
            
            $_SESSION['success'] = "Leave request " . strtolower($status) . " successfully!";
        } catch(PDOException $e) {
            $_SESSION['error'] = "Error updating leave request: " . $e->getMessage();
        }
        
        header('Location: leave_requests.php');
        exit;
    }
}

// Fetch all leave requests with employee details
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
} catch(PDOException $e) {
    $error = "Error fetching leave requests: " . $e->getMessage();
}

// Include header
include '../includes/header.php';
?>

<div class="card">
    <h2>Manage Leave Requests</h2>
    
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    
    <?php if(isset($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if(isset($leave_requests) && count($leave_requests) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Employee</th>
                    <th>Department</th>
                    <th>Leave Type</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Days</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($leave_requests as $lr): ?>
                <tr>
                    <td><?php echo htmlspecialchars($lr['leave_id']); ?></td>
                    <td><?php echo htmlspecialchars($lr['first_name'] . ' ' . $lr['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($lr['department']); ?></td>
                    <td><?php echo htmlspecialchars($lr['leave_type']); ?></td>
                    <td><?php echo date('M d, Y', strtotime($lr['start_date'])); ?></td>
                    <td><?php echo date('M d, Y', strtotime($lr['end_date'])); ?></td>
                    <td><?php echo htmlspecialchars($lr['total_days']); ?> days</td>
                    <td><?php echo htmlspecialchars($lr['reason']); ?></td>
                    <td>
                        <?php 
                        $badgeClass = 'badge-warning';
                        if($lr['status'] == 'Approved') $badgeClass = 'badge-success';
                        if($lr['status'] == 'Rejected') $badgeClass = 'badge-danger';
                        ?>
                        <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($lr['status']); ?></span>
                    </td>
                    <td>
                        <?php if($lr['status'] == 'Pending'): ?>
                            <a href="?action=approve&id=<?php echo $lr['leave_id']; ?>" class="btn btn-small btn-success">Approve</a>
                            <a href="?action=reject&id=<?php echo $lr['leave_id']; ?>" class="btn btn-small btn-danger">Reject</a>
                        <?php else: ?>
                            <span style="color: #999; font-size: 0.85rem;">
                                <?php echo htmlspecialchars($lr['status']); ?> 
                                <?php if($lr['approved_by']): ?>
                                    by <?php echo htmlspecialchars($lr['approved_by']); ?>
                                <?php endif; ?>
                            </span>
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

<?php include '../includes/footer.php'; ?>