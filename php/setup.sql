-- setup.sql
-- Consolidated Schema for Infinity 2k26
-- Removed Firebase user_id from users table; using auto-increment id as numeric User ID.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+05:30";

-- 1. Database Creation
CREATE DATABASE IF NOT EXISTS `infinity_db`;
USE `infinity_db`;

-- 2. Registrations Table
CREATE TABLE IF NOT EXISTS `registrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `team_id` varchar(50) NOT NULL,
  `event_name` varchar(255) NOT NULL,
  `roll_no` varchar(50) NOT NULL,
  `degree` varchar(100) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `teammate_name` varchar(255) DEFAULT NULL,
  `teammate_email` varchar(255) DEFAULT NULL,
  `teammate_roll_no` varchar(50) DEFAULT NULL,
  `teammate_phone` varchar(20) DEFAULT NULL,
  `firebase_doc_id` varchar(255) DEFAULT NULL,
  `publicity_member` varchar(255) DEFAULT NULL,
  `slot` varchar(50) DEFAULT NULL,
  `attendance_status` enum('pending','attended','not coming') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_team_event` (`team_id`, `event_name`),
  INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Users / Global Coin System Table
-- Using auto-increment 'id' as the numeric User ID.
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL UNIQUE,
  `roll_no` varchar(50) DEFAULT NULL,
  `teammate_user_id` int(11) DEFAULT NULL, -- Refers to id in this table
  `coins` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  INDEX `idx_email` (`email`),
  INDEX `idx_roll_no` (`roll_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Publicity Members Table (No changes)
CREATE TABLE IF NOT EXISTS `publicity_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL UNIQUE,
  `active` tinyint(4) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Alumni Registrations Table
CREATE TABLE IF NOT EXISTS `alumni_registrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `grad_year` int(11) NOT NULL,
  `firebase_doc_id` varchar(255) DEFAULT NULL,
  `slot` varchar(50) DEFAULT NULL,
  `attendance_status` enum('pending','attended','not coming') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;
