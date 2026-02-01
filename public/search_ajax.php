<?php
require_once '../includes/auth_check.php';
require_once '../config/db.php';

header('Content-Type: application/json');

// Get search parameters
$search_type = isset($_GET['search_type']) ? $_GET['search_type'] : '';
$search_query = isset($_GET['search_query']) ? trim($_GET['search_query']) : '';
$search_month = isset($_GET['search_month']) ? $_GET['search_month'] : '';
$leave_type = isset($_GET['leave_type']) ? $_GET['leave_type'] : '';
$leave_status = isset($_GET['leave_status']) ? $_GET['leave_status'] : '';

// Check if user is admin
$isAdmin = isAdmin();
$currentEmployeeId = $_SESSION['employee_id'];

$response = [
    'type' => $search_type,
    'is_admin' => $isAdmin,
    'results' => [],
    'error' => null
];

try {
    if ($search_type == 'employees' && $isAdmin) {
        // Search employees (admin only)
        if (!empty($search_query)) {
            $stmt = $pdo->prepare("
                SELECT employee_id, first_name, last_name, email, phone, department, position, role 
                FROM employees 
                WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR department LIKE ? OR position LIKE ?
                ORDER BY first_name
                LIMIT 50
            ");
            $search_param = "%$search_query%";
            $stmt->execute([$search_param, $search_param, $search_param, $search_param, $search_param]);
            $response['results'] = $stmt->fetchAll();
        }
    }
    elseif ($search_type == 'attendance') {
        // Search attendance
        if ($isAdmin) {
            // Admin can search all attendance
            $sql = "
                SELECT a.*, e.first_name, e.last_name 
                FROM attendance a
                JOIN employees e ON a.employee_id = e.employee_id
                WHERE 1=1
            ";
            $params = [];
            
            if (!empty($search_query)) {
                $sql .= " AND (e.first_name LIKE ? OR e.last_name LIKE ?)";
                $search_param = "%$search_query%";
                $params[] = $search_param;
                $params[] = $search_param;
            }
            if (!empty($search_month)) {
                $sql .= " AND DATE_FORMAT(a.date, '%Y-%m') = ?";
                $params[] = $search_month;
            }
            
            $sql .= " ORDER BY a.date DESC LIMIT 100";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $response['results'] = $stmt->fetchAll();
        } else {
            // Employee can only search their own attendance
            $sql = "SELECT * FROM attendance WHERE employee_id = ?";
            $params = [$currentEmployeeId];
            
            if (!empty($search_month)) {
                $sql .= " AND DATE_FORMAT(date, '%Y-%m') = ?";
                $params[] = $search_month;
            }
            
            $sql .= " ORDER BY date DESC LIMIT 100";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $response['results'] = $stmt->fetchAll();
        }
    }
    elseif ($search_type == 'leaves') {
        // Search leave requests
        if ($isAdmin) {
            // Admin can search all leaves
            $sql = "
                SELECT lr.*, e.first_name, e.last_name, e.department 
                FROM leave_requests lr
                JOIN employees e ON lr.employee_id = e.employee_id
                WHERE 1=1
            ";
            $params = [];
            
            if (!empty($search_query)) {
                $sql .= " AND (e.first_name LIKE ? OR e.last_name LIKE ?)";
                $search_param = "%$search_query%";
                $params[] = $search_param;
                $params[] = $search_param;
            }
            if (!empty($leave_type)) {
                $sql .= " AND lr.leave_type = ?";
                $params[] = $leave_type;
            }
            if (!empty($leave_status)) {
                $sql .= " AND lr.status = ?";
                $params[] = $leave_status;
            }
            
            $sql .= " ORDER BY lr.request_date DESC LIMIT 100";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $response['results'] = $stmt->fetchAll();
        } else {
            // Employee can only search their own leaves
            $sql = "SELECT * FROM leave_requests WHERE employee_id = ?";
            $params = [$currentEmployeeId];
            
            if (!empty($leave_type)) {
                $sql .= " AND leave_type = ?";
                $params[] = $leave_type;
            }
            if (!empty($leave_status)) {
                $sql .= " AND status = ?";
                $params[] = $leave_status;
            }
            
            $sql .= " ORDER BY request_date DESC LIMIT 100";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $response['results'] = $stmt->fetchAll();
        }
    }
} catch(PDOException $e) {
    $response['error'] = "Error performing search. Please try again.";
    error_log("Search error: " . $e->getMessage());
}

// Return JSON response
echo json_encode($response);
?>