<?php
// Check authentication
require_once '../includes/auth_check.php';

// Include database connection
require_once '../config/db.php';

// Set page title for header
$pageTitle = 'Dashboard - Employee Attendance System';

// Include header
include '../includes/header.php';

// Check if user is admin or employee
$isAdmin = isAdmin();
$currentEmployeeId = $_SESSION['employee_id'];

// Fetch  from database based on role
try {
    if($isAdmin) {
        // Admin sees all statistics
        
        // Get total attendance records
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM attendance");
        $totalAttendance = $stmt->fetch()['total'];
        
        // Get pending leave requests count
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM leave_requests WHERE status = 'Pending'");
        $pendingLeaves = $stmt->fetch()['total'];
        
        // Get total employees
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM employees WHERE status = 'Active'");
        $totalEmployees = $stmt->fetch()['total'];
        
    } else {
        // Employee sees only their own statistics
        
        // Get employee's attendance count
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM attendance WHERE employee_id = ?");
        $stmt->execute([$currentEmployeeId]);
        $myAttendance = $stmt->fetch()['total'];
        
        // Get employee's leave requests count
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM leave_requests WHERE employee_id = ?");
        $stmt->execute([$currentEmployeeId]);
        $myLeaves = $stmt->fetch()['total'];
        
        // Get employee's pending leave requests
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM leave_requests WHERE employee_id = ? AND status = 'Pending'");
        $stmt->execute([$currentEmployeeId]);
        $myPendingLeaves = $stmt->fetch()['total'];
    }
    
} catch(PDOException $e) {
    // Store error message if query fails
    $error = "Error fetching statistics: " . $e->getMessage();
}
?>

<div class="card">
    <h2>📊 Dashboard</h2>
    
    <?php if(isset($error)): ?>
        <!-- Display error message if there was a database error -->
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if($isAdmin): ?>
        <!-- Admin Dashboard - Show all statistics -->
        <h3>Admin Overview</h3>
        <div>
            <p><strong>Total Employees:</strong> <?php echo $totalEmployees; ?></p>
            <p><strong>Total Attendance Records:</strong> <?php echo $totalAttendance; ?></p>
            <p><strong>Pending Leave Requests:</strong> <?php echo $pendingLeaves; ?></p>
        </div>
    <?php else: ?>
        <!-- < Employee Dashboard - Show only their statistics --> 
        <h3>My Overview</h3>
        <div>
            <p><strong>My Attendance Records:</strong> <?php echo $myAttendance; ?></p>
            <p><strong>My Leave Requests:</strong> <?php echo $myLeaves; ?></p>
            <p><strong>My Pending Leaves:</strong> <?php echo $myPendingLeaves; ?></p>
        </div>
    <?php endif; ?>
</div>

<div class="card">
    <h2>Actions</h2>
    <div>
        <?php if($isAdmin): ?>
            <!-- Admin Quick Actions - Full access -->
            <a href="employees.php" class="btn"> Manage Employees</a>
            <a href="add_employee.php" class="btn">Add New Employee</a>
            <a href="attendance.php" class="btn"> View All Attendance</a>
            <a href="mark_attendance.php" class="btn btn-success"> Mark Attendance</a>
            <a href="leave_requests.php" class="btn btn-warning">Manage Leave Requests</a>
            <a href="search.php" class="btn"> Search Records</a>
        <?php else: ?>
            <!-- Employee Quick Actions - Limited access -->
            <a href="mark_attendance.php" class="btn btn-success">✓ Mark My Attendance</a>
            <a href="apply_leave.php" class="btn btn-warning">Apply for Leave</a>
            <a href="my_attendance.php" class="btn">View My Attendance</a>
            <a href="my_leaves.php" class="btn">View My Leaves</a>
        <?php endif; ?>
    </div>
</div>

