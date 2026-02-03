<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Generate CSRF token once per session
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?= $pageTitle ?? 'Employee Attendance System' ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">

</head>
<body>
	<header>
    <div class="header-content">
        <h1>Employee Attendance & Leave Management</h1>

        <?php if (isset($_SESSION['employee_id'])): ?>
        <div class="logout_btn">
            <a href="logout.php" class="btn">Logout</a>
        </div>
        <?php endif; ?>
    </div>
</header>

<div class="container">

