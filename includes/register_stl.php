<?php
// Prevent any output buffering issues
if (ob_get_level()) ob_end_clean();

// Ensure we only output JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

require_once 'db_connect.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Ensure POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Get and validate input data
    $raw_input = file_get_contents('php://input');
    if (empty($raw_input)) {
        throw new Exception('No data received');
    }

    $data = json_decode($raw_input, true);
    if ($data === null) {
        throw new Exception('Invalid JSON data received');
    }

    // Validate required fields
    $required_fields = ['pagibig_number', 'id_number', 'last_name', 'first_name', 'loan_amount', 'loan_term'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            throw new Exception("Missing required field: " . str_replace('_', ' ', $field));
        }
    }

    // Clean and validate Pag-IBIG number
    $pagibig_number = preg_replace('/[^0-9]/', '', $data['pagibig_number']);
    if (strlen($pagibig_number) !== 12) {
        throw new Exception('Pag-IBIG number must be exactly 12 digits');
    }

    // Validate loan amount
    $loan_amount = floatval($data['loan_amount']);
    if ($loan_amount < 5000) {
        throw new Exception('Loan amount must be at least ₱5,000');
    }

    // Validate loan term
    $loan_term = intval($data['loan_term']);
    if (!in_array($loan_term, [6, 12, 24])) {
        throw new Exception('Loan term must be 6, 12, or 24 months');
    }

    // Start transaction
    $conn->beginTransaction();

    // Check if employee exists and get employee ID
    $stmt = $conn->prepare("SELECT id FROM employees WHERE pagibig_number = :pagibig AND status = 'active'");
    $stmt->execute([':pagibig' => $pagibig_number]);
    
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$employee) {
        throw new Exception('No active employee found with this Pag-IBIG number');
    }

    // Check for existing active loans
    $stmt = $conn->prepare("SELECT id FROM stl_loans WHERE pagibig_number = :pagibig AND status IN ('pending', 'approved')");
    $stmt->execute([':pagibig' => $pagibig_number]);
    
    if ($stmt->fetch()) {
        throw new Exception('Employee already has an active or pending loan application');
    }

    // Calculate monthly amortization (10.5% per annum interest rate)
    $monthly_interest = 0.105 / 12;
    $monthly_amortization = ($loan_amount * $monthly_interest * pow(1 + $monthly_interest, $loan_term)) / 
                           (pow(1 + $monthly_interest, $loan_term) - 1);

    // Insert loan application
    $sql = "INSERT INTO stl_loans (
        employee_id, pagibig_number, loan_amount, loan_term, 
        monthly_amortization, application_date, status
    ) VALUES (
        :employee_id, :pagibig, :amount, :term,
        :monthly, :app_date, 'pending'
    )";

    $stmt = $conn->prepare($sql);
    $success = $stmt->execute([
        ':employee_id' => $employee['id'],
        ':pagibig' => $pagibig_number,
        ':amount' => $loan_amount,
        ':term' => $loan_term,
        ':monthly' => $monthly_amortization,
        ':app_date' => date('Y-m-d')
    ]);

    if (!$success) {
        throw new Exception('Failed to submit loan application');
    }

    $loanId = $conn->lastInsertId();

    // Commit the transaction
    $conn->commit();

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Loan application submitted successfully',
        'loan' => [
            'id' => $loanId,
            'pagibig_number' => $pagibig_number,
            'loan_amount' => $loan_amount,
            'loan_term' => $loan_term,
            'monthly_amortization' => round($monthly_amortization, 2),
            'application_date' => date('Y-m-d'),
            'status' => 'pending'
        ]
    ]);

} catch (Exception $e) {
    // Rollback transaction if active
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }

    // Log the error
    error_log('Error in register_stl.php: ' . $e->getMessage());

    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
