<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../database/db_connect.php';

$conn = getConnection();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// 👇 IMPORTANT: Filter by system_type = 'stl'
$query = "SELECT id, pagibig_number, id_number, last_name, first_name, middle_name, tin, birthdate, status, system_type 
          FROM employees 
          WHERE status = 1 AND system_type = 'stl' 
          ORDER BY last_name, first_name";

$result = $conn->query($query);

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Query error: ' . $conn->error]);
    exit();
}

$employees = [];
while ($row = $result->fetch_assoc()) {
    // Format birthdate using strtotime and date to ensure MM/DD/YYYY format
    $bd = $row['birthdate'];
    
    // Check if birthdate is empty, NULL, or 'N/A' or '0000-00-00'
    if (empty($bd) || $bd === 'N/A' || $bd === '0000-00-00' || $bd === '0000-00-00 00:00:00') {
        $row['birthdate'] = null;
    } else {
        $birthdateStr = trim($bd);
        
        // If it's still empty after trim, set to null
        if (empty($birthdateStr)) {
            $row['birthdate'] = null;
        } else {
            // Try to parse with strtotime
            $timestamp = @strtotime($birthdateStr);
            
            if ($timestamp !== false && $timestamp > 0) {
                // Successfully parsed, format as MM/DD/YYYY
                $row['birthdate'] = @date('m/d/Y', $timestamp);
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
                    $row['birthdate'] = $date->format('m/d/Y');
                } else {
                    // Still failed, set to null
                    $row['birthdate'] = null;
                }
            }
        }
    }
    $employees[] = $row;
}

echo json_encode([
    'success' => true,
    'data' => $employees,
    'count' => count($employees),
    'system' => 'stl'
]);

$conn->close();
