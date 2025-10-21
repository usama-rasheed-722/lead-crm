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
    lead_owner VARCHAR(255) DEFAULT NULL,
    contact_name VARCHAR(255) DEFAULT NULL,
    job_title VARCHAR(255) DEFAULT NULL,
    industry VARCHAR(255) DEFAULT NULL,
    lead_source VARCHAR(255) DEFAULT NULL,
    lead_source_id INT DEFAULT NULL,
    tier VARCHAR(50) DEFAULT NULL,
    lead_status VARCHAR(100) DEFAULT NULL,
    insta VARCHAR(255) DEFAULT NULL,
    social_profile VARCHAR(255) DEFAULT NULL,
    address VARCHAR(255) DEFAULT NULL,
    description_information TEXT DEFAULT NULL,
    whatsapp VARCHAR(100) DEFAULT NULL,
    next_step TEXT DEFAULT NULL,
    other TEXT DEFAULT NULL,
    status VARCHAR(100) DEFAULT NULL,
    country VARCHAR(100) DEFAULT NULL,
    sdr_name VARCHAR(255) DEFAULT NULL,
 
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (lead_source_id) REFERENCES lead_sources(id) ON DELETE SET NULL
) ENGINE=InnoDB;


-- Activity / Lead Notes
CREATE TABLE lead_notes (
id INT AUTO_INCREMENT PRIMARY KEY,
lead_id INT NOT NULL,
user_id INT NOT NULL,
type ENUM('call','email','update','note') DEFAULT 'note',
content TEXT NOT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE
) ENGINE=InnoDB;


-- Status management table
CREATE TABLE status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    restrict_bulk_update BOOLEAN DEFAULT FALSE,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Lead source management table
CREATE TABLE lead_sources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Contact status history table
CREATE TABLE contact_status_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lead_id INT NOT NULL,
    old_status VARCHAR(100) DEFAULT NULL,
    new_status VARCHAR(100) NOT NULL,
    changed_by INT NOT NULL,
    custom_fields_data JSON DEFAULT NULL,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Custom status fields table
CREATE TABLE status_custom_fields (
    id INT AUTO_INCREMENT PRIMARY KEY,
    status_id INT NOT NULL,
    field_name VARCHAR(100) NOT NULL,
    field_label VARCHAR(255) NOT NULL,
    field_type ENUM('text', 'textarea', 'select', 'date', 'number', 'email', 'url') NOT NULL DEFAULT 'text',
    field_options TEXT DEFAULT NULL,
    is_required BOOLEAN DEFAULT FALSE,
    field_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (status_id) REFERENCES status(id) ON DELETE CASCADE,
    UNIQUE KEY unique_status_field (status_id, field_name)
) ENGINE=InnoDB;

-- Insert default statuses
INSERT INTO status (name, is_default) VALUES
('New Lead', TRUE),
('Email Contact', FALSE),
('Responded', FALSE),
('Qualified', FALSE),
('Unqualified', FALSE),
('Converted', FALSE),
('Lost', FALSE);

-- Insert default lead sources
INSERT INTO lead_sources (name, description) VALUES
('Website', 'Leads from company website'),
('Referral', 'Leads from referrals'),
('Social Media', 'Leads from social media platforms'),
('Email Campaign', 'Leads from email marketing campaigns'),
('Cold Outreach', 'Leads from cold calling or outreach'),
('Trade Show', 'Leads from trade shows and events'),
('Partner', 'Leads from business partners'),
('Other', 'Other lead sources');

-- Indexes for faster searching
CREATE INDEX idx_leads_email ON leads(email);
CREATE INDEX idx_leads_phone ON leads(phone);
CREATE INDEX idx_leads_leadid ON leads(lead_id);
CREATE INDEX idx_leads_sdr ON leads(sdr_id);
CREATE INDEX idx_contact_status_history_lead_id ON contact_status_history(lead_id);
CREATE INDEX idx_contact_status_history_changed_by ON contact_status_history(changed_by);
CREATE INDEX idx_contact_status_history_changed_at ON contact_status_history(changed_at);
CREATE INDEX idx_status_custom_fields_status_id ON status_custom_fields(status_id);
CREATE INDEX idx_lead_sources_name ON lead_sources(name);
CREATE INDEX idx_lead_sources_active ON lead_sources(is_active);
CREATE INDEX idx_leads_lead_source_id ON leads(lead_source_id);