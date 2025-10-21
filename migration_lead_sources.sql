-- Migration script to add lead source management functionality
-- Run this script to update your existing database

-- Create lead_sources table
CREATE TABLE IF NOT EXISTS lead_sources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Add lead_source_id column to leads table
ALTER TABLE leads ADD COLUMN lead_source_id INT DEFAULT NULL;

-- Add foreign key constraint for lead_source_id
ALTER TABLE leads ADD CONSTRAINT fk_leads_lead_source_id 
    FOREIGN KEY (lead_source_id) REFERENCES lead_sources(id) ON DELETE SET NULL;

-- Insert default lead sources
INSERT IGNORE INTO lead_sources (name, description) VALUES
('Website', 'Leads from company website'),
('Referral', 'Leads from referrals'),
('Social Media', 'Leads from social media platforms'),
('Email Campaign', 'Leads from email marketing campaigns'),
('Cold Outreach', 'Leads from cold calling or outreach'),
('Trade Show', 'Leads from trade shows and events'),
('Partner', 'Leads from business partners'),
('Other', 'Other lead sources');

-- Add indexes for better performance
CREATE INDEX IF NOT EXISTS idx_lead_sources_name ON lead_sources(name);
CREATE INDEX IF NOT EXISTS idx_lead_sources_active ON lead_sources(is_active);
CREATE INDEX IF NOT EXISTS idx_leads_lead_source_id ON leads(lead_source_id);

-- Update existing leads to map lead_source to lead_source_id
-- This will map existing lead_source values to the new lead_source_id
UPDATE leads l 
JOIN lead_sources ls ON l.lead_source = ls.name 
SET l.lead_source_id = ls.id 
WHERE l.lead_source IS NOT NULL AND l.lead_source != '';

-- Migration completed successfully
SELECT 'Lead source migration completed successfully!' as message;