<?php if($isAdmin): ?>
    <!-- Admin Dashboard - Show recent employees and pending leaves -->
    <div>
        <!-- Recent Employees Table -->
        <div class="card">
            <h2>Recent Employees</h2>
            <?php
            try {
                // Fetch last 5 employees ordered by creation date
                $stmt = $pdo->query("SELECT employee_id, first_name, last_name, department, position, role FROM employees ORDER BY created_at DESC LIMIT 5");
                $recentEmployees = $stmt->fetchAll();
                
                // Check if any employees found
                if(count($recentEmployees) > 0) {
                    // Display table with employee data
                    echo '<table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Department</th>
                                    <th>Position</th>
                                    <th>Role</th>
                                </tr>
                            </thead>
                            <tbody>';
                    
                    // Loop through each employee and display in table row
                    foreach($recentEmployees as $emp) {
                        echo '<tr>
                                <td>' . htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) . '</td>
                                <td>' . htmlspecialchars($emp['department']) . '</td>
                                <td>' . htmlspecialchars($emp['position']) . '</td>
                                <td><span class="badge badge-info">' . htmlspecialchars($emp['role']) . '</span></td>
                              </tr>';
                    }
                    
                    echo '</tbody></table>';
                    
                    // Link to view all employees
                    echo '<p class="text-center mt-2"><a href="employees.php" class="btn btn-small">View All Employees</a></p>';
                } else {
                    // Display message if no employees found
                    echo '<p>No employees found.</p>';
                }
            } catch(PDOException $e) {
                // Display error message if query fails
                echo '<div class="alert alert-error">Error loading employees: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            ?>
        </div>
        
        <!-- Pending Leave Requests Table -->
        <div class="card">
            <h2>📋 Pending Leave Requests</h2>
            <?php
            try {
                // Fetch pending leave requests with employee names using JOIN
                $stmt = $pdo->query("
                    SELECT lr.leave_id, lr.leave_type, lr.start_date, lr.end_date, lr.total_days,
                           e.first_name, e.last_name 
                    FROM leave_requests lr
                    JOIN employees e ON lr.employee_id = e.employee_id
                    WHERE lr.status = 'Pending'
                    ORDER BY lr.request_date DESC
                    LIMIT 5
                ");
                $pendingRequests = $stmt->fetchAll();
                
                // Check if any pending requests found
                if(count($pendingRequests) > 0) {
                    // Display table with leave request data
                    echo '<table>
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Type</th>
                                    <th>Dates</th>
                                    <th>Days</th>
                                </tr>
                            </thead>
                            <tbody>';
                    
                    // Loop through each request and display in table row
                    foreach($pendingRequests as $req) {
                        echo '<tr>
                                <td>' . htmlspecialchars($req['first_name'] . ' ' . $req['last_name']) . '</td>
                                <td><span class="badge badge-warning">' . htmlspecialchars($req['leave_type']) . '</span></td>
                                <td>' . date('M d', strtotime($req['start_date'])) . ' - ' . date('M d', strtotime($req['end_date'])) . '</td>
                                <td>' . htmlspecialchars($req['total_days']) . ' days</td>
                              </tr>';
                    }
                    
                    echo '</tbody></table>';
                    
                    // Link to view all leave requests
                    echo '<p class="text-center mt-2"><a href="leave_requests.php" class="btn btn-small">Manage All Requests</a></p>';
                } else {
                    // Display message if no pending requests
                    echo '<p>No pending leave requests.</p>';
                }
            } catch(PDOException $e) {
                // Display error message if query fails
                echo '<div class="alert alert-error">Error loading leave requests: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            ?>
        </div>
    </div>

<?php else: ?>
    <!-- Employee Dashboard - Show their recent attendance and leaves -->
    <div>
        <!-- My Recent Attendance -->
        <div class="card">
            <h2>My Recent Attendance</h2>
            <?php
            try {
                // Fetch employee's last 5 attendance records
                $stmt = $pdo->prepare("
                    SELECT attendance_id, date, status, check_in_time, check_out_time 
                    FROM attendance 
                    WHERE employee_id = ? 
                    ORDER BY date DESC 
                    LIMIT 5
                ");
                $stmt->execute([$currentEmployeeId]);
                $myRecentAttendance = $stmt->fetchAll();
                
                // Check if any attendance found
                if(count($myRecentAttendance) > 0) {
                    // Display table with attendance data
                    echo '<table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Check In</th>
                                    <th>Check Out</th>
                                </tr>
                            </thead>
                            <tbody>';
                    
                    // Loop through each attendance record
                    foreach($myRecentAttendance as $att) {
                        // Determine badge color based on status
                        $badgeClass = 'badge-success';
                        if($att['status'] == 'Absent') $badgeClass = 'badge-danger';
                        if($att['status'] == 'Late') $badgeClass = 'badge-warning';
                        if($att['status'] == 'Half-day') $badgeClass = 'badge-info';
                        
                        echo '<tr>
                                <td>' . date('M d, Y', strtotime($att['date'])) . '</td>
                                <td><span class="badge ' . $badgeClass . '">' . htmlspecialchars($att['status']) . '</span></td>
                                <td>' . ($att['check_in_time'] ? date('h:i A', strtotime($att['check_in_time'])) : '-') . '</td>
                                <td>' . ($att['check_out_time'] ? date('h:i A', strtotime($att['check_out_time'])) : '-') . '</td>
                              </tr>';
                    }
                    
                    echo '</tbody></table>';
                    echo '<p class="text-center mt-2"><a href="my_attendance.php" class="btn btn-small">View All My Attendance</a></p>';
                } else {
                    echo '<p>No attendance records found.</p>';
                }
            } catch(PDOException $e) {
                echo '<div class="alert alert-error">Error loading attendance: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            ?>
        </div>
        
        <!-- My Leave Requests -->
        <div class="card">
            <h2>My Leave Requests</h2>
            <?php
            try {
                // Fetch employee's last 5 leave requests
                $stmt = $pdo->prepare("
                    SELECT leave_id, leave_type, start_date, end_date, total_days, status 
                    FROM leave_requests 
                    WHERE employee_id = ? 
                    ORDER BY request_date DESC 
                    LIMIT 5
                ");
                $stmt->execute([$currentEmployeeId]);
                $myLeaveRequests = $stmt->fetchAll();
                
                // Check if any leave requests found
                if(count($myLeaveRequests) > 0) {
                    // Display table with leave request data
                    echo '<table>
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Dates</th>
                                    <th>Days</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>';
                    
                    // Loop through each leave request
                    foreach($myLeaveRequests as $leave) {
                        // Determine badge color based on status
                        $badgeClass = 'badge-warning';
                        if($leave['status'] == 'Approved') $badgeClass = 'badge-success';
                        if($leave['status'] == 'Rejected') $badgeClass = 'badge-danger';
                        
                        echo '<tr>
                                <td>' . htmlspecialchars($leave['leave_type']) . '</td>
                                <td>' . date('M d', strtotime($leave['start_date'])) . ' - ' . date('M d', strtotime($leave['end_date'])) . '</td>
                                <td>' . htmlspecialchars($leave['total_days']) . ' days</td>
                                <td><span class="badge ' . $badgeClass . '">' . htmlspecialchars($leave['status']) . '</span></td>
                              </tr>';
                    }
                    
                    echo '</tbody></table>';
                    echo '<p class="text-center mt-2"><a href="my_leaves.php" class="btn btn-small">View All My Leaves</a></p>';
                } else {
                    echo '<p>No leave requests found.</p>';
                }
            } catch(PDOException $e) {
                echo '<div class="alert alert-error">Error loading leave requests: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            ?>
        </div>
    </div>
<?php endif; ?>

<?php 
// Include footer
include '../includes/footer.php'; 
?>