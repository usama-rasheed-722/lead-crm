<?php
/**
 * Test script to verify the custom fields migration
 * Run this after applying the migration to ensure everything is working
 */

require_once __DIR__ . '/app/config.php';
require_once __DIR__ . '/core/Database.php';

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    echo "Testing Custom Fields Migration...\n\n";
    
    // Test 1: Check if restrict_bulk_update column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM status LIKE 'restrict_bulk_update'");
    if ($stmt->rowCount() > 0) {
        echo "✓ restrict_bulk_update column exists in status table\n";
    } else {
        echo "✗ restrict_bulk_update column missing in status table\n";
    }
    
    // Test 1b: Check if is_default column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM status LIKE 'is_default'");
    if ($stmt->rowCount() > 0) {
        echo "✓ is_default column exists in status table\n";
    } else {
        echo "✗ is_default column missing in status table\n";
    }
    
    // Test 2: Check if custom_fields_data column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM contact_status_history LIKE 'custom_fields_data'");
    if ($stmt->rowCount() > 0) {
        echo "✓ custom_fields_data column exists in contact_status_history table\n";
    } else {
        echo "✗ custom_fields_data column missing in contact_status_history table\n";
    }
    
    // Test 3: Check if status_custom_fields table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'status_custom_fields'");
    if ($stmt->rowCount() > 0) {
        echo "✓ status_custom_fields table exists\n";
    } else {
        echo "✗ status_custom_fields table missing\n";
    }
    
    // Test 4: Check table structure
    $stmt = $pdo->query("DESCRIBE status_custom_fields");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $expectedColumns = ['id', 'status_id', 'field_name', 'field_label', 'field_type', 'field_options', 'is_required', 'field_order', 'created_at'];
    
    $missingColumns = array_diff($expectedColumns, $columns);
    if (empty($missingColumns)) {
        echo "✓ status_custom_fields table has all required columns\n";
    } else {
        echo "✗ status_custom_fields table missing columns: " . implode(', ', $missingColumns) . "\n";
    }
    
    // Test 5: Check foreign key constraint
    $stmt = $pdo->query("
        SELECT CONSTRAINT_NAME 
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE TABLE_NAME = 'status_custom_fields' 
        AND COLUMN_NAME = 'status_id' 
        AND REFERENCED_TABLE_NAME = 'status'
    ");
    if ($stmt->rowCount() > 0) {
        echo "✓ Foreign key constraint exists for status_id\n";
    } else {
        echo "✗ Foreign key constraint missing for status_id\n";
    }
    
    // Test 6: Check unique constraint
    $stmt = $pdo->query("
        SELECT CONSTRAINT_NAME 
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE TABLE_NAME = 'status_custom_fields' 
        AND CONSTRAINT_NAME = 'unique_status_field'
    ");
    if ($stmt->rowCount() > 0) {
        echo "✓ Unique constraint exists for status_id + field_name\n";
    } else {
        echo "✗ Unique constraint missing for status_id + field_name\n";
    }
    
    // Test 7: Test inserting a sample custom field
    try {
        // Get first status
        $stmt = $pdo->query("SELECT id FROM status LIMIT 1");
        $status = $stmt->fetch();
        
        if ($status) {
            $stmt = $pdo->prepare("
                INSERT INTO status_custom_fields 
                (status_id, field_name, field_label, field_type, is_required, field_order) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $result = $stmt->execute([
                $status['id'], 
                'test_field', 
                'Test Field', 
                'text', 
                0, 
                0
            ]);
            
            if ($result) {
                echo "✓ Can insert custom field record\n";
                
                // Clean up test record
                $pdo->prepare("DELETE FROM status_custom_fields WHERE field_name = 'test_field'")->execute();
            } else {
                echo "✗ Cannot insert custom field record\n";
            }
        } else {
            echo "⚠ No status records found to test with\n";
        }
    } catch (Exception $e) {
        echo "✗ Error testing custom field insertion: " . $e->getMessage() . "\n";
    }
    
    // Test 8: Check if default status is set
    $stmt = $pdo->query("SELECT COUNT(*) FROM status WHERE is_default = 1");
    $defaultCount = $stmt->fetchColumn();
    if ($defaultCount > 0) {
        echo "✓ Default status is set (" . $defaultCount . " default status(es))\n";
    } else {
        echo "⚠ No default status is set\n";
    }
    
    echo "\nMigration test completed!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
