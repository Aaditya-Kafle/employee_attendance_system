<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

$pageTitle = 'Search';

// Check if user is admin
$isAdmin = isAdmin();
$currentEmployeeId = $_SESSION['employee_id'];

include '../includes/header.php';
?>

<div class="card">
    <h2>Search</h2>
    
    <div class="form-group">
        <label>Search Type *</label>
        <select id="search_type" required>
            <option value="">Select Search Type</option>
            <?php if($isAdmin): ?>
            <option value="employees">Employees</option>
            <?php endif; ?>
            <option value="attendance">Attendance Records</option>
            <option value="leaves">Leave Requests</option>
        </select>
    </div>
    
    <!-- Search fields will appear here based on search type -->
    <div id="search_fields"></div>
    <!-- Loading indicator -->
     <div id="loading" style="display:none; margin: 10px 0; font-weight: bold;">
       Loading...
    </div>
    <!-- Results will appear here -->
    <div id="search_results"></div>
</div>

<script>
    // Pass PHP variables to JavaScript
    const isUserAdmin = <?= $isAdmin ? 'true' : 'false'; ?>;
</script>
<a href="index.php" class="btn">Back to Dashboard</a>

<script src="../assets/js/search.js"></script>

<?php include '../includes/footer.php'; ?>