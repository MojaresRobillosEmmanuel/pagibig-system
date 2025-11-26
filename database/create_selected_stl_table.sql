-- Create selected_stl table in pagibig_db if it doesn't exist

CREATE TABLE IF NOT EXISTS pagibig_db.selected_stl (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pagibig_no VARCHAR(50) NOT NULL UNIQUE,
    id_number VARCHAR(50),
    user_id INT,
    last_name VARCHAR(100),
    first_name VARCHAR(100),
    middle_name VARCHAR(100),
    tin VARCHAR(20),
    birthdate DATE,
    ee DECIMAL(10,2) DEFAULT 0,
    er DECIMAL(10,2) DEFAULT 0,
    date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    loan_amount DECIMAL(10,2) DEFAULT 0,
    loan_status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
    is_active TINYINT(1) DEFAULT 1,
    INDEX idx_pagibig_no (pagibig_no),
    INDEX idx_user_id (user_id),
    INDEX idx_date_added (date_added),
    FOREIGN KEY (pagibig_no) REFERENCES pagibig_db.employees(pagibig_number) ON DELETE CASCADE
);
