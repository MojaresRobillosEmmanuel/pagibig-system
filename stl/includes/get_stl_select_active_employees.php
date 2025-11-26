<?php
// Enable error reporting but don't display to output
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Start session
session_start();

// Include database connection
require_once '../../database/db_connect.php';

header('Content-Type: application/json');

try {
    $conn = getConnection();
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    // Query to get ACTIVE STL employees (status = 1 and system_type = 'stl')
    $query = "
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
            e.system_type,
            CONCAT(e.last_name, ', ', e.first_name, ' ', COALESCE(e.middle_name, '')) as full_name,
            CASE 
                WHEN EXISTS(
                    SELECT 1 FROM selected_stl WHERE pagibig_no = e.pagibig_number
                ) THEN 'already added'
                ELSE 'not added'
            END as stl_status
        FROM employees e
        WHERE e.status = 1
        AND e.system_type = 'stl'
        ORDER BY e.last_name, e.first_name
    ";
    
    $result = $conn->query($query);

    if (!$result) {
        throw new Exception('Query failed: ' . $conn->error);
    }

    $employees = array();
    while ($row = $result->fetch_assoc()) {
        // Format birthdate
        $bd = $row['birthdate'];
        if (empty($bd) || $bd === 'N/A' || $bd === '0000-00-00' || $bd === '0000-00-00 00:00:00') {
            $row['birthdate'] = null;
        } else {
            $birthdateStr = trim($bd);
            if (!empty($birthdateStr)) {
                $timestamp = @strtotime($birthdateStr);
                if ($timestamp !== false && $timestamp > 0) {
                    $row['birthdate'] = @date('m/d/Y', $timestamp);
                } else {
                    $row['birthdate'] = null;
                }
            } else {
                $row['birthdate'] = null;
            }
        }
        
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
            'stl_status' => $row['stl_status']
        );
    }

    echo json_encode([
        'success' => true,
        'data' => $employees,
        'message' => 'Active STL employees retrieved successfully'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    error_log('Error in stl/includes/get_stl_select_active_employees.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage(),
        'data' => []
    ]);
}
?>
