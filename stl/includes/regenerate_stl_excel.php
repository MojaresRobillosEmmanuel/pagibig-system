<?php
// Start output buffering to prevent accidental output
ob_start();

session_start();

// Set JSON header immediately
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../database/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $month = isset($_POST['month']) ? $_POST['month'] : '';
    $year = isset($_POST['year']) ? intval($_POST['year']) : 0;
    
    if (!$month || !$year) {
        ob_end_clean();
        echo json_encode(['status' => 'error', 'message' => 'Missing month or year']);
        exit;
    }
    
    try {
        $conn = getConnection();
        
        // First, check if we have a saved record in the database for this month/year
        $checkQuery = "SELECT employee_data FROM stl_file_records WHERE month = ? AND year = ? LIMIT 1";
        $checkStmt = $conn->prepare($checkQuery);
        if (!$checkStmt) {
            ob_end_clean();
            echo json_encode(['status' => 'error', 'message' => 'Query preparation failed: ' . $conn->error]);
            exit;
        }
        
        $checkStmt->bind_param('si', $month, $year);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        $employeeData = [];
        
        if ($checkResult->num_rows > 0) {
            // Use saved employee data from database
            $record = $checkResult->fetch_assoc();
            $employeeData = json_decode($record['employee_data'], true);
            if (!$employeeData || !is_array($employeeData)) {
                ob_end_clean();
                echo json_encode(['status' => 'error', 'message' => 'Invalid employee data in database']);
                exit;
            }
        } else {
            // Get all active STL employees if no saved record exists
            $query = "
                SELECT 
                    id,
                    pagibig_no,
                    id_number,
                    ee,
                    er,
                    tin,
                    birthdate
                FROM selected_stl
                WHERE is_active = 1
                ORDER BY pagibig_no
            ";
            
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                ob_end_clean();
                echo json_encode(['status' => 'error', 'message' => 'Query preparation failed: ' . $conn->error]);
                exit;
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                ob_end_clean();
                echo json_encode(['status' => 'error', 'message' => 'No active STL records found in database']);
                exit;
            }
            
            while ($row = $result->fetch_assoc()) {
                $employeeData[] = $row;
            }
            $stmt->close();
        }
        $checkStmt->close();
        
        // Load PHPSpreadsheet
        require_once __DIR__ . '/../../vendor/autoload.php';
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();
        $worksheet->setTitle('STL Summary');
        
        // Set headers
        $headers = [
            'PAG-IBIG MID NO.',
            'EMPLOYEE NUMB',
            'EE',
            'ER',
            'TIN',
            'BIRTHDATE'
        ];
        
        foreach ($headers as $index => $header) {
            $worksheet->setCellValueByColumnAndRow($index + 1, 1, $header);
        }
        
        // Style header row
        $worksheet->getStyle('A1:F1')->getFont()->setBold(true);
        $worksheet->getStyle('A1:F1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $worksheet->getStyle('A1:F1')->getFill()->getStartColor()->setARGB('FFCCCCCC');
        
        // Add data rows
        $rowNum = 2;
        $totalEE = 0;
        $totalER = 0;
        $recordCount = 0;
        
        foreach ($employeeData as $row) {
            $worksheet->setCellValueByColumnAndRow(1, $rowNum, $row['pagibig_no']);
            $worksheet->setCellValueByColumnAndRow(2, $rowNum, $row['id_number']);
            $worksheet->setCellValueByColumnAndRow(3, $rowNum, floatval($row['ee']));
            $worksheet->setCellValueByColumnAndRow(4, $rowNum, floatval($row['er']));
            $worksheet->setCellValueByColumnAndRow(5, $rowNum, $row['tin']);
            $worksheet->setCellValueByColumnAndRow(6, $rowNum, $row['birthdate']);
            
            $totalEE += floatval($row['ee']);
            $totalER += floatval($row['er']);
            $recordCount++;
            
            $rowNum++;
        }
        
        // Add totals row
        $worksheet->setCellValueByColumnAndRow(1, $rowNum, 'TOTAL');
        $worksheet->getStyle('A' . $rowNum)->getFont()->setBold(true);
        $worksheet->setCellValueByColumnAndRow(3, $rowNum, $totalEE);
        $worksheet->setCellValueByColumnAndRow(4, $rowNum, $totalER);
        $worksheet->getStyle('A' . $rowNum . ':F' . $rowNum)->getFont()->setBold(true);
        $worksheet->getStyle('A' . $rowNum . ':F' . $rowNum)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $worksheet->getStyle('A' . $rowNum . ':F' . $rowNum)->getFill()->getStartColor()->setARGB('FFFFFFCC');
        
        // Auto-size columns
        foreach (range('A', 'F') as $col) {
            $worksheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Generate filename
        $filename = strtolower($month) . '_' . $year . '_stl.xlsx';
        
        // Save to folder
        $uploadDir = __DIR__ . '/../../generated excel files/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $filepath = $uploadDir . $filename;
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($filepath);
        
        // Get file size
        $fileSize = filesize($filepath);
        
        // Save or update the record in database
        $employeeDataJson = json_encode($employeeData);
        $userId = $_SESSION['user_id'];
        
        $upsertQuery = "
            INSERT INTO stl_file_records 
            (filename, month, year, num_borrowers, total_ee_deducted, total_er_deducted, employee_data, file_path, file_size, created_by, created_date)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
            num_borrowers = VALUES(num_borrowers),
            total_ee_deducted = VALUES(total_ee_deducted),
            total_er_deducted = VALUES(total_er_deducted),
            employee_data = VALUES(employee_data),
            file_path = VALUES(file_path),
            file_size = VALUES(file_size),
            created_by = VALUES(created_by),
            updated_date = NOW()
        ";
        
        $upsertStmt = $conn->prepare($upsertQuery);
        if (!$upsertStmt) {
            error_log("Database insert/update failed: " . $conn->error);
            // Continue anyway - Excel file was created successfully
        } else {
            $upsertStmt->bind_param(
                'ssiiiddsii',
                $filename,
                $month,
                $year,
                $recordCount,
                $totalEE,
                $totalER,
                $employeeDataJson,
                $filepath,
                $fileSize,
                $userId
            );
            $upsertStmt->execute();
            $upsertStmt->close();
        }
        
        echo json_encode([
            'status' => 'success', 
            'message' => 'Excel file generated successfully',
            'filename' => $filename,
            'filepath' => $filepath,
            'record_count' => $recordCount,
            'total_ee' => $totalEE
        ]);
        ob_end_flush();
        
    } catch (Exception $e) {
        error_log("Error regenerating STL Excel: " . $e->getMessage());
        ob_end_clean();
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    ob_end_clean();
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
