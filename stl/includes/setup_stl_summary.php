<?php
session_start();

require_once __DIR__ . '/../../database/db_connect.php';

try {
    $conn = getConnection();
    
    // First, create the table if it doesn't exist
    $createTableSQL = "
        CREATE TABLE IF NOT EXISTS `stl_summary` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `filename` VARCHAR(255) NOT NULL,
          `month` VARCHAR(20) NOT NULL,
          `year` INT NOT NULL,
          `num_borrowers` INT NOT NULL DEFAULT 0,
          `total_deducted_amount` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
          `created_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          `updated_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          UNIQUE KEY `unique_month_year` (`month`, `year`),
          INDEX `idx_year_month` (`year`, `month`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ";
    
    if ($conn->query($createTableSQL)) {
        echo "✅ Table `stl_summary` created successfully<br>";
    } else {
        echo "✓ Table `stl_summary` already exists<br>";
    }
    
    // Now populate from existing Excel files
    require_once __DIR__ . '/../../vendor/autoload.php';
    
    $excelDir = __DIR__ . '/../../generated excel files/';
    
    if (!is_dir($excelDir)) {
        die("❌ Excel folder not found!");
    }
    
    $files = scandir($excelDir);
    $importedCount = 0;
    
    foreach ($files as $file) {
        // Look for STL files only
        if (stripos($file, '_stl') !== false && (substr($file, -5) === '.xlsx' || substr($file, -4) === '.xls')) {
            $filePath = $excelDir . $file;
            
            // Parse filename
            preg_match('/^([a-z]+)_(\d{4})_stl/i', $file, $matches);
            
            if (count($matches) === 3) {
                $monthName = ucfirst(strtolower($matches[1]));
                $year = $matches[2];
                
                try {
                    // Load Excel file
                    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
                    $worksheet = $spreadsheet->getActiveSheet();
                    $rows = $worksheet->toArray();
                    
                    // Count borrowers (skip header)
                    $borrowerCount = count($rows) - 1;
                    $totalDeducted = 0;
                    
                    // Sum EE column (index 5 = column F)
                    for ($i = 1; $i < count($rows); $i++) {
                        if (isset($rows[$i][5])) {
                            $amount = str_replace(['₱', ',', ' '], '', $rows[$i][5]);
                            $totalDeducted += floatval($amount);
                        }
                    }
                    
                    // Insert or update the record
                    $insertSQL = "
                        INSERT INTO stl_summary (filename, month, year, num_borrowers, total_deducted_amount)
                        VALUES (?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE
                            filename = VALUES(filename),
                            num_borrowers = VALUES(num_borrowers),
                            total_deducted_amount = VALUES(total_deducted_amount)
                    ";
                    
                    $stmt = $conn->prepare($insertSQL);
                    $stmt->bind_param('ssidd', $file, $monthName, $year, $borrowerCount, $totalDeducted);
                    $stmt->execute();
                    
                    echo "✅ Imported: $monthName $year - $borrowerCount borrowers, ₱" . number_format($totalDeducted, 2) . "<br>";
                    $importedCount++;
                    
                    $stmt->close();
                    
                } catch (Exception $e) {
                    echo "❌ Error reading $file: " . $e->getMessage() . "<br>";
                }
            }
        }
    }
    
    if ($importedCount === 0) {
        echo "⚠️ No STL files found in the excel folder<br>";
    } else {
        echo "<br>✅ Successfully imported $importedCount STL summary records!<br>";
    }
    
    // Show all records
    echo "<br><strong>Current records in stl_summary:</strong><br>";
    $result = $conn->query("SELECT month, year, num_borrowers, total_deducted_amount FROM stl_summary ORDER BY year DESC, FIELD(month, 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December') DESC");
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "- " . $row['month'] . " " . $row['year'] . ": " . $row['num_borrowers'] . " borrowers, ₱" . number_format($row['total_deducted_amount'], 2) . "<br>";
        }
    } else {
        echo "No records found<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
