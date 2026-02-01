<?php
// Check authentication
require_once '../includes/auth_check.php';

// Include database connection
require_once '../config/db.php';

// Set page title
$pageTitle = 'Apply for Leave - Employee Attendance System';

// Initialize variables
$error = "";
$success = "";

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $employee_id = $_SESSION['employee_id'];
    $leave_type = $_POST['leave_type'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $reason = trim($_POST['reason']);
    
    // Validate inputs
    if(empty($leave_type) || empty($start_date) || empty($end_date) || empty($reason)){
        $error = "Please fill in all required fields.";
    }else {
        try {
            // Calculate total days
            $start = new DateTime($start_date);
            $end = new DateTime($end_date);
            $end = $end->modify('+1 day'); 
            $interval = $start->diff($end);
            $total_days = $interval->days;
            
            // Insert leave request
            $stmt = $pdo->prepare("
                INSERT INTO leave_requests (employee_id, leave_type, start_date, end_date, total_days, reason, request_date, status) 
                VALUES (?, ?, ?, ?, ?, ?, CURDATE(), 'Pending')
            ");
            
            $stmt->execute([
                $employee_id,
                $leave_type,
                $start_date,
                $end_date,
                $total_days,
                $reason
            ]);
            
            $success = "Leave request submitted successfully!";
            
            // Clear form
            $leave_type = $start_date = $end_date = $reason = "";
        } catch(PDOException $e) {
            $error = "Error submitting leave request: " . $e->getMessage();
        }
    }
}

include '../includes/header.php';
?>

<div class="card">
    <h2>Apply for Leave</h2>
    
    <?php if(!empty($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if(!empty($success)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="form-group">
            <label>Leave Type *</label>
            <select name="leave_type" required>
                <option value="">Select Leave Type</option>
                <option value="Sick Leave">Sick Leave</option>
                <option value="Casual Leave">Casual Leave</option>
                <option value="Annual Leave">Annual Leave</option>
                <option value="Unpaid Leave">Unpaid Leave</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Start Date *</label>
            <input type="date" name="start_date" required value="<?=htmlspecialchars($start_date)?>">
        </div>
        
        <div class="form-group">
            <label>End Date *</label>
            <input type="date" name="end_date" required value="<?=htmlspecialchars($end_date)?>">
        </div>
        
        <div class="form-group">
            <label>Reason *</label>
            <textarea name="reason" rows="4" required><?=htmlspecialchars($reason)?></textarea>
        </div>
        
        <button type="submit" class="btn btn-warning">Submit Leave Request</button>
        <a href="index.php" class="btn">Back to Dashboard</a>
    </form>
</div>

<?php include '../includes/footer.php'; ?>