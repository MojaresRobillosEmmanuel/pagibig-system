<?php
// Check session
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

require_once __DIR__ . '/../../database/db_connect.php';

try {
    $conn = getConnection();
    $stlConn = getSTLConnection();
    
    // Get parameters
    $type = $_GET['type'] ?? 'active';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $search = $_GET['search'] ?? '';
    $offset = ($page - 1) * $limit;
    
    // Base query - ONLY get employees from selected_stl table (the working list)
    // Join with employees table to get full details
    // Note: ER is set to 0 for STL system as it only uses EE values
    $sql = "SELECT 
        e.id,
        e.pagibig_number,
        e.id_number,
        e.last_name,
        e.first_name,
        e.middle_name,
        e.tin,
        e.birthdate,
        COALESCE(ss.ee, e.ee, 0) as ee,
        0 as er,
        e.status
    FROM 
        selected_stl ss
    INNER JOIN employees e ON e.pagibig_number = ss.pagibig_no
    WHERE 
        e.status = ?
    AND e.system_type = 'stl'";
    
    // Add search condition if provided
    if (!empty($search)) {
        $sql .= " AND (
            e.last_name LIKE ? OR 
            e.first_name LIKE ? OR 
            e.middle_name LIKE ? OR
            e.pagibig_number LIKE ? OR
            e.id_number LIKE ?
        )";
    }
    
    // Add ordering
    $sql .= " ORDER BY e.last_name, e.first_name";
    
    // Add pagination
    $sql .= " LIMIT ? OFFSET ?";
    
    // Prepare and execute count query first
    $countSql = str_replace("SELECT 
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
        e.status", "SELECT COUNT(*) as total", $sql);
    $countSql = preg_replace("/LIMIT.*OFFSET.*/", "", $countSql);
    
    // Prepare statements
    $stmt = $conn->prepare($sql);
    $countStmt = $conn->prepare($countSql);
    
    // Bind parameters
    $status = ($type === 'active') ? 1 : 0;
    
    if (!empty($search)) {
        $searchPattern = "%$search%";
        $stmt->bind_param("issssii", $status, $searchPattern, $searchPattern, $searchPattern, $searchPattern, $searchPattern, $limit, $offset);
        $countStmt->bind_param("isssss", $status, $searchPattern, $searchPattern, $searchPattern, $searchPattern, $searchPattern);
    } else {
        $stmt->bind_param("iii", $status, $limit, $offset);
        $countStmt->bind_param("i", $status);
    }
    
    // Execute count query
    $countStmt->execute();
    $totalResult = $countStmt->get_result();
    $total = $totalResult->fetch_assoc()['total'];
    
    // Execute main query
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Get STL employees to check membership
    $stlQuery = "SELECT pagibig_no FROM selected_stl";
    $stlResult = $stlConn->query($stlQuery);
    $stlEmployees = array();
    while ($stlRow = $stlResult->fetch_assoc()) {
        $stlEmployees[$stlRow['pagibig_no']] = true;
    }
    
    // Fetch results
    $employees = [];
    while ($row = $result->fetch_assoc()) {
        // Format birthdate
        $bd = $row['birthdate'];
        $formattedBirthdate = null;
        
        if (!empty($bd) && $bd !== 'N/A' && $bd !== '0000-00-00' && $bd !== '0000-00-00 00:00:00') {
            $birthdateStr = trim($bd);
            if (!empty($birthdateStr)) {
                $timestamp = @strtotime($birthdateStr);
                
                if ($timestamp !== false && $timestamp > 0) {
                    $formattedBirthdate = @date('m/d/Y', $timestamp);
                } else {
                    // Try DateTime fallback
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
                        $formattedBirthdate = $date->format('m/d/Y');
                    }
                }
            }
        }
        
        $in_stl = isset($stlEmployees[$row['pagibig_number']]) ? 1 : 0;
        $employees[] = [
            'id' => $row['id'],
            'pagibig_no' => $row['pagibig_number'],
            'id_number' => $row['id_number'],
            'last_name' => $row['last_name'],
            'first_name' => $row['first_name'],
            'middle_name' => $row['middle_name'],
            'full_name' => $row['last_name'] . ', ' . $row['first_name'] . ' ' . ($row['middle_name'] ?? ''),
            'tin' => $row['tin'],
            'birthdate' => $formattedBirthdate,
            'ee' => $row['ee'],
            'er' => $row['er'],
            'status' => $row['status'],
            'in_stl' => $in_stl
        ];
    }
    
    // Calculate pagination info
    $totalPages = ceil($total / $limit);
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'data' => [
            'employees' => $employees,
            'pagination' => [
                'current' => $page,
                'pages' => $totalPages,
                'total' => $total,
                'limit' => $limit
            ]
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Error in get_stl_employees.php: " . $e->getMessage());
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

// Close connections
if (isset($stmt)) $stmt->close();
if (isset($countStmt)) $countStmt->close();
if (isset($conn)) $conn->close();
?>