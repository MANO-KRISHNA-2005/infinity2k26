-- Infinity 2k26 Database Setup & Update Script
-- Can be run on new or existing installations

CREATE DATABASE IF NOT EXISTS infinity_db;
USE infinity_db;

-- 1. Registrations Table
CREATE TABLE IF NOT EXISTS registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    team_id VARCHAR(50),
    event_name VARCHAR(255),
    user_id VARCHAR(255) NOT NULL,
    events TEXT NOT NULL,
    roll_no VARCHAR(50) NOT NULL,
    degree VARCHAR(50) NOT NULL,
    year INT NOT NULL,
    department VARCHAR(100) NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    teammate_name VARCHAR(255),
    teammate_email VARCHAR(255),
    teammate_roll_no VARCHAR(50),
    teammate_phone VARCHAR(20),
    firebase_doc_id VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Safely add 'publicity_member' column if it doesn't exist
SET @dbname = DATABASE();
SET @tablename = "registrations";
SET @columnname = "publicity_member";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE registrations ADD COLUMN publicity_member VARCHAR(100) AFTER user_id;"
));
PREPARE stmt FROM @preparedStatement;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;


-- 2. Alumni Registrations Table
CREATE TABLE IF NOT EXISTS alumni_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    grad_year INT NOT NULL,
    firebase_doc_id VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Publicity Members Table
CREATE TABLE IF NOT EXISTS publicity_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    active TINYINT DEFAULT 1
);

SELECT "Database setup/update completed successfully." as status;
