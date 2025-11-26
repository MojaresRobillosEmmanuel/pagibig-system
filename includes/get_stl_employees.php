<?php
// Configure error handling
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
error_log("Starting get_stl_employees.php");

// Start session and require dependencies
session_start();
require_once '../database/db_connect.php';
require_once 'Response.php';

// Setup error logging
error_log('Starting get_stl_employees.php execution');

try {
    error_log('Attempting database connection');
    $conn = getConnection();
    if (!$conn) {
        throw new Exception('Failed to establish database connection');
    }
    error_log('Database connection successful');

    // Get and validate request parameters
    $type = isset($_GET['type']) ? $conn->real_escape_string(strip_tags($_GET['type'])) : 'active';
    $search = isset($_GET['search']) ? $conn->real_escape_string(strip_tags($_GET['search'])) : '';
    $page = (int)(filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1);
    $limit = (int)(filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT) ?: 10);
    $offset = ($page - 1) * $limit;

    error_log(sprintf(
        'Parameters: type=%s, search=%s, page=%d, limit=%d',
        $type,
        $search,
        $page,
        $limit
    ));

    // Tables should already exist, just log the start of the query
    error_log('Starting employee query');

    // Select the correct database
    if (!$conn->select_db('pagibig_db')) {
        throw new Exception('Failed to select main database: ' . $conn->error);
    }

    // Base query for STL employees with JOIN to STL table
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
            COALESCE(e.ee, 0) as ee,
            COALESCE(e.er, 0) as er,
            CONCAT(e.last_name, ', ', e.first_name, ' ', COALESCE(e.middle_name, '')) as full_name,
            CASE WHEN stl.id IS NOT NULL THEN 1 ELSE 0 END as in_stl
        FROM employees e
        LEFT JOIN selected_stl stl ON e.pagibig_number = stl.pagibig_no";
    error_log('Base query prepared');

    // Add active/inactive condition
    $activeValue = ($type === 'active' ? 1 : 0);
    $whereClause = " WHERE e.status = " . $activeValue;

    // Add search condition if search term is provided
    if ($search) {
        $searchTerm = "%{$search}%";
        $whereClause .= " AND (
            e.id_number LIKE '" . $conn->real_escape_string($searchTerm) . "' OR 
            e.last_name LIKE '" . $conn->real_escape_string($searchTerm) . "' OR 
            e.first_name LIKE '" . $conn->real_escape_string($searchTerm) . "' OR 
            e.middle_name LIKE '" . $conn->real_escape_string($searchTerm) . "' OR
            CONCAT(e.last_name, ', ', e.first_name, ' ', COALESCE(e.middle_name, '')) LIKE '" . $conn->real_escape_string($searchTerm) . "'
        )";
    }

    // ==============================
    // 1. Get total count
    // ==============================
    $countQuery = "
        SELECT COUNT(DISTINCT e.id) as total 
        FROM employees e
        " . $whereClause;
    $countResult = $conn->query($countQuery);
    if (!$countResult) {
        throw new Exception("Count query failed: " . $conn->error);
    }
    $total = (int)$countResult->fetch_assoc()['total'];
    error_log("Total count: $total");

    // ==============================
    // 2. Get paginated results
    // ==============================
    $query = $baseQuery . $whereClause . " ORDER BY e.last_name, e.first_name LIMIT $limit OFFSET $offset";
    $result = $conn->query($query);
    if (!$result) {
        throw new Exception("Main query failed: " . $conn->error);
    }

    $employees = array();
    while ($row = $result->fetch_assoc()) {
        $employees[] = array(
            'id'                  => $row['id'],
            'pagibig_number'      => $row['pagibig_number'],
            'id_number'           => $row['id_number'],
            'last_name'           => $row['last_name'],
            'first_name'          => $row['first_name'],
            'middle_name'         => $row['middle_name'],
            'full_name'           => $row['full_name'],
            'tin'                 => $row['tin'],
            'birthdate'           => $row['birthdate'],
            'status'              => $row['status'],
            'ee'                  => $row['ee'],
            'er'                  => $row['er']
        );
    }

    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'message' => 'Employees retrieved successfully',
        'data' => [
            'employees' => $employees,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit)
            ]
        ]
    ]);
} catch (Exception $e) {
    // Log the full error details
    error_log('Caught Exception: ' . $e->getMessage());
    error_log('Exception trace: ' . $e->getTraceAsString());
    
    // Send error response
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} finally {
    // No need to explicitly close PDO connections
    error_log('Finished get_stl_employees.php execution');
}
