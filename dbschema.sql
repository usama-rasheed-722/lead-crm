-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 25, 2025 at 03:28 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `crm_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `contact_status_history`
--

CREATE TABLE `contact_status_history` (
  `id` int(11) NOT NULL,
  `lead_id` int(11) NOT NULL,
  `old_status_id` int(11) DEFAULT NULL,
  `new_status_id` int(11) DEFAULT NULL,
  `custom_fields_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields_data`)),
  `changed_by` int(11) NOT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
 
--
-- Table structure for table `leads`
--

CREATE TABLE `leads` (
  `id` int(11) NOT NULL,
  `lead_id` varchar(50) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `linkedin` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `clutch` varchar(255) DEFAULT NULL,
  `sdr_id` int(11) DEFAULT NULL,
  `duplicate_status` enum('unique','duplicate','incomplete') DEFAULT 'incomplete',
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `date` date DEFAULT NULL,
  `lead_owner` varchar(255) DEFAULT NULL,
  `contact_name` varchar(255) DEFAULT NULL,
  `job_title` varchar(255) DEFAULT NULL,
  `industry` varchar(255) DEFAULT NULL,
  `tier` varchar(50) DEFAULT NULL,
  `lead_status` varchar(100) DEFAULT NULL,
  `insta` varchar(255) DEFAULT NULL,
  `social_profile` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `description_information` text DEFAULT NULL,
  `whatsapp` varchar(100) DEFAULT NULL,
  `next_step` text DEFAULT NULL,
  `other` text DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `sdr_name` varchar(255) DEFAULT NULL,
  `lead_source_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

 
--
-- Table structure for table `leads_quota`
--

