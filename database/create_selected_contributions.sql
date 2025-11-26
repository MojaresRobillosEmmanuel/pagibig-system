USE pagibig_db;

-- Create selected_contributions table
CREATE TABLE IF NOT EXISTS selected_contributions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pagibig_no VARCHAR(50),
    id_number VARCHAR(50),
    user_id INT,
    date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status TINYINT(1) DEFAULT 1,
    FOREIGN KEY (pagibig_no) REFERENCES employees(pagibig_number) ON DELETE CASCADE,
    INDEX idx_pagibig_no (pagibig_no),
    INDEX idx_user_id (user_id)
);