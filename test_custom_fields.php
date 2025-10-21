<?php
/**
 * Test script to verify custom fields functionality
 * This script tests the AJAX endpoint and custom field retrieval
 */

require_once 'app/init.php';

// Test the AJAX endpoint
function testCustomFieldsEndpoint() {
    echo "Testing Custom Fields AJAX Endpoint...\n\n";
    
    // Simulate GET request
    $_GET['action'] = 'get_custom_fields_for_status';
    $_GET['status'] = 'Qualified'; // Assuming this status has custom fields
    
    // Capture output
    ob_start();
    
    try {
        // Include the main index.php to test the routing
        include 'index.php';
        $output = ob_get_clean();
        
        // Check if we got JSON response
        $data = json_decode($output, true);
        if ($data && isset($data['customFields'])) {
            echo "✓ AJAX endpoint working correctly\n";
            echo "✓ Custom fields retrieved: " . count($data['customFields']) . " fields\n";
            
            foreach ($data['customFields'] as $field) {
                echo "  - {$field['field_label']} ({$field['field_type']})" . 
                     ($field['is_required'] ? ' [Required]' : '') . "\n";
            }
        } else {
            echo "✗ AJAX endpoint not returning expected JSON format\n";
            echo "Output: " . $output . "\n";
        }
    } catch (Exception $e) {
        ob_end_clean();
        echo "✗ Error testing AJAX endpoint: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

// Test StatusModel methods
function testStatusModel() {
    echo "Testing StatusModel Custom Fields Methods...\n\n";
    
    try {
        $statusModel = new StatusModel();
        
        // Test getting all statuses
        $statuses = $statusModel->all();
        echo "✓ Retrieved " . count($statuses) . " statuses\n";
        
        // Test getting custom fields for each status
        foreach ($statuses as $status) {
            $customFields = $statusModel->getCustomFieldsByName($status['name']);
            if (count($customFields) > 0) {
                echo "✓ Status '{$status['name']}' has " . count($customFields) . " custom fields\n";
                foreach ($customFields as $field) {
                    echo "  - {$field['field_label']} ({$field['field_type']})" . 
                         ($field['is_required'] ? ' [Required]' : '') . "\n";
                }
            } else {
                echo "  Status '{$status['name']}' has no custom fields\n";
            }
        }
        
        // Test bulk update restriction
        foreach ($statuses as $status) {
            $restricts = $statusModel->restrictsBulkUpdateByName($status['name']);
            echo "  Status '{$status['name']}' " . ($restricts ? 'restricts' : 'allows') . " bulk updates\n";
        }
        
    } catch (Exception $e) {
        echo "✗ Error testing StatusModel: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

// Test database connection and custom fields table
function testDatabase() {
    echo "Testing Database Custom Fields Table...\n\n";
    
    try {
        $db = new Database();
        $pdo = $db->getConnection();
        
        // Check if custom fields table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'status_custom_fields'");
        if ($stmt->rowCount() > 0) {
            echo "✓ status_custom_fields table exists\n";
            
            // Check table structure
            $stmt = $pdo->query("DESCRIBE status_custom_fields");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo "✓ Table columns: " . implode(', ', $columns) . "\n";
            
            // Check for sample data
            $stmt = $pdo->query("SELECT COUNT(*) FROM status_custom_fields");
            $count = $stmt->fetchColumn();
            echo "✓ Custom fields records: " . $count . "\n";
            
            if ($count > 0) {
                $stmt = $pdo->query("SELECT * FROM status_custom_fields LIMIT 3");
                $fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo "Sample custom fields:\n";
                foreach ($fields as $field) {
                    echo "  - {$field['field_label']} ({$field['field_type']}) for status_id {$field['status_id']}\n";
                }
            }
        } else {
            echo "✗ status_custom_fields table does not exist\n";
        }
        
    } catch (Exception $e) {
        echo "✗ Error testing database: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

// Run all tests
echo "=== Custom Fields Functionality Test ===\n\n";

testDatabase();
testStatusModel();
testCustomFieldsEndpoint();

echo "=== Test Complete ===\n";
?>
