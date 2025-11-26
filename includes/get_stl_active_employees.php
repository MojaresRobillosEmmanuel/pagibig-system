<?php
// Enable error reporting but don't display to output
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Start session
session_start();

// Include database connection
require_once '../database/db_connect.php';
require_once 'Response.php';

try {
    $conn = getConnection();
    $stlConn = getSTLConnection();
    
    // Get pagination parameters
    $page = (int)(filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1);
    $limit = (int)(filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT) ?: 10);
    $offset = ($page - 1) * $limit;
    $search = isset($_GET['search']) ? $conn->real_escape_string(strip_tags($_GET['search'])) : '';

    // Base query for active STL employees ONLY
    $baseQuery = "
        SELECT 
            e.id,
            e.pagibig_number,
            e.id_number,
            e.last_name,
            e.first_name,
            e.middle_name,
            e.tin,
            e.birthdate,
            e.status,
            e.ee,
            e.er,
            CONCAT(e.last_name, ', ', e.first_name, ' ', COALESCE(e.middle_name, '')) as full_name
        FROM employees e
        WHERE e.status = 1
        AND e.system_type = 'stl'";
    
    // Add search condition if provided
    if ($search) {
        $searchTerm = "%{$search}%";
        $baseQuery .= " AND (
            e.id_number LIKE '" . $conn->real_escape_string($searchTerm) . "' OR 
            e.last_name LIKE '" . $conn->real_escape_string($searchTerm) . "' OR 
            e.first_name LIKE '" . $conn->real_escape_string($searchTerm) . "'
        )";
    }

    // Get total count
    $countQuery = "SELECT COUNT(DISTINCT e.id) as total FROM employees e WHERE e.status = 1 AND e.system_type = 'stl'";
    if ($search) {
        $searchTerm = "%{$search}%";
        $countQuery .= " AND (
            e.id_number LIKE '" . $conn->real_escape_string($searchTerm) . "' OR 
            e.last_name LIKE '" . $conn->real_escape_string($searchTerm) . "' OR 
            e.first_name LIKE '" . $conn->real_escape_string($searchTerm) . "'
        )";
    }
    
    $countResult = $conn->query($countQuery);
    if (!$countResult) {
        throw new Exception("Count query failed: " . $conn->error);
    }
    $total = (int)$countResult->fetch_assoc()['total'];

    // Get paginated results
    $query = $baseQuery . " ORDER BY e.last_name, e.first_name LIMIT $limit OFFSET $offset";
    $result = $conn->query($query);

    if (!$result) {
        throw new Exception($conn->error);
    }

    $employees = array();
    while ($row = $result->fetch_assoc()) {
        // Check if employee is in STL using the STL connection
        $stlCheckQuery = "SELECT COUNT(*) as count FROM selected_stl WHERE pagibig_no = '" . $stlConn->real_escape_string($row['pagibig_number']) . "'";
        $stlCheckResult = $stlConn->query($stlCheckQuery);
        $in_stl = 0;
        if ($stlCheckResult) {
            $stlCheckRow = $stlCheckResult->fetch_assoc();
            $in_stl = ($stlCheckRow['count'] > 0) ? 1 : 0;
        }
        
        // Include all employees (both in STL and not in STL)
        // The checkbox state will be managed by the in_stl flag
        $employees[] = array(
            'id' => $row['id'],
            'pagibig_number' => $row['pagibig_number'],
            'id_number' => $row['id_number'],
            'last_name' => $row['last_name'],
            'first_name' => $row['first_name'],
            'middle_name' => $row['middle_name'],
            'full_name' => $row['full_name'],
            'tin' => $row['tin'],
            'birthdate' => $row['birthdate'],
            'ee' => $row['ee'],
            'er' => $row['er'],
            'status' => $row['status'],
            'in_stl' => $in_stl
        );
    }

    Response::success(array(
        'employees' => $employees,
        'pagination' => array(
            'pages' => ceil($total / $limit),
            'total' => $total,
            'page' => $page,
            'limit' => $limit
        )
    ), 'Active employees retrieved successfully');

} catch (Exception $e) {
    Response::error('Database error: ' . $e->getMessage());
}
