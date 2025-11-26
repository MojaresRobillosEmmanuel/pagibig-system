<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once __DIR__ . '/../database/db_connect.php';

// Set headers
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Check authentication
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Authentication required'
    ]);
    exit;
}

try {
    // Get database connection (uses pagibig_contributions)
    $conn = getConnection();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    // Get parameters
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $type = isset($_GET['type']) ? $_GET['type'] : 'active';
    
    // Calculate offset
    $offset = ($page - 1) * $limit;

    // Prepare the base query - use status = 1 for active employees
    // 👇 IMPORTANT: Filter by system_type = 'contribution'
    $statusValue = ($type === 'active' ? 1 : 0);
    $systemType = 'contribution';
    
    $sql = "SELECT 
                e.id,
                e.pagibig_number,
                e.id_number,
                e.last_name,
                e.first_name,
                e.middle_name,
                e.tin,
                e.birthdate,
                e.ee,
                e.er,
                e.status,
                e.system_type,
                CONCAT(e.last_name, ', ', e.first_name, ' ', COALESCE(e.middle_name, '')) as full_name,
                (CASE WHEN sc.id IS NOT NULL THEN 'Added' ELSE 'Not Added' END) as contribution_status
            FROM employees e
            LEFT JOIN selected_contributions sc ON e.pagibig_number = sc.pagibig_no AND sc.user_id = ?
            WHERE e.status = ? AND e.system_type = ?";

    // Add search condition if provided
    if (!empty($search)) {
        $sql .= " AND (
            e.last_name LIKE ? 
            OR e.first_name LIKE ? 
            OR e.middle_name LIKE ? 
            OR e.pagibig_number LIKE ?
            OR e.id_number LIKE ?
        )";
    }

    // Add ordering
    $sql .= " ORDER BY e.last_name, e.first_name";

    // Prepare the count query first
    $countSql = "SELECT COUNT(*) as total FROM employees e LEFT JOIN selected_contributions sc ON e.pagibig_number = sc.pagibig_no AND sc.user_id = ? WHERE e.status = ? AND e.system_type = ?";
    if (!empty($search)) {
        $countSql .= " AND (
            e.last_name LIKE ? 
            OR e.first_name LIKE ? 
            OR e.middle_name LIKE ? 
            OR e.pagibig_number LIKE ?
            OR e.id_number LIKE ?
        )";
    }

    // Execute count query
    $countStmt = $conn->prepare($countSql);
    if (!$countStmt) {
        throw new Exception("Prepare count query failed: " . $conn->error);
    }

    if (!empty($search)) {
        $searchTerm = "%$search%";
        $countStmt->bind_param('isssssss', $_SESSION['user_id'], $statusValue, $systemType, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    } else {
        $countStmt->bind_param('iss', $_SESSION['user_id'], $statusValue, $systemType);
    }
    
    if (!$countStmt->execute()) {
        throw new Exception("Execute count query failed: " . $countStmt->error);
    }
    
    $countResult = $countStmt->get_result()->fetch_assoc();
    $totalCount = $countResult['total'] ?? 0;

    // Add limit and offset to main query
    $sql .= " LIMIT ? OFFSET ?";
    
    // Prepare and execute the main query
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare main query failed: " . $conn->error);
    }

    // Build parameter string for binding
    if (!empty($search)) {
        $searchTerm = "%$search%";
        $paramTypes = 'isssssssii';
        $stmt->bind_param($paramTypes, $_SESSION['user_id'], $statusValue, $systemType, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit, $offset);
    } else {
        $stmt->bind_param('issii', $_SESSION['user_id'], $statusValue, $systemType, $limit, $offset);
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Execute main query failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $employees = $result->fetch_all(MYSQLI_ASSOC);
    
    // Format birthdate for all employees using strtotime and date
    foreach ($employees as &$employee) {
        $bd = $employee['birthdate'];
        
        // Check if birthdate is empty, NULL, or 'N/A'
        if (empty($bd) || $bd === 'N/A' || $bd === '0000-00-00' || $bd === '0000-00-00 00:00:00') {
            $employee['birthdate'] = null;
            continue;
        }
        
        // Trim whitespace
        $birthdateStr = trim($bd);
        
        // If it's still empty after trim, set to null
        if (empty($birthdateStr)) {
            $employee['birthdate'] = null;
            continue;
        }
        
        // Try to parse with strtotime
        $timestamp = @strtotime($birthdateStr);
        
        if ($timestamp !== false && $timestamp > 0) {
            // Successfully parsed, format as MM/DD/YYYY
            $employee['birthdate'] = @date('m/d/Y', $timestamp);
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
                $employee['birthdate'] = $date->format('m/d/Y');
            } else {
                // Still failed, set to null
                $employee['birthdate'] = null;
            }
        }
    }
    unset($employee);

    // Calculate pagination info
    $totalPages = ceil($totalCount / $limit);

    echo json_encode([
        'success' => true,
        'data' => $employees,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $totalCount,
            'pages' => $totalPages
        ],
        'system' => 'contribution'
    ]);

} catch (Exception $e) {
    error_log('Error in get_active_employees.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load employees: ' . $e->getMessage()
    ]);
}

// Clean up
if (isset($stmt)) {
    $stmt->close();
}
if (isset($countStmt)) {
    $countStmt->close();
}
if (isset($conn)) {
    $conn->close();
}
?>
