<?php
require_once __DIR__ . '/../database/db_connect.php';

// Set error reporting for debugging
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);

// Set header for JSON response
header('Content-Type: application/json');

// Get input data (JSON or POST)
$data = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentType = $_SERVER['HTTP_CONTENT_TYPE'] ?? $_SERVER['CONTENT_TYPE'] ?? '';
    
    if (stripos($contentType, 'application/json') !== false) {
        // Handle JSON input
        $jsonData = file_get_contents('php://input');
        $data = json_decode($jsonData, true);
    } else {
        // Handle form data (POST)
        $data = $_POST;
    }
}

if (!$data) {
    echo json_encode([
        'success' => false,
        'message' => 'No data provided'
    ]);
    exit;
}

// Validate required fields
if (!isset($data['id']) || !isset($data['pagibig_number'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Employee ID and Pag-IBIG number are required'
    ]);
    exit;
}

if (!isset($data['last_name']) || !isset($data['first_name'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Last Name and First Name are required'
    ]);
    exit;
}

// Get database connection
$conn = getConnection();

try {
    $sql = "UPDATE employees SET 
            last_name = ?,
            first_name = ?,
            middle_name = ?,
            ee = ?,
            er = ?,
            tin = ?,
            birthdate = ?
            WHERE id = ? AND pagibig_number = ?";
            
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('Prepare statement failed: ' . $conn->error);
    }
    
    // Get the values
    $last_name = isset($data['last_name']) ? strtoupper($data['last_name']) : '';
    $first_name = isset($data['first_name']) ? strtoupper($data['first_name']) : '';
    $middle_name = isset($data['middle_name']) ? strtoupper($data['middle_name']) : '';
    $tin = isset($data['tin']) ? $data['tin'] : null;
    
    // Format TIN: ensure it's in XXX-XXX-XXX-0000 format
    if ($tin && !empty($tin)) {
        $tinDigits = preg_replace('/\D/', '', $tin);
        if (strlen($tinDigits) === 12) {
            $tin = substr($tinDigits, 0, 3) . '-' . 
                   substr($tinDigits, 3, 3) . '-' . 
                   substr($tinDigits, 6, 3) . '-' . 
                   substr($tinDigits, 9);
        } else {
            $tin = $tinDigits;
        }
    }
    
    $ee = isset($data['ee']) ? floatval($data['ee']) : 0.00;
    $er = isset($data['er']) ? floatval($data['er']) : 0.00;
    $birthdate = isset($data['birthdate']) ? $data['birthdate'] : null;
    $id = intval($data['id']);
    $pagibig_number = $data['pagibig_number'];
    
    // Bind parameters
    $stmt->bind_param('sssddssis', 
        $last_name,
        $first_name,
        $middle_name,
        $ee,
        $er,
        $tin,
        $birthdate,
        $id,
        $pagibig_number
    );

    // Execute the statement
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Employee updated successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No changes made or employee not found'
            ]);
        }
    } else {
        throw new Exception($stmt->error);
    }

    // Close the statement
    $stmt->close();
    
} catch(Exception $e) {
    error_log("Error in update_employee.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update employee: ' . $e->getMessage()
    ]);
}

// Close the connection
$conn->close();
?>
