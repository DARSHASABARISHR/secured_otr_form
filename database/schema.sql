CREATE DATABASE IF NOT EXISTS secured_otr_updated CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE secured_otr_updated;
CREATE TABLE IF NOT EXISTS users (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 registration_number VARCHAR(24) NOT NULL UNIQUE,
 name VARCHAR(120) NOT NULL,
 email VARCHAR(190) NOT NULL UNIQUE,
 phone VARCHAR(10) NOT NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS otp_verifications (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 purpose ENUM('registration','forgot_password') NOT NULL,
 channel ENUM('email','phone') NOT NULL,
 target VARCHAR(190) NOT NULL,
 otp_hash VARCHAR(255) NOT NULL,
 expires_at DATETIME NOT NULL,
 resend_available_at DATETIME NOT NULL,
 attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
 verified_at DATETIME NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_otp_target (purpose,channel,target,id)
) ENGINE=InnoDB;
