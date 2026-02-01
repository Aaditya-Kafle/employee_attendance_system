<?php
// Check authentication
require_once '../includes/auth_check.php';

// Include database connection
require_once '../config/db.php';

// Set page title
$pageTitle = 'Search';

// Check if user is admin
$isAdmin = isAdmin();
$currentEmployeeId = $_SESSION['employee_id'];

// Include header
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
    
    <!-- Results will appear here -->
    <div id="search_results"></div>
</div>

<script>
    // Pass PHP variables to JavaScript
    const isUserAdmin = <?= $isAdmin ? 'true' : 'false'; ?>;
</script>


<script src="../assets/js/search.js"></script>

<?php include '../includes/footer.php'; ?>