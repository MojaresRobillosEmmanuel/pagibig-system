-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 27, 2025 at 01:58 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pagibig_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `contributions`
--

CREATE TABLE `contributions` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `contribution_date` date DEFAULT NULL,
  `ee_amount` decimal(10,2) DEFAULT NULL,
  `er_amount` decimal(10,2) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `contribution_period` varchar(7) DEFAULT NULL,
  `status` enum('pending','processed','cancelled') DEFAULT 'pending',
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contribution_history`
--

CREATE TABLE `contribution_history` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `transaction_date` datetime DEFAULT NULL,
  `action_type` enum('create','update','delete') DEFAULT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `system_type` varchar(50) DEFAULT 'contribution',
  `pagibig_number` varchar(50) DEFAULT NULL,
  `id_number` varchar(50) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `tin` varchar(50) DEFAULT NULL,
  `ee` decimal(10,2) DEFAULT NULL,
  `er` decimal(10,2) DEFAULT NULL,
  `birthdate` varchar(10) DEFAULT NULL COMMENT 'Birthdate in MM/DD/YYYY format',
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `system_type`, `pagibig_number`, `id_number`, `last_name`, `first_name`, `middle_name`, `tin`, `ee`, `er`, `birthdate`, `status`, `created_at`, `updated_at`) VALUES
(121, 'contribution', '255884946663', '01', 'JACOB', 'GFUSH', 'EW', '8738472130000', NULL, NULL, '10/20/2001', 0, '2025-11-26 02:24:26', '2025-11-26 04:47:09'),
(122, 'contribution', '454222878154', '08', 'JAYS', 'ON', 'T', '6431271720000', NULL, NULL, '07/29/2001', 0, '2025-11-26 02:32:09', '2025-11-26 04:47:04'),
(123, 'contribution', '559998989896', '09', 'DESCATIAR', 'DIVINE', 'R', '4455484880000', NULL, NULL, '12/20/2001', 1, '2025-11-26 03:00:58', '2025-11-26 03:00:58'),
(124, 'contribution', '258846468894', '05', 'VILLAMOR', 'PAULA', 'I', '7853477850000', NULL, NULL, '04/18/2001', 1, '2025-11-26 03:05:18', '2025-11-26 03:05:18'),
(125, 'contribution', '566884477721', '012', 'OLIVEROS', 'TINTIN', 'P', '873-231-213-000', NULL, NULL, '10/23/2000', 1, '2025-11-26 03:14:13', '2025-11-27 00:22:06'),
(126, 'contribution', '889477766211', '013', 'NICE', 'RICO', 'J', '7762631660000', NULL, NULL, '08/31/2001', 1, '2025-11-26 03:24:14', '2025-11-26 03:24:14'),
(127, 'contribution', '454577889999', '023', 'CASOCO', 'ANDREI', 'R', '757-437-828-000', NULL, NULL, '09/20/2000', 1, '2025-11-26 04:56:14', '2025-11-26 04:56:14'),
(129, 'stl', '458879899666', '0998', 'DUSTINE', 'JEI', 'R', '732-888-832-000', NULL, NULL, '03/03/1999', 1, '2025-11-26 05:09:22', '2025-11-26 09:06:29'),
(130, 'stl', '445888665632', '0332', 'REYMON', 'DELMUNDO', 'E', '482-637-462-000', NULL, NULL, '10/20/2000', 1, '2025-11-26 06:46:09', '2025-11-26 06:46:09'),
(131, 'contribution', '154448788874', '077', 'VILLAFLORES', 'RICAFORT', 'P', '545-788-771-000', 0.00, 0.00, '01/20/2001', 1, '2025-11-26 08:58:41', '2025-11-27 00:14:08'),
(132, 'stl', '154448875656', '78', 'AXEK', 'KURT', 'R', '827-837-482-000', NULL, NULL, '09/29/2000', 0, '2025-11-26 09:03:35', '2025-11-26 09:06:22'),
(133, 'stl', '878237841231', '99', 'JEIDEN', 'SMITH', 'R', '775-346-722-000', NULL, NULL, '03/20/2000', 1, '2025-11-27 00:40:25', '2025-11-27 00:40:25'),
(134, 'stl', '857471321232', '099', 'TERENCE', 'ROMEO', 'J', '723-471-929-000', NULL, NULL, '09/02/1970', 1, '2025-11-27 00:41:15', '2025-11-27 00:41:15'),
(135, 'stl', '756739823498', '988', 'ASI', 'TAO', 'L', '766-372-648-000', NULL, NULL, '07/24/1897', 1, '2025-11-27 00:42:07', '2025-11-27 00:42:07'),
(136, 'stl', '663672461231', '283', 'AGUILAR', 'JAPET', 'R', '376-476-276-000', NULL, NULL, '09/21/1956', 1, '2025-11-27 00:42:42', '2025-11-27 00:42:42'),
(137, 'stl', '723455215365', '877', 'TIU', 'CRIS', 'R', '351-562-536-000', NULL, NULL, '01/20/1978', 1, '2025-11-27 00:43:35', '2025-11-27 00:43:35'),
(138, 'stl', '786457637623', '8837', 'PINOY', 'SAKURAGI', 'G', '732-842-736-000', NULL, NULL, '02/02/2001', 1, '2025-11-27 00:44:55', '2025-11-27 00:44:55'),
(139, 'stl', '423423142232', '312', 'PINGRIS', 'MARK', 'G', '561-523-645-000', NULL, NULL, '09/20/1932', 1, '2025-11-27 00:45:50', '2025-11-27 00:45:50');

-- --------------------------------------------------------

--
-- Table structure for table `selected_contributions`
--

CREATE TABLE `selected_contributions` (
  `id` int(11) NOT NULL,
  `pagibig_no` varchar(50) DEFAULT NULL,
  `id_number` varchar(50) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `selected_contributions`
--

INSERT INTO `selected_contributions` (`id`, `pagibig_no`, `id_number`, `user_id`, `date_added`) VALUES
(27, '255884946663', '01', 1, '2025-11-26 02:59:50'),
(30, '454222878154', '08', 1, '2025-11-26 04:45:56'),
(31, '889477766211', '013', 1, '2025-11-26 04:45:56'),
(32, '566884477721', '012', 1, '2025-11-26 04:45:56'),
(35, '454577889999', '023', 1, '2025-11-26 09:00:01'),
(36, '559998989896', '09', 1, '2025-11-26 09:00:01'),
(37, '258846468894', '05', 1, '2025-11-26 09:00:01'),
(38, '154448788874', '077', 1, '2025-11-27 00:22:45');

-- --------------------------------------------------------

--
-- Table structure for table `selected_stl`
--

CREATE TABLE `selected_stl` (
  `id` int(11) NOT NULL,
  `pagibig_no` varchar(50) DEFAULT NULL,
  `id_number` varchar(50) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp(),
  `loan_amount` decimal(10,2) DEFAULT 0.00,
  `loan_status` enum('pending','approved','rejected','completed') DEFAULT 'pending',
  `ee` decimal(10,2) DEFAULT 0.00,
  `er` decimal(10,2) DEFAULT 0.00,
  `tin` varchar(20) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `selected_stl`
--

INSERT INTO `selected_stl` (`id`, `pagibig_no`, `id_number`, `user_id`, `date_added`, `loan_amount`, `loan_status`, `ee`, `er`, `tin`, `birthdate`, `is_active`) VALUES
(56, '458879899666', '0998', 1, '2025-11-26 09:18:52', 0.00, 'pending', 0.00, 200.00, '732-888-832-000', '0000-00-00', 1),
(57, '445888665632', '0332', 1, '2025-11-26 09:18:52', 0.00, 'pending', 0.00, 200.00, '482-637-462-000', '0000-00-00', 1),
(58, '663672461231', '283', 1, '2025-11-27 00:43:48', 0.00, 'pending', 0.00, 200.00, '376-476-276-000', '0000-00-00', 1),
(59, '723455215365', '877', 1, '2025-11-27 00:43:48', 0.00, 'pending', 0.00, 200.00, '351-562-536-000', '0000-00-00', 1),
(60, '756739823498', '988', 1, '2025-11-27 00:43:48', 0.00, 'pending', 0.00, 200.00, '766-372-648-000', '0000-00-00', 1),
(61, '878237841231', '99', 1, '2025-11-27 00:43:48', 0.00, 'pending', 0.00, 2000.00, '775-346-722-000', '0000-00-00', 1),
(62, '857471321232', '099', 1, '2025-11-27 00:43:48', 0.00, 'pending', 0.00, 200.00, '723-471-929-000', '0000-00-00', 1),
(63, '786457637623', '8837', 1, '2025-11-27 00:45:05', 0.00, 'pending', 0.00, 200.00, '732-842-736-000', '0000-00-00', 1),
(64, '423423142232', '312', 1, '2025-11-27 00:46:17', 0.00, 'pending', 0.00, 200.00, '561-523-645-000', '0000-00-00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `status` tinyint(1) DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`, `status`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$6nLXIa7UI3LStI67WsWquehj71xVV/mMgYzyXqscDSWIN/KYNa8gm', 'admin@pagibig.local', 'admin', 1, NULL, '2025-11-07 04:47:04', '2025-11-07 04:47:04');

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_employees`
-- (See below for the actual view)
--
CREATE TABLE `view_employees` (
`id` int(11)
,`system_type` varchar(50)
,`pagibig_number` varchar(50)
,`id_number` varchar(50)
,`last_name` varchar(100)
,`first_name` varchar(100)
,`middle_name` varchar(100)
,`tin` varchar(50)
,`birthdate` varchar(10)
,`ee` decimal(10,2)
,`er` decimal(10,2)
,`status` tinyint(1)
,`created_at` timestamp
,`updated_at` timestamp
);

-- --------------------------------------------------------

--
-- Structure for view `view_employees`
--
DROP TABLE IF EXISTS `view_employees`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_employees`  AS SELECT `employees`.`id` AS `id`, `employees`.`system_type` AS `system_type`, `employees`.`pagibig_number` AS `pagibig_number`, `employees`.`id_number` AS `id_number`, `employees`.`last_name` AS `last_name`, `employees`.`first_name` AS `first_name`, `employees`.`middle_name` AS `middle_name`, `employees`.`tin` AS `tin`, date_format(`employees`.`birthdate`,'%m/%d/%Y') AS `birthdate`, `employees`.`ee` AS `ee`, `employees`.`er` AS `er`, `employees`.`status` AS `status`, `employees`.`created_at` AS `created_at`, `employees`.`updated_at` AS `updated_at` FROM `employees` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `contributions`
--
ALTER TABLE `contributions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `idx_contribution_period` (`contribution_period`),
  ADD KEY `idx_contribution_date` (`contribution_date`);

--
-- Indexes for table `contribution_history`
--
ALTER TABLE `contribution_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `idx_transaction_date` (`transaction_date`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pagibig_number` (`pagibig_number`),
  ADD KEY `idx_id_number` (`id_number`),
  ADD KEY `pagibig_number` (`pagibig_number`);

--
-- Indexes for table `selected_contributions`
--
ALTER TABLE `selected_contributions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pagibig_no` (`pagibig_no`);

--
-- Indexes for table `selected_stl`
--
ALTER TABLE `selected_stl`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pagibig_no` (`pagibig_no`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `contributions`
--
ALTER TABLE `contributions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contribution_history`
--
ALTER TABLE `contribution_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=140;

--
-- AUTO_INCREMENT for table `selected_contributions`
--
ALTER TABLE `selected_contributions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `selected_stl`
--
ALTER TABLE `selected_stl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `contributions`
--
ALTER TABLE `contributions`
  ADD CONSTRAINT `contributions_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);

--
-- Constraints for table `contribution_history`
--
ALTER TABLE `contribution_history`
  ADD CONSTRAINT `contribution_history_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);

--
-- Constraints for table `selected_contributions`
--
ALTER TABLE `selected_contributions`
  ADD CONSTRAINT `selected_contributions_ibfk_1` FOREIGN KEY (`pagibig_no`) REFERENCES `employees` (`pagibig_number`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
