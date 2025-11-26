<?php
// Start output buffering first
ob_start();

// Start session and include required files
session_start();
require_once 'database/db_connect.php';

// Disable error display but keep logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Clear any existing output
ob_clean();

// Set headers
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in. Please log in again.',
        'code' => 'AUTH_REQUIRED'
    ]);
    exit;
}

try {
    // First ensure we have a valid connection
    $conn = getConnection();
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    // Check if table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'selected_contributions'");
    if ($tableCheck->num_rows === 0) {
        // Table doesn't exist, create it
        require_once 'database/setup_tables.php';
    }
    
    // Get selected contributions for current user with employee details from employees table
    // IMPORTANT: Use proper JOIN to get employee data including birthdate
    $query = "SELECT 
                sc.id,
                sc.pagibig_no,
                sc.id_number,
                sc.user_id,
                sc.date_added,
                e.first_name,
                e.last_name,
                e.middle_name,
                e.pagibig_number,
                e.tin,
                e.birthdate,
                e.ee,
                e.er
              FROM selected_contributions sc
              LEFT JOIN employees e ON (
                e.pagibig_number = sc.pagibig_no 
                AND e.system_type = 'contribution'
              )
              WHERE sc.user_id = ?
              ORDER BY sc.date_added DESC";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $conn->error);
    }
    
    $userId = $_SESSION['user_id'];
    $stmt->bind_param('i', $userId);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to execute query: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if (!$result) {
        throw new Exception('Failed to get result: ' . $stmt->error);
    }
    
    $employees = $result->fetch_all(MYSQLI_ASSOC);
    
    // Format birthdate for all employees using strtotime and date
    foreach ($employees as &$emp) {
        $bd = $emp['birthdate'];
        
        // Check if birthdate is empty, NULL, or 'N/A'
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
    
    // Log successful fetch
    error_log("Successfully fetched " . count($employees) . " employees for user ID: " . $userId);
    
    // Close statement
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'employees' => $employees,
        'count' => count($employees),
        'timestamp' => time()
    ]);
    
} catch (Exception $e) {
    error_log('Error in get_selected_contributions.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching data: ' . $e->getMessage(),
        'code' => 'DB_ERROR'
    ]);
}

// Clean up output buffer
if (ob_get_length()) ob_end_flush();
?>
