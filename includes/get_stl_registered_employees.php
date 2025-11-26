<?php
// Enable error reporting but don't display to output
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Start session
session_start();

// Include database connection
require_once '../database/db_connect.php';
require_once 'Response.php';

error_log('==== get_stl_registered_employees.php called ====');

try {
    $conn = getConnection();
    $stlConn = getSTLConnection();
    
    error_log('Connections established');
    
    // Get pagination parameters
    $page = (int)(filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1);
    $limit = (int)(filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT) ?: 10);
    $offset = ($page - 1) * $limit;
    $search = isset($_GET['search']) ? $conn->real_escape_string(strip_tags($_GET['search'])) : '';

    error_log("Page: $page, Limit: $limit, Offset: $offset, Search: $search");

    // First, get all distinct pagibig_no from selected_stl table in STL database
    $stlQuery = "SELECT DISTINCT pagibig_no FROM selected_stl";
    error_log("STL Query: $stlQuery");
    
    $stlResult = $stlConn->query($stlQuery);
    
    if (!$stlResult) {
        throw new Exception("Failed to query STL table: " . $stlConn->error);
    }
    
    // Collect all pagibig numbers from STL
    $stlPagibigNumbers = array();
    while ($row = $stlResult->fetch_assoc()) {
        $stlPagibigNumbers[] = $conn->real_escape_string($row['pagibig_no']);
    }
    
    error_log("Found " . count($stlPagibigNumbers) . " STL records");
    
    if (empty($stlPagibigNumbers)) {
        error_log("No employees in STL - returning empty response");
        // No employees in STL yet
        Response::success(array(
            'employees' => array(),
            'pagination' => array(
                'pages' => 1,
                'total' => 0,
                'page' => $page,
                'limit' => $limit
            )
        ), 'Registered STL employees retrieved successfully');
        exit;
    }
    
    // Build the WHERE clause with pagibig numbers
    $pagibigList = "'" . implode("','", $stlPagibigNumbers) . "'";
    
    error_log("Pagibig list for query: " . substr($pagibigList, 0, 100) . "...");
    
    // Get active employees from pagibig_contributions that are in STL
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
        WHERE e.pagibig_number IN ($pagibigList)
        AND e.status = 1";
    
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
    $countQuery = "
        SELECT COUNT(DISTINCT e.id) as total 
        FROM employees e
        WHERE e.pagibig_number IN ($pagibigList)
        AND e.status = 1";
    
    if ($search) {
        $searchTerm = "%{$search}%";
        $countQuery .= " AND (
            e.id_number LIKE '" . $conn->real_escape_string($searchTerm) . "' OR 
            e.last_name LIKE '" . $conn->real_escape_string($searchTerm) . "' OR 
            e.first_name LIKE '" . $conn->real_escape_string($searchTerm) . "'
        )";
    }
    
    error_log("Count query executing...");
    $countResult = $conn->query($countQuery);
    if (!$countResult) {
        throw new Exception("Count query failed: " . $conn->error);
    }
    $total = (int)$countResult->fetch_assoc()['total'];
    
    error_log("Total employees found: $total");

    // Get paginated results
    $query = $baseQuery . " ORDER BY e.last_name, e.first_name LIMIT $limit OFFSET $offset";
    error_log("Executing main query...");
    
    $result = $conn->query($query);

    if (!$result) {
        throw new Exception($conn->error);
    }

    $employees = array();
    while ($row = $result->fetch_assoc()) {
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
            'in_stl' => 1
        );
    }
    
    error_log("Employees collected: " . count($employees));

    Response::success(array(
        'employees' => $employees,
        'pagination' => array(
            'pages' => ceil($total / $limit),
            'total' => $total,
            'page' => $page,
            'limit' => $limit
        )
    ), 'Registered STL employees retrieved successfully');

} catch (Exception $e) {
    error_log('ERROR in get_stl_registered_employees.php: ' . $e->getMessage());
    Response::error('Database error: ' . $e->getMessage());
}
?>