CREATE TABLE `leads_quota` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL,
  `quota_count` int(11) NOT NULL,
  `assigned_date` date NOT NULL DEFAULT curdate(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
 
--
-- Table structure for table `lead_notes`
--

CREATE TABLE `lead_notes` (
  `id` int(11) NOT NULL,
  `lead_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('call','email','update','note') DEFAULT 'note',
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lead_quota_assignments`
--

CREATE TABLE `lead_quota_assignments` (
  `id` int(11) NOT NULL,
  `leads_quota_id` int(11) NOT NULL,
  `lead_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lead_quota_assignments`
--

 

--
-- Table structure for table `lead_sources`
--

CREATE TABLE `lead_sources` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lead_sources`
--
 
-- --------------------------------------------------------

--
-- Table structure for table `quotas`
--

CREATE TABLE `quotas` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL,
  `quota_limit` int(11) NOT NULL DEFAULT 0,
  `days_limit` int(11) NOT NULL DEFAULT 30,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quotas`
--
 
 
--
-- Table structure for table `quota_logs`
--

CREATE TABLE `quota_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL,
  `quota_assigned` int(11) DEFAULT 0,
  `quota_used` int(11) DEFAULT 0,
  `quota_carry_forward` int(11) DEFAULT 0,
  `log_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

 

-- --------------------------------------------------------

--
-- Table structure for table `status`
--

CREATE TABLE `status` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `is_default` tinyint(4) NOT NULL,
  `restrict_bulk_update` tinyint(4) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `sequence` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

 

--
-- Table structure for table `status_custom_fields`
--

CREATE TABLE `status_custom_fields` (
  `id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL,
  `field_name` varchar(100) NOT NULL,
  `field_label` varchar(255) NOT NULL,
  `field_type` enum('text','textarea','select','date','number','email','url') NOT NULL DEFAULT 'text',
  `field_options` text DEFAULT NULL,
  `is_required` tinyint(1) DEFAULT 0,
  `field_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

 

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `sdr_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `role` enum('admin','sdr','manager') NOT NULL DEFAULT 'sdr',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

 
--

--
-- Indexes for table `contact_status_history`
--
ALTER TABLE `contact_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lead_id` (`lead_id`),
  ADD KEY `idx_old_status_id` (`old_status_id`),
  ADD KEY `idx_new_status_id` (`new_status_id`),
  ADD KEY `idx_user_status_date` (`changed_by`,`new_status_id`,`changed_at`),
  ADD KEY `idx_contact_history_old_status_id` (`old_status_id`),
  ADD KEY `idx_contact_history_new_status_id` (`new_status_id`),
  ADD KEY `idx_contact_history_changed_by` (`changed_by`),
  ADD KEY `idx_contact_history_changed_at` (`changed_at`);

--
-- Indexes for table `leads`
--
ALTER TABLE `leads`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lead_id` (`lead_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_leads_email` (`email`),
  ADD KEY `idx_leads_phone` (`phone`),
  ADD KEY `idx_leads_leadid` (`lead_id`),
  ADD KEY `idx_leads_sdr` (`sdr_id`);

--
-- Indexes for table `leads_quota`
--
ALTER TABLE `leads_quota`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_status_date` (`user_id`,`status_id`,`assigned_date`),
  ADD KEY `idx_leads_quota_user_date` (`user_id`,`assigned_date`),
  ADD KEY `idx_leads_quota_status` (`status_id`);

--
-- Indexes for table `lead_notes`
--
ALTER TABLE `lead_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lead_id` (`lead_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `lead_quota_assignments`
--
ALTER TABLE `lead_quota_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_quota_lead` (`leads_quota_id`,`lead_id`),
  ADD KEY `idx_lead_quota_assignments_quota` (`leads_quota_id`),
  ADD KEY `idx_lead_quota_assignments_lead` (`lead_id`),
  ADD KEY `idx_lead_quota_assignments_completed` (`completed_at`);

--
-- Indexes for table `lead_sources`
--
ALTER TABLE `lead_sources`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `quotas`
--
ALTER TABLE `quotas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_status` (`user_id`,`status_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status_id` (`status_id`);

--
-- Indexes for table `quota_logs`
--
ALTER TABLE `quota_logs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_status_date` (`user_id`,`status_id`,`log_date`),
  ADD KEY `idx_quota_logs_user_id` (`user_id`),
  ADD KEY `idx_quota_logs_status_id` (`status_id`),
  ADD KEY `idx_quota_logs_date` (`log_date`),
  ADD KEY `idx_quota_logs_user_date` (`user_id`,`log_date`);

--
-- Indexes for table `status`
--
ALTER TABLE `status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `status_custom_fields`
--
ALTER TABLE `status_custom_fields`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_status_field` (`status_id`,`field_name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `contact_status_history`
--
ALTER TABLE `contact_status_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `leads`
--
ALTER TABLE `leads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `leads_quota`
--
ALTER TABLE `leads_quota`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lead_notes`
--
ALTER TABLE `lead_notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lead_quota_assignments`
--
ALTER TABLE `lead_quota_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `lead_sources`
--
ALTER TABLE `lead_sources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `quotas`
--
ALTER TABLE `quotas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `quota_logs`
--
ALTER TABLE `quota_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `status`
--
ALTER TABLE `status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `status_custom_fields`
--
ALTER TABLE `status_custom_fields`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `contact_status_history`
--
ALTER TABLE `contact_status_history`
  ADD CONSTRAINT `contact_status_history_ibfk_1` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `contact_status_history_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_contact_history_new_status_id` FOREIGN KEY (`new_status_id`) REFERENCES `status` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_contact_history_old_status_id` FOREIGN KEY (`old_status_id`) REFERENCES `status` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_contact_status_history_new_status` FOREIGN KEY (`new_status_id`) REFERENCES `status` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_contact_status_history_old_status` FOREIGN KEY (`old_status_id`) REFERENCES `status` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `leads`
--
ALTER TABLE `leads`
  ADD CONSTRAINT `leads_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `leads_quota`
--
ALTER TABLE `leads_quota`
  ADD CONSTRAINT `leads_quota_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leads_quota_ibfk_2` FOREIGN KEY (`status_id`) REFERENCES `status` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lead_notes`
--
ALTER TABLE `lead_notes`
  ADD CONSTRAINT `lead_notes_ibfk_1` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lead_notes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lead_quota_assignments`
--
ALTER TABLE `lead_quota_assignments`
  ADD CONSTRAINT `lead_quota_assignments_ibfk_1` FOREIGN KEY (`leads_quota_id`) REFERENCES `leads_quota` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lead_quota_assignments_ibfk_2` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quotas`
--
ALTER TABLE `quotas`
  ADD CONSTRAINT `quotas_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quotas_ibfk_2` FOREIGN KEY (`status_id`) REFERENCES `status` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quota_logs`
--
ALTER TABLE `quota_logs`
  ADD CONSTRAINT `quota_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quota_logs_ibfk_2` FOREIGN KEY (`status_id`) REFERENCES `status` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `status_custom_fields`
--
ALTER TABLE `status_custom_fields`
  ADD CONSTRAINT `status_custom_fields_ibfk_1` FOREIGN KEY (`status_id`) REFERENCES `status` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
