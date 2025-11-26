-- Create the main database if it doesn't exist
CREATE DATABASE IF NOT EXISTS pagibig_db;
USE pagibig_db;

-- Create employees table
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