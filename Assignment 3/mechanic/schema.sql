CREATE DATABASE IF NOT EXISTS `workshop_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `workshop_db`;


DROP TABLE IF EXISTS `appointments`;
DROP TABLE IF EXISTS `mechanics`;
DROP TABLE IF EXISTS `admin_users`;

CREATE TABLE `mechanics` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `specialty` VARCHAR(150) NOT NULL,
    `experience_years` INT NOT NULL DEFAULT 5,
    `phone` VARCHAR(30) NOT NULL DEFAULT '',
    `max_daily_slots` INT NOT NULL DEFAULT 4,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `appointments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `client_name` VARCHAR(100) NOT NULL,
    `address` TEXT NOT NULL,
    `phone` VARCHAR(30) NOT NULL,
    `car_license` VARCHAR(50) NOT NULL,
    `car_engine` VARCHAR(50) NOT NULL,
    `appointment_date` DATE NOT NULL,
    `mechanic_id` INT NOT NULL,
    `status` ENUM('Approved', 'Rescheduled', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Approved',
    `admin_notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_date_mech` (`appointment_date`, `mechanic_id`),
    INDEX `idx_client_date` (`phone`, `appointment_date`),
    INDEX `idx_car_date` (`car_license`, `appointment_date`),
    CONSTRAINT `fk_appointment_mechanic` FOREIGN KEY (`mechanic_id`) REFERENCES `mechanics` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `admin_users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `mechanics` (`id`, `name`, `specialty`, `experience_years`, `phone`, `max_daily_slots`) VALUES
(1, 'Sakib', 'Master Diagnostic & Automotive Electrical', 14, '+1 (555) 234-5671', 4),
(2, 'Rakib', 'Engine Overhaul & Transmission Specialist', 16, '+1 (555) 234-5672', 4),
(3, 'Akib', 'Brakes, Steering & Suspension Systems', 12, '+1 (555) 234-5673', 4),
(4, 'Mosarrof', 'Hybrid Systems & ECU Performance Tuning', 11, '+1 (555) 234-5674', 4),
(5, 'Jahid', 'Air Conditioning & General Precision Service', 15, '+1 (555) 234-5675', 4);

-- Seed Default Admin User: admin / admin123
INSERT INTO `admin_users` (`username`, `password_hash`, `full_name`) VALUES
('admin', '$2y$10$vG0rR5p0Z5wQ3n9p4h5kbe2g1Vb5O6r.TfXWqT6r1P3iQ7eK2A7Ce', 'Workshop Manager');
-- Note: password is 'admin123'

INSERT INTO `appointments` (`client_name`, `address`, `phone`, `car_license`, `car_engine`, `appointment_date`, `mechanic_id`, `status`) VALUES
('Sarah ', '742 Evergreen Terrace, Springfield', '01711223344', 'DHK-METRO-GA-1122', '13579246', CURDATE(), 1, 'Approved'),
('Mizan', '1088 Pine Crest Ave, Seattle', '01822334455', 'WA-789-XYZ', '98765432', CURDATE(), 1, 'Approved'),
('Ali', '45 Baker Street, Boston', '01933445566', 'MA-456-ABC', '56473829', CURDATE(), 2, 'Approved'),
('Maha', '12 Rose Valley, San Jose', '01544556677', 'CA-102-DEF', '23456789', CURDATE(), 3, 'Approved');
