-- Add deleted_at column to leads table for soft delete functionality
-- This migration should be run on the database

ALTER TABLE `leads` ADD COLUMN `deleted_at` timestamp NULL AFTER `updated_at`;

-- Add index for better query performance
CREATE INDEX idx_deleted_at ON leads(deleted_at);
