-- Migration: Add verification columns to leads table
-- Description: Adds boolean flags to track verification status of contact fields
-- Date: 2025-10-30

ALTER TABLE `leads` 
ADD COLUMN `email_verified` TINYINT(1) DEFAULT 0 AFTER `email`,
ADD COLUMN `phone_verified` TINYINT(1) DEFAULT 0 AFTER `phone`,
ADD COLUMN `whatsapp_verified` TINYINT(1) DEFAULT 0 AFTER `whatsapp`,
ADD COLUMN `linkedin_verified` TINYINT(1) DEFAULT 0 AFTER `linkedin`;

-- Add index for better query performance
CREATE INDEX `idx_email_verified` ON `leads` (`email_verified`);
CREATE INDEX `idx_phone_verified` ON `leads` (`phone_verified`);

-- Comment the columns for clarity
ALTER TABLE `leads` 
MODIFY COLUMN `email_verified` TINYINT(1) DEFAULT 0 COMMENT 'Email verification status: 0=unverified, 1=verified',
MODIFY COLUMN `phone_verified` TINYINT(1) DEFAULT 0 COMMENT 'Phone verification status: 0=unverified, 1=verified',
MODIFY COLUMN `whatsapp_verified` TINYINT(1) DEFAULT 0 COMMENT 'WhatsApp verification status: 0=unverified, 1=verified',
MODIFY COLUMN `linkedin_verified` TINYINT(1) DEFAULT 0 COMMENT 'LinkedIn verification status: 0=unverified, 1=verified';

