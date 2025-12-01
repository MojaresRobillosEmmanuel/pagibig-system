-- Create table to store STL file generation records with employee snapshots
CREATE TABLE IF NOT EXISTS `stl_file_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `month` varchar(20) NOT NULL,
  `year` int(4) NOT NULL,
  `num_borrowers` int(11) NOT NULL,
  `total_ee_deducted` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_er_deducted` decimal(10,2) NOT NULL DEFAULT 0.00,
  `employee_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'JSON array of employee records at time of file generation',
  `file_path` varchar(500) DEFAULT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_month_year` (`month`, `year`),
  KEY `idx_filename` (`filename`),
  KEY `idx_year` (`year`),
  KEY `idx_created_date` (`created_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
