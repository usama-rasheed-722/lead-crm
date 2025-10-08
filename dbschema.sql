-- db_schema.sql
CREATE DATABASE IF NOT EXISTS crm_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE crm_db;


-- Users (Admin, SDR, Manager)
CREATE TABLE users (
id INT AUTO_INCREMENT PRIMARY KEY,
username VARCHAR(100) NOT NULL UNIQUE,
email VARCHAR(255) NOT NULL UNIQUE,
password VARCHAR(255) NOT NULL,
full_name VARCHAR(255),
role ENUM('admin','sdr','manager') NOT NULL DEFAULT 'sdr',
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- Leads
CREATE TABLE leads (
id INT AUTO_INCREMENT PRIMARY KEY,
lead_id VARCHAR(50) NOT NULL UNIQUE,
name VARCHAR(255) DEFAULT NULL,
company VARCHAR(255) DEFAULT NULL,
email VARCHAR(255) DEFAULT NULL,
phone VARCHAR(100) DEFAULT NULL,
linkedin VARCHAR(255) DEFAULT NULL,
website VARCHAR(255) DEFAULT NULL,
clutch VARCHAR(255) DEFAULT NULL,
sdr_id INT DEFAULT NULL,
duplicate_status ENUM('unique','duplicate','incomplete') DEFAULT 'incomplete',
notes TEXT DEFAULT NULL,
created_by INT DEFAULT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
FOREIGN KEY (sdr_id) REFERENCES users(id) ON DELETE SET NULL,
FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;


-- Activity / Lead Notes
CREATE TABLE lead_notes (
id INT AUTO_INCREMENT PRIMARY KEY,
lead_id INT NOT NULL,
user_id INT NOT NULL,
type ENUM('call','email','update','note') DEFAULT 'note',
content TEXT NOT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;


-- Indexes for faster searching
CREATE INDEX idx_leads_email ON leads(email);
CREATE INDEX idx_leads_phone ON leads(phone);
CREATE INDEX idx_leads_leadid ON leads(lead_id);
CREATE INDEX idx_leads_sdr ON leads(sdr_id);