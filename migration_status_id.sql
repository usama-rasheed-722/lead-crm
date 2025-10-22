-- Migration to add status_id field to leads table
-- This migration adds a status_id foreign key field and migrates existing data

USE crm_db;

-- Add status_id column to leads table
ALTER TABLE leads ADD COLUMN status_id INT DEFAULT NULL AFTER status;

-- Add foreign key constraint
ALTER TABLE leads ADD CONSTRAINT fk_leads_status_id FOREIGN KEY (status_id) REFERENCES status(id) ON DELETE SET NULL;

-- Migrate existing status data to status_id
-- This will match status names to status IDs and update the status_id field
UPDATE leads l 
JOIN status s ON l.status = s.name 
SET l.status_id = s.id 
WHERE l.status IS NOT NULL AND l.status != '';

-- Create index for better performance
CREATE INDEX idx_leads_status_id ON leads(status_id);

-- Note: After this migration, you should update your application code to use status_id instead of status
-- The status field can be kept for backward compatibility during transition
