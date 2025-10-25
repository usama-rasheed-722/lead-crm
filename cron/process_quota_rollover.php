<?php
/**
 * Daily Quota Rollover Processing Script
 * 
 * This script should be run daily via cron job to automatically process
 * incomplete quotas from the previous day and roll them over to the current day.
 * 
 * Cron job example (runs daily at 1:00 AM):
 * 0 1 * * * /usr/bin/php /path/to/LeadManager/cron/process_quota_rollover.php
 */

// Set the path to the application root
$appRoot = dirname(__DIR__);

// Include the application initialization
require_once $appRoot . '/app/init.php';

// Set timezone
date_default_timezone_set('UTC');

// Log function
function logMessage($message) {
    $timestamp = date('Y-m-d H:i:s');
    $logFile = dirname(__FILE__) . '/quota_rollover.log';
    $logEntry = "[$timestamp] $message" . PHP_EOL;
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    echo $logEntry;
}

try {
    logMessage("Starting daily quota rollover process");
    
    // Initialize the quota model
    $quotaModel = new LeadsQuotaModel();
    
    // Process rollover for today (rolls over from yesterday)
    $today = date('Y-m-d');
    $rolloverCount = $quotaModel->processDailyRollover($today);
    
    logMessage("Rollover process completed. {$rolloverCount} quotas processed.");
    
    // Optional: Send notification email to admin
    if ($rolloverCount > 0) {
        // You can add email notification logic here
        logMessage("Notification: {$rolloverCount} quotas were rolled over and may require attention.");
    }
    
    logMessage("Daily quota rollover process finished successfully");
    
} catch (Exception $e) {
    $errorMessage = "Error in quota rollover process: " . $e->getMessage();
    logMessage($errorMessage);
    
    // Optional: Send error notification to admin
    // You can add error notification logic here
    
    exit(1); // Exit with error code for cron monitoring
}

exit(0); // Exit successfully
?>
