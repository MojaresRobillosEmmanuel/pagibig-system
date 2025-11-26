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

    // Get all STL history - employees that were added to STL
    // We'll show employees that are in the STL system but with non-pending status
    // Or we can show active employees not in STL as candidates for reactivation
    
    // Query: Active employees NOT currently in STL (these are candidates for reactivation)
    // First get the STL employee list from STL database
    $stlQuery = "SELECT DISTINCT pagibig_no FROM selected_stl";
    $stlResult = $stlConn->query($stlQuery);
    
    $stlPagibigNumbers = array();
    if ($stlResult) {
        while ($row = $stlResult->fetch_assoc()) {
            $stlPagibigNumbers[] = $conn->real_escape_string($row['pagibig_no']);
        }
    }
    
    // Build query for employees NOT in STL
    if (empty($stlPagibigNumbers)) {
        // If no employees in STL, show all active employees
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
                CONCAT(e.last_name, ', ', e.first_name, ' ', COALESCE(e.middle_name, '')) as full_name,
                0 as in_stl
            FROM employees e
            WHERE e.status = 1";
    } else {
        // Show active employees that are NOT in the selected_stl table
        $pagibigList = "'" . implode("','", $stlPagibigNumbers) . "'";
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
                CONCAT(e.last_name, ', ', e.first_name, ' ', COALESCE(e.middle_name, '')) as full_name,
                0 as in_stl
            FROM employees e
            WHERE e.status = 1
            AND e.pagibig_number NOT IN ($pagibigList)";
    }
    
    // Add search condition if provided
    if ($search) {
        $searchTerm = "%{$search}%";
        $baseQuery .= " AND (
            e.id_number LIKE '" . $conn->real_escape_string($searchTerm) . "' OR 
            e.last_name LIKE '" . $conn->real_escape_string($searchTerm) . "' OR 
            e.first_name LIKE '" . $conn->real_escape_string($searchTerm) . "'
        )";
    }

    // Get total count based on baseQuery
    if (empty($stlPagibigNumbers)) {
        // Count all active employees
        $countQuery = "SELECT COUNT(DISTINCT e.id) as total FROM employees e WHERE e.status = 1";
    } else {
        // Count active employees NOT in STL
        $pagibigList = "'" . implode("','", $stlPagibigNumbers) . "'";
        $countQuery = "SELECT COUNT(DISTINCT e.id) as total FROM employees e 
            WHERE e.status = 1 
            AND e.pagibig_number NOT IN ($pagibigList)";
    }
    
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
        $employees[] = array(
            'id' => $row['id'],
            'pagibig_number' => $row['pagibig_number'],
            'pagibig_no' => $row['pagibig_number'],  // Add both field names for compatibility
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
            'in_stl' => $row['in_stl']
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
    ), 'Inactive STL employees retrieved successfully');

} catch (Exception $e) {
    Response::error('Database error: ' . $e->getMessage());
}
?>
