-- Migration script to add custom fields functionality
-- Run this script to update your existing database

-- Add restrict_bulk_update column to status table
ALTER TABLE status ADD COLUMN restrict_bulk_update BOOLEAN DEFAULT FALSE;

-- Add is_default column to status table
ALTER TABLE status ADD COLUMN is_default BOOLEAN DEFAULT FALSE;

-- Add custom_fields_data column to contact_status_history table
ALTER TABLE contact_status_history ADD COLUMN custom_fields_data JSON DEFAULT NULL;

-- Create status_custom_fields table
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

-- Add index for faster searching
CREATE INDEX idx_status_custom_fields_status_id ON status_custom_fields(status_id);

-- Update existing status records to have restrict_bulk_update = FALSE
UPDATE status SET restrict_bulk_update = FALSE WHERE restrict_bulk_update IS NULL;

-- Set the first status (usually "New Lead") as default if no default is set
UPDATE status SET is_default = TRUE WHERE id = (SELECT id FROM status ORDER BY id ASC LIMIT 1) AND is_default IS NULL;
