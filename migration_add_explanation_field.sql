-- Migration: Add explanation field to leads_quota table
-- Date: 2025-01-27
-- Description: Adds an explanation/instructions field to the leads_quota table
--              to allow admins to provide specific instructions for quota assignments

-- Add explanation column to leads_quota table
ALTER TABLE `leads_quota` 
ADD COLUMN `explanation` TEXT NULL DEFAULT NULL 
COMMENT 'Instructions or explanation for the quota assignment' 
AFTER `quota_count`;

-- Update existing records to have NULL explanation (optional)
-- This is already handled by the DEFAULT NULL above

-- Add index for better performance when searching by explanation (optional)
-- ALTER TABLE `leads_quota` ADD INDEX `idx_explanation` (`explanation`(100));

-- Verify the column was added successfully
-- SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT, COLUMN_COMMENT 
-- FROM INFORMATION_SCHEMA.COLUMNS 
-- WHERE TABLE_SCHEMA = DATABASE() 
-- AND TABLE_NAME = 'leads_quota' 
-- AND COLUMN_NAME = 'explanation';
