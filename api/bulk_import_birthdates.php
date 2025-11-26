<?php
/**
 * Bulk Import Birthdates API
 * Handles CSV data import and updates employee birthdates
 */

header('Content-Type: application/json');

require_once '../database/db_connect.php';

try {
    // Check if POST data exists
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Get JSON data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['records']) || !is_array($input['records'])) {
        throw new Exception('Invalid data format');
    }

    $records = $input['records'];
    $conn = getConnection();
    
    $results = [];
    $summary = [
        'success' => 0,
        'error' => 0,
        'skipped' => 0
    ];

    foreach ($records as $record) {
        $idNumber = trim($record['idNumber']);
        $birthdate = trim($record['birthdate']);
        
        // Validate format again (safety check)
        if (!preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $birthdate)) {
            $results[] = [
                'idNumber' => $idNumber,
                'birthdate' => $birthdate,
                'name' => '-',
                'status' => 'error',
                'message' => 'Invalid birthdate format'
            ];
            $summary['error']++;
            continue;
        }

        // Get employee info
        $getStmt = $conn->prepare(
            "SELECT id, first_name, last_name, birthdate as current_birthdate 
             FROM employees 
             WHERE id_number = ? AND system_type = 'contribution' 
             LIMIT 1"
        );
        
        if (!$getStmt) {
            $results[] = [
                'idNumber' => $idNumber,
                'birthdate' => $birthdate,
                'name' => '-',
                'status' => 'error',
                'message' => 'Database query error'
            ];
            $summary['error']++;
            continue;
        }

        $getStmt->bind_param('s', $idNumber);
        $getStmt->execute();
        $result = $getStmt->get_result();
        $employee = $result->fetch_assoc();
        $getStmt->close();

        if (!$employee) {
            $results[] = [
                'idNumber' => $idNumber,
                'birthdate' => $birthdate,
                'name' => '-',
                'status' => 'skipped',
                'message' => 'Employee not found'
            ];
            $summary['skipped']++;
            continue;
        }

        $name = $employee['last_name'] . ', ' . $employee['first_name'];
        $empId = $employee['id'];
        $currentBirthdate = $employee['current_birthdate'];

        // Check if already has valid birthdate
        if ($currentBirthdate && $currentBirthdate !== '' && $currentBirthdate !== null) {
            // Optionally skip if already has birthdate
            // Uncomment next 9 lines to skip employees who already have birthdates
            /*
            $results[] = [
                'idNumber' => $idNumber,
                'birthdate' => $birthdate,
                'name' => $name,
                'status' => 'skipped',
                'message' => 'Already has birthdate: ' . $currentBirthdate
            ];
            $summary['skipped']++;
            continue;
            */
        }

        // Update the employee
        $updateStmt = $conn->prepare(
            "UPDATE employees SET birthdate = ? WHERE id = ? AND system_type = 'contribution'"
        );
        
        if (!$updateStmt) {
            $results[] = [
                'idNumber' => $idNumber,
                'birthdate' => $birthdate,
                'name' => $name,
                'status' => 'error',
                'message' => 'Update query error'
            ];
            $summary['error']++;
            continue;
        }

        $updateStmt->bind_param('si', $birthdate, $empId);
        
        if ($updateStmt->execute()) {
            if ($updateStmt->affected_rows > 0) {
                $results[] = [
                    'idNumber' => $idNumber,
                    'birthdate' => $birthdate,
                    'name' => $name,
                    'status' => 'success',
                    'message' => 'Updated successfully'
                ];
                $summary['success']++;
            } else {
                $results[] = [
                    'idNumber' => $idNumber,
                    'birthdate' => $birthdate,
                    'name' => $name,
                    'status' => 'error',
                    'message' => 'No rows updated'
                ];
                $summary['error']++;
            }
        } else {
            $results[] = [
                'idNumber' => $idNumber,
                'birthdate' => $birthdate,
                'name' => $name,
                'status' => 'error',
                'message' => 'Execution error: ' . $updateStmt->error
            ];
            $summary['error']++;
        }
        
        $updateStmt->close();
    }

    $conn->close();

    echo json_encode([
        'success' => true,
        'results' => $results,
        'summary' => $summary
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
