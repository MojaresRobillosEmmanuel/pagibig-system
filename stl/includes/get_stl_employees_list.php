<?php
session_start();
require_once '../../database/pdo_connect.php';

header('Content-Type: application/json');

try {
    // Get PDO connection
    $pdo = getPDOConnection();
    
    if (!$pdo) {
        throw new Exception("Database connection not established");
    }

    // Get user ID from session
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("User not logged in");
    }
    $user_id = $_SESSION['user_id'];

    // Build the query dynamically based on available columns
    $queryColumns = "e.id, e.id_number, e.pagibig_number, e.last_name, e.first_name, e.middle_name, e.tin, e.birthdate, e.status";
    
    // Check if ee and er columns exist
    $stmt = $pdo->prepare("SHOW COLUMNS FROM employees LIKE 'ee'");
    $stmt->execute();
    $hasEE = $stmt->rowCount() > 0;
    
    $stmt = $pdo->prepare("SHOW COLUMNS FROM employees LIKE 'er'");
    $stmt->execute();
    $hasER = $stmt->rowCount() > 0;
    
    // Add ee and er to query if they exist
    if ($hasEE) {
        $queryColumns .= ", e.ee";
    } else {
        $queryColumns .= ", 0 as ee";
    }
    
    if ($hasER) {
        $queryColumns .= ", e.er";
    } else {
        $queryColumns .= ", 0 as er";
    }
    
    // Check if system_type column exists
    $stmt = $pdo->prepare("SHOW COLUMNS FROM employees LIKE 'system_type'");
    $stmt->execute();
    $hasSystemType = $stmt->rowCount() > 0;

    // Prepare the query to get active employees from pagibig_db database
    $query = "SELECT 
                $queryColumns,
                CASE WHEN stl.id IS NOT NULL THEN 1 ELSE 0 END as is_selected
            FROM employees e
            INNER JOIN selected_stl stl 
                ON e.pagibig_number = stl.pagibig_no
            WHERE e.status = 1";
    
    // Only filter by system_type if column exists
    if ($hasSystemType) {
        $query .= " AND e.system_type = 'stl'";
    }
    
    $query .= " ORDER BY e.last_name, e.first_name";

    $stmt = $pdo->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $pdo->errorInfo()[2]);
    }

    if (!$stmt->execute()) {
        throw new Exception("Failed to execute query: " . implode(", ", $pdo->errorInfo()));
    }

    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format birthdate for all employees using strtotime and date
    foreach ($employees as &$emp) {
        $bd = $emp['birthdate'];
        
        // Check if birthdate is empty, NULL, or 'N/A' or '0000-00-00'
        if (empty($bd) || $bd === 'N/A' || $bd === '0000-00-00' || $bd === '0000-00-00 00:00:00') {
            $emp['birthdate'] = null;
            continue;
        }
        
        // Trim whitespace
        $birthdateStr = trim($bd);
        
        // If it's still empty after trim, set to null
        if (empty($birthdateStr)) {
            $emp['birthdate'] = null;
            continue;
        }
        
        // Try to parse with strtotime
        $timestamp = @strtotime($birthdateStr);
        
        if ($timestamp !== false && $timestamp > 0) {
            // Successfully parsed, format as MM/DD/YYYY
            $emp['birthdate'] = @date('m/d/Y', $timestamp);
        } else {
            // strtotime failed, try DateTime fallback
            $date = null;
            $formats = ['m/d/Y', 'm/d/y', 'n/j/Y', 'n/j/y', 'm-d-Y', 'm-d-y', 'n-j-Y', 'n-j-y', 'Y-m-d', 'Y/m/d'];
            
            foreach ($formats as $format) {
                try {
                    $parsed = DateTime::createFromFormat($format, $birthdateStr);
                    if ($parsed && $parsed->format('Y') > 1900) {
                        $date = $parsed;
                        break;
                    }
                } catch (Exception $e) {
                    continue;
                }
            }
            
            if ($date) {
                $emp['birthdate'] = $date->format('m/d/Y');
            } else {
                // Still failed, set to null
                $emp['birthdate'] = null;
            }
        }
    }
    unset($emp);

    echo json_encode([
        'success' => true,
        'message' => 'Employees fetched successfully',
        'data' => $employees
    ]);

} catch (Exception $e) {
    error_log("Error in get_stl_employees_list.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch employees: ' . $e->getMessage()
    ]);
}
?>
