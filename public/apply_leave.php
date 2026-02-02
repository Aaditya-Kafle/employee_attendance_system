<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';
$pageTitle = 'Apply for Leave';
$error = "";
$success = "";

$leave_type = $_POST['leave_type'] ?? '';
$start_date = $_POST['start_date'] ?? '';
$end_date = $_POST['end_date'] ?? '';
$reason = trim($_POST['reason'] ?? '');

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    if (empty($_POST['csrf']) || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'])) {
        http_response_code(403);
        exit('Invalid CSRF token');
    }

    $employee_id = $_SESSION['employee_id'];

    // Validate inputs
    if(empty($leave_type) || empty($start_date) || empty($end_date) || empty($reason)){
        $error = "Please fill in all required fields.";
    } else {
        try {
            // Calculate total days
            $start = new DateTime($start_date);
            $end = new DateTime($end_date);
            $end = $end->modify('+1 day'); 
            $interval = $start->diff($end);
            $total_days = $interval->days;

            // Insert leave request
            $stmt = $pdo->prepare("
                INSERT INTO leave_requests 
                (employee_id, leave_type, start_date, end_date, total_days, reason, request_date, status) 
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
        <div class="error-msg"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if(!empty($success)): ?>
        <div class="success-msg"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?? '' ?>">

        <div class="form-group">
            <label>Leave Type *</label>
            <select name="leave_type" required>
                <option value="">Select Leave Type</option>
                <?php
                $leaveTypes = ['Sick Leave', 'Casual Leave', 'Annual Leave', 'Unpaid Leave'];
                foreach($leaveTypes as $type){
                    $selected = ($leave_type === $type) ? 'selected' : '';
                    echo "<option value=\"$type\" $selected>$type</option>";
                }
                ?>
            </select>

            <label>Start Date *</label>
            <input type="date" name="start_date" required value="<?= htmlspecialchars($start_date) ?>">

            <label>End Date *</label>
            <input type="date" name="end_date" required value="<?= htmlspecialchars($end_date) ?>">

            <label>Reason *</label>
            <textarea name="reason" rows="4" required><?= htmlspecialchars($reason) ?></textarea>

            <button type="submit" class="btn">Submit Leave Request</button>
            <a href="index.php" class="btn">Back to Dashboard</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
