<?php
require_once '../includes/auth_check.php';

require_once '../config/db.php';

// Set page title
$pageTitle = 'Mark Attendance';

// Initialize variables
$error = "";
$success = "";
$date = '';
$status = '';
$check_in_time = '';
$check_out_time = '';
$notes = '';
$employee_id = '';


// For admins, they can mark attendance for any employee
// For employees, they can only mark their own attendance
$isAdmin = isAdmin();

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    if (
        empty($_POST['csrf']) ||
        !hash_equals($_SESSION['csrf'], $_POST['csrf'])
    ) {
        http_response_code(403);
        exit('Invalid CSRF token');
    }
    $employee_id = $isAdmin ? $_POST['employee_id'] : $_SESSION['employee_id'];
    $date = $_POST['date'];
    $status = $_POST['status'];
    $check_in_time = $_POST['check_in_time'];
    $check_out_time = $_POST['check_out_time'];
    $notes = trim($_POST['notes']);
    
    // Validate inputs
    if(empty($date) || empty($status)){
        $error = "Please fill in all required fields.";
    }
    elseif($isAdmin && empty($employee_id)){
        $error = "Please select an employee.";
    }
    else {
        try {
            // Check if attendance already exists for this date
            $stmt = $pdo->prepare("SELECT attendance_id FROM attendance WHERE employee_id = ? AND date = ?");
            $stmt->execute([$employee_id, $date]);
            
            if($stmt->fetch()){
                $error = "Attendance already marked for this date.";
            } else {
                // Insert attendance record
                $stmt = $pdo->prepare("
                    INSERT INTO attendance (employee_id, date, status, check_in_time, check_out_time, notes) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $employee_id,
                    $date,
                    $status,
                    !empty($check_in_time) ? $check_in_time : null,
                    !empty($check_out_time) ? $check_out_time : null,
                    $notes
                ]);
                
                $success = "Attendance marked successfully!";
                
                // Clear form
                $date = $status = $check_in_time = $check_out_time = $notes = "";
            }
        } catch(PDOException $e) {
            $error = "Error marking attendance: " . $e->getMessage();
        }
    }
}

// Fetch all employees for admin dropdown
if($isAdmin){
    try {
        $stmt = $pdo->query("SELECT employee_id, first_name, last_name FROM employees ORDER BY first_name");
        $employees = $stmt->fetchAll();
    } catch(PDOException $e) {
        $error = "Error fetching employees.";
    }
}

// Include header
include '../includes/header.php';
?>

<div class="card">
    <h2>Mark Attendance</h2>
    
    <?php if(!empty($error)): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if(!empty($success)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
        <?php if($isAdmin): ?>
        <div class="form-group">
            <label>Select Employee *</label>
            <select name="employee_id" required>
                <option value="">Choose Employee</option>
                <?php foreach($employees as $emp): ?>
                    <option value="<?php echo $emp['employee_id']; ?>">
                        <?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        
        <div class="form-group">
            <label>Date *</label>
            <input type="date" name="date" required value="<?=htmlspecialchars($date) ?>">
        </div>
        
        <div class="form-group">
            <label>Status *</label>
            <select name="status" required>
                <option value="">Select Status</option>
                <option value="Present">Present</option>
                <option value="Absent">Absent</option>
                <option value="Late">Late</option>
                <option value="Half-day">Half-day</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Check In Time</label>
            <input type="time" name="check_in_time" value="<?=htmlspecialchars($check_in_time)?>">
        </div>
        
        <div class="form-group">
            <label>Check Out Time</label>
            <input type="time" name="check_out_time" value="<?=htmlspecialchars($check_out_time)?>">
        </div>
        
        <div class="form-group">
            <label>Notes</label>
            <textarea name="notes" rows="3"><?=htmlspecialchars($notes)?></textarea>
        </div>
        
        <button type="submit" class="btn btn-success">Mark Attendance</button>
        <a href="index.php" class="btn">Back to Dashboard</a>
    </form>
</div>

<?php include '../includes/footer.php'; ?>