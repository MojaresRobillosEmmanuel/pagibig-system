-- Create stl_summary table
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
