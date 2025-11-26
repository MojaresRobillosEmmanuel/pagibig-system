-- Create the contribution database
CREATE DATABASE IF NOT EXISTS pagibig_contributions;
USE pagibig_contributions;

-- Create employees table (shared between both systems)
CREATE TABLE IF NOT EXISTS employees (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pagibig_number VARCHAR(50) UNIQUE,
    id_number VARCHAR(50),
    last_name VARCHAR(100),
    first_name VARCHAR(100),
    middle_name VARCHAR(100),
    tin VARCHAR(50),
    birthdate DATE,
    ee DECIMAL(10,2), -- Employee contribution
    er DECIMAL(10,2), -- Employer contribution
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_pagibig_number (pagibig_number),
    INDEX idx_id_number (id_number)
);

-- Create contributions table
CREATE TABLE IF NOT EXISTS contributions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT,
    contribution_date DATE,
    ee_amount DECIMAL(10,2), -- Employee contribution amount
    er_amount DECIMAL(10,2), -- Employer contribution amount
    total_amount DECIMAL(10,2),
    contribution_period VARCHAR(7), -- Format: YYYY-MM
    status ENUM('pending', 'processed', 'cancelled') DEFAULT 'pending',
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    INDEX idx_contribution_period (contribution_period),
    INDEX idx_contribution_date (contribution_date)
);

-- Create selected_contributions table
CREATE TABLE IF NOT EXISTS selected_contributions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pagibig_no VARCHAR(50),
    id_number VARCHAR(50),
    user_id INT,
    date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pagibig_no) REFERENCES employees(pagibig_number) ON DELETE CASCADE,
    INDEX idx_pagibig_no (pagibig_no)
);

-- Create contribution_history table
CREATE TABLE IF NOT EXISTS contribution_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id INT,
    transaction_date DATETIME,
    action_type ENUM('create', 'update', 'delete'),
    details JSON,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    INDEX idx_transaction_date (transaction_date)
);

-- Create users table for contribution system
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255),
    email VARCHAR(100) UNIQUE,
    role ENUM('admin', 'user') DEFAULT 'user',
    status TINYINT(1) DEFAULT 1,
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
);