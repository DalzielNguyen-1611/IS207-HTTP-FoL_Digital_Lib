-- ====================================================================
-- TECHCARE PLATFORM DATABASE SCHEMA (MYSQL / MARIADB)
-- Môn học: Phát Triển Ứng Dụng Web
-- Cơ sở dữ liệu chính: MySQL (Engine InnoDB, Chuẩn hóa 3NF, Khóa ngoại)
-- ====================================================================

-- 0. KHỞI TẠO CƠ SỞ DỮ LIỆU
CREATE DATABASE IF NOT EXISTS `techcare_db` 
    DEFAULT CHARACTER SET utf8mb4 
    COLLATE utf8mb4_unicode_ci;

USE `techcare_db`;

-- Xóa bảng theo thứ tự khóa ngoại nếu cần reset
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `wallet_transactions`;
DROP TABLE IF EXISTS `wallets`;
DROP TABLE IF EXISTS `booking_parts`;
DROP TABLE IF EXISTS `bookings`;
DROP TABLE IF EXISTS `technicians`;
DROP TABLE IF EXISTS `services`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. BẢNG NGƯỜI DÙNG & TÀI KHOẢN (USERS)
CREATE TABLE `users` (
    `id` VARCHAR(50) NOT NULL,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `role` ENUM('customer', 'technician', 'dispatcher', 'admin') NOT NULL DEFAULT 'customer',
    `phone` VARCHAR(20) DEFAULT NULL,
    `avatar_url` TEXT DEFAULT NULL,
    `status` ENUM('active', 'inactive', 'banned') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. BẢNG DANH MỤC DỊCH VỤ (SERVICES)
CREATE TABLE `services` (
    `id` VARCHAR(50) NOT NULL,
    `platform` ENUM('TechCare Desk', 'TechCare Home') NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    `category` VARCHAR(50) NOT NULL,
    `base_price` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `estimated_duration_minutes` INT DEFAULT 60,
    `description` TEXT DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. BẢNG HỒ SƠ KỸ THUẬT VIÊN HIỆN TRƯỜNG (TECHNICIANS)
CREATE TABLE `technicians` (
    `id` VARCHAR(50) NOT NULL,
    `code` VARCHAR(20) NOT NULL UNIQUE, -- Ví dụ: NV-204
    `user_id` VARCHAR(50) NOT NULL,
    `level` VARCHAR(30) DEFAULT 'Bậc 2', -- Bậc 1, Bậc 2, Bậc 3, Chuyên gia
    `specialty` VARCHAR(150) NOT NULL,
    `rating` DECIMAL(3, 2) DEFAULT 5.00,
    `review_count` INT DEFAULT 0,
    `sla_success_rate` DECIMAL(5, 2) DEFAULT 98.00,
    `shift_code` VARCHAR(20) DEFAULT '#A1',
    `shift_status` ENUM('ready', 'busy', 'offline') DEFAULT 'ready',
    `current_lat` DECIMAL(10, 8) DEFAULT NULL,
    `current_lng` DECIMAL(11, 8) DEFAULT NULL,
    `current_address` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_tech_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. BẢNG PHIẾU YÊU CẦU & ĐẶT LỊCH (BOOKINGS / TICKETS)
CREATE TABLE `bookings` (
    `id` VARCHAR(50) NOT NULL,
    `code` VARCHAR(30) NOT NULL UNIQUE, -- Ví dụ: TC-HOME-3895, TC-DESK-1029
    `platform` ENUM('TechCare Desk', 'TechCare Home') NOT NULL,
    `service_id` VARCHAR(50) DEFAULT NULL,
    `customer_id` VARCHAR(50) DEFAULT NULL,
    `customer_name` VARCHAR(100) NOT NULL,
    `customer_phone` VARCHAR(20) NOT NULL,
    `address` TEXT NOT NULL,
    `device_type` VARCHAR(100) NOT NULL,
    `issue_description` TEXT NOT NULL,
    `scheduled_time` VARCHAR(100) DEFAULT NULL,
    `priority` ENUM('low', 'normal', 'high', 'urgent') DEFAULT 'normal',
    `status` ENUM('pending', 'assigned', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    `assigned_technician_id` VARCHAR(50) DEFAULT NULL,
    `estimated_cost` DECIMAL(12, 2) DEFAULT 0.00,
    `actual_cost` DECIMAL(12, 2) DEFAULT NULL,
    `technical_note` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `completed_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_booking_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_booking_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_booking_tech` FOREIGN KEY (`assigned_technician_id`) REFERENCES `technicians` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. BẢNG BẢNG KÊ LINH KIỆN & PHỤ TÙNG CA SỬA CHỮA (BOOKING_PARTS)
CREATE TABLE `booking_parts` (
    `id` VARCHAR(50) NOT NULL,
    `booking_id` VARCHAR(50) NOT NULL,
    `part_name` VARCHAR(150) NOT NULL,
    `quantity` INT DEFAULT 1,
    `unit_price` DECIMAL(12, 2) NOT NULL,
    `total_price` DECIMAL(12, 2) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_parts_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. BẢNG VÍ ĐIỆN TỬ KỸ THUẬT VIÊN (WALLETS)
CREATE TABLE `wallets` (
    `id` VARCHAR(50) NOT NULL,
    `technician_id` VARCHAR(50) NOT NULL UNIQUE,
    `balance` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `pending_balance` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `bank_name` VARCHAR(100) DEFAULT NULL,
    `bank_account_number` VARCHAR(50) DEFAULT NULL,
    `bank_account_name` VARCHAR(100) DEFAULT NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_wallet_tech` FOREIGN KEY (`technician_id`) REFERENCES `technicians` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. BẢNG LỊCH SỬ GIAO DỊCH VÍ (WALLET_TRANSACTIONS)
CREATE TABLE `wallet_transactions` (
    `id` VARCHAR(50) NOT NULL,
    `wallet_id` VARCHAR(50) NOT NULL,
    `booking_id` VARCHAR(50) DEFAULT NULL,
    `type` ENUM('income', 'withdraw', 'penalty', 'bonus') NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `amount` DECIMAL(12, 2) NOT NULL,
    `status` ENUM('pending', 'success', 'failed') DEFAULT 'success',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_trans_wallet` FOREIGN KEY (`wallet_id`) REFERENCES `wallets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_trans_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. BẢNG ĐÁNH GIÁ DỊCH VỤ (REVIEWS)
CREATE TABLE `reviews` (
    `id` VARCHAR(50) NOT NULL,
    `booking_id` VARCHAR(50) NOT NULL UNIQUE,
    `technician_id` VARCHAR(50) NOT NULL,
    `customer_id` VARCHAR(50) DEFAULT NULL,
    `rating` TINYINT NOT NULL CHECK (`rating` >= 1 AND `rating` <= 5),
    `comment` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_review_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_review_tech` FOREIGN KEY (`technician_id`) REFERENCES `technicians` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_review_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
