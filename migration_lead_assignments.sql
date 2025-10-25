-- Migration: Lead Assignment System
-- Date: 2025-01-27
-- Description: Add lead assignment functionality with history tracking

-- Add assigned_to column to leads table
ALTER TABLE `leads` ADD COLUMN `assigned_to` int(11) NULL AFTER `sdr_id`;
ALTER TABLE `leads` ADD COLUMN `assigned_by` int(11) NULL AFTER `assigned_to`;
ALTER TABLE `leads` ADD COLUMN `assigned_at` timestamp NULL AFTER `assigned_by`;
ALTER TABLE `leads` ADD COLUMN `assignment_comment` text NULL AFTER `assigned_at`;

-- Create lead_assignments table for assignment history
CREATE TABLE `lead_assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lead_id` int(11) NOT NULL,
  `assigned_to` int(11) NOT NULL,
  `assigned_by` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `comment` text NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_lead_id` (`lead_id`),
  KEY `idx_assigned_to` (`assigned_to`),
  KEY `idx_assigned_by` (`assigned_by`),
  KEY `idx_assigned_at` (`assigned_at`),
  KEY `idx_is_active` (`is_active`),
  CONSTRAINT `lead_assignments_ibfk_1` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lead_assignments_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lead_assignments_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add indexes for better performance
ALTER TABLE `leads` ADD KEY `idx_assigned_to` (`assigned_to`);
ALTER TABLE `leads` ADD KEY `idx_assigned_by` (`assigned_by`);
ALTER TABLE `leads` ADD KEY `idx_assigned_at` (`assigned_at`);
