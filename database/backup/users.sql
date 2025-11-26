-- Drop existing table if it exists
DROP TABLE IF EXISTS `users`;

-- Create users table with proper structure
CREATE TABLE `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `id_number` varchar(50) NOT NULL,
    `last_name` varchar(100) NOT NULL,
    `first_name` varchar(100) NOT NULL,
    `middle_name` varchar(100) DEFAULT NULL,
    `email` varchar(100) NOT NULL,
    `username` varchar(50) NOT NULL,
    `password` varchar(255) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
