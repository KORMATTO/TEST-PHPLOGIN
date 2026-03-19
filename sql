-- Run this FIRST in phpMyAdmin
CREATE DATABASE IF NOT EXISTS `naruto_game` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `naruto_game`;

-- NUCLEAR USERS TABLE
DROP TABLE IF EXISTS `password_resets`, `users`, `login_attempts`;
CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(16) NOT NULL UNIQUE KEY,
  `email` VARCHAR(100) NOT NULL UNIQUE KEY,
  `password` VARCHAR(255) NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent_hash` CHAR(64) NOT NULL,
  `failed_attempts` TINYINT UNSIGNED DEFAULT 0,
  `lockout_until` TIMESTAMP NULL DEFAULT NULL,
  `last_login` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_lockout` (`lockout_until`)
) ENGINE=InnoDB;

CREATE TABLE `password_resets` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `token_hash` CHAR(64) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `used` TINYINT(1) DEFAULT 0,
  `ip_address` VARCHAR(45) NOT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `token_unique` (`token_hash`),
  INDEX `idx_expires` (`expires_at`)
) ENGINE=InnoDB;

CREATE TABLE `login_attempts` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `ip_address` VARCHAR(45) NOT NULL,
  `identifier` VARCHAR(100) NOT NULL,
  `attempts` TINYINT UNSIGNED DEFAULT 1,
  `last_attempt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_ip` (`ip_address`),
  INDEX `idx_time` (`last_attempt`)
) ENGINE=InnoDB;
