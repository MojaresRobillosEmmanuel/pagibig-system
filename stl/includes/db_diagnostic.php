<?php
/**
 * Database Diagnostic Tool
 * Check what's in the selected_stl and employees tables
 */

session_start();

// Allow access - comment out the check for testing
// if (!isset($_SESSION['user_id'])) {
//     http_response_code(403);
//     die('Access Denied');
// }

require_once __DIR__ . '/../../database/db_connect.php';

header('Content-Type: application/json');

try {
    $conn = getConnection();
    
    // Check if selected_stl table exists
    $tableCheckSql = "SHOW TABLES LIKE 'selected_stl'";
    $tableCheckResult = $conn->query($tableCheckSql);
    $tableExists = $tableCheckResult->num_rows > 0;
    
    $diagnostics = [
        'status' => 'success',
        'database' => 'pagibig_db',
        'selected_stl_exists' => $tableExists,
        'data' => []
    ];
    
    if ($tableExists) {
        // Count total records in selected_stl
        $countSql = "SELECT COUNT(*) as total FROM selected_stl";
        $countResult = $conn->query($countSql);
        $countRow = $countResult->fetch_assoc();
        $diagnostics['selected_stl_total_records'] = $countRow['total'];
        
        // Get all records from selected_stl with employee details
        $dataSql = "SELECT 
            ss.id,
            ss.pagibig_no,
            ss.id_number,
            ss.last_name,
            ss.first_name,
            ss.middle_name,
            ss.tin,
            ss.birthdate,
            ss.ee,
            ss.er,
            ss.is_active,
            ss.date_added,
            e.status as employee_status,
            e.system_type
        FROM selected_stl ss
        LEFT JOIN employees e ON e.pagibig_number = ss.pagibig_no
        ORDER BY ss.date_added DESC
        LIMIT 20";
        
        $dataResult = $conn->query($dataSql);
        $records = [];
        while ($row = $dataResult->fetch_assoc()) {
            $records[] = $row;
        }
        
        $diagnostics['data']['selected_stl_records'] = $records;
        
        // Check for STL employees in employees table
        $stlEmployeesSql = "SELECT COUNT(*) as total FROM employees WHERE system_type = 'stl'";
        $stlEmployeesResult = $conn->query($stlEmployeesSql);
        $stlEmployeesRow = $stlEmployeesResult->fetch_assoc();
        $diagnostics['stl_employees_in_employees_table'] = $stlEmployeesRow['total'];
        
        // Check for active STL employees
        $activeStlSql = "SELECT COUNT(*) as total FROM employees WHERE system_type = 'stl' AND status = 1";
        $activeStlResult = $conn->query($activeStlSql);
        $activeStlRow = $activeStlResult->fetch_assoc();
        $diagnostics['active_stl_employees'] = $activeStlRow['total'];
        
        // Check the JOIN result that the get_stl_employees.php uses
        $joinTestSql = "SELECT COUNT(*) as total FROM selected_stl ss
                        INNER JOIN employees e ON e.pagibig_number = ss.pagibig_no
                        WHERE e.status = 1 AND e.system_type = 'stl'";
        $joinTestResult = $conn->query($joinTestSql);
        $joinTestRow = $joinTestResult->fetch_assoc();
        $diagnostics['active_stl_employees_in_selected_stl'] = $joinTestRow['total'];
        
        // Get sample of the JOIN query results
        $joinSampleSql = "SELECT 
            e.pagibig_number,
            e.id_number,
            e.last_name,
            e.first_name,
            e.status,
            e.system_type,
            ss.id as ss_id,
            ss.ee,
            ss.er
        FROM selected_stl ss
        INNER JOIN employees e ON e.pagibig_number = ss.pagibig_no
        WHERE e.status = 1 AND e.system_type = 'stl'
        LIMIT 10";
        
        $joinSampleResult = $conn->query($joinSampleSql);
        $joinSamples = [];
        while ($row = $joinSampleResult->fetch_assoc()) {
            $joinSamples[] = $row;
        }
        
        $diagnostics['data']['join_query_samples'] = $joinSamples;
    }
    
    echo json_encode($diagnostics, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>
