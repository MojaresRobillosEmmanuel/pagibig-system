<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Check for authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required']);
    exit;
}

try {
    // Check if required parameters are present
    if (!isset($_GET['month']) || !isset($_GET['year'])) {
        throw new Exception('Missing month or year parameter.');
    }

    $month = $_GET['month'];
    $year = $_GET['year'];

    // Include database connection
    require_once __DIR__ . '/../database/db_connect.php';

    // Check if we have a connection
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception('Database connection failed: ' . ($conn->connect_error ?? 'Unknown error'));
    }

    // First, let's verify if the table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'contributions'");
    if ($tableCheck->num_rows === 0) {
        throw new Exception('Contributions table does not exist in the database.');
    }

    // Get the table structure
    $tableStructure = $conn->query("DESCRIBE contributions");
    if (!$tableStructure) {
        throw new Exception('Could not get table structure: ' . $conn->error);
    }

    // Sanitize inputs
    $month = $conn->real_escape_string($month);
    $year = $conn->real_escape_string($year);

    // Build and execute the query
    $query = "SELECT 
        sc.pagibig_no,
        sc.id_no,
        sc.last_name,
        sc.first_name,
        sc.middle_name,
        sc.ee,
        sc.er,
        sc.tin,
        sc.birthdate
    FROM selected_contributions sc 
    WHERE sc.user_id = " . $_SESSION['user_id'];

    error_log("Executing query: " . $query); // Log the query for debugging

    $result = $conn->query($query);
    if (!$result) {
        throw new Exception('Query failed: ' . $conn->error);
    }

    // Fetch and format the results
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'Pag-IBIG #' => $row['pagibig_no'],
            'ID #' => $row['id_no'],
            'Last Name' => $row['last_name'],
            'First Name' => $row['first_name'],
            'Middle Name' => $row['middle_name'],
            'EE' => $row['ee'],
            'ER' => $row['er'],
            'TIN' => $row['tin'],
            'Birthdate' => $row['birthdate']
        ];
    }

    if (empty($data)) {
        echo json_encode([
            'error' => 'No data found for the selected month and year.',
            'month' => $month,
            'year' => $year
        ]);
    } else {
        echo json_encode($data);
    }

} catch (Exception $e) {
    error_log("Error in get_contributions.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'details' => [
            'file' => basename(__FILE__),
            'month' => $_GET['month'] ?? null,
            'year' => $_GET['year'] ?? null
        ]
    ]);
}
?>
