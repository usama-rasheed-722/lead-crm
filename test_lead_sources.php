<?php
/**
 * Test script to verify lead source functionality
 * This script tests the lead source management and import validation
 */

require_once 'app/init.php';

// Test the LeadSourceModel
function testLeadSourceModel() {
    echo "Testing LeadSourceModel...\n\n";
    
    try {
        $leadSourceModel = new LeadSourceModel();
        
        // Test getting all lead sources
        $sources = $leadSourceModel->all();
        echo "✓ Retrieved " . count($sources) . " lead sources\n";
        
        // Test getting active lead sources
        $activeSources = $leadSourceModel->getActive();
        echo "✓ Retrieved " . count($activeSources) . " active lead sources\n";
        
        // Test getting lead source by name
        if (!empty($sources)) {
            $firstSource = $sources[0];
            $sourceByName = $leadSourceModel->getByName($firstSource['name']);
            if ($sourceByName) {
                echo "✓ Successfully retrieved lead source by name: {$sourceByName['name']}\n";
            } else {
                echo "✗ Failed to retrieve lead source by name\n";
            }
        }
        
        // Test name exists check
        if (!empty($sources)) {
            $firstSource = $sources[0];
            $exists = $leadSourceModel->nameExists($firstSource['name']);
            if ($exists) {
                echo "✓ Name exists check working correctly\n";
            } else {
                echo "✗ Name exists check failed\n";
            }
        }
        
        // Test usage count
        if (!empty($sources)) {
            $firstSource = $sources[0];
            $usageCount = $leadSourceModel->getUsageCount($firstSource['id']);
            echo "✓ Usage count for '{$firstSource['name']}': {$usageCount} leads\n";
        }
        
    } catch (Exception $e) {
        echo "✗ Error testing LeadSourceModel: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

// Test import validation
function testImportValidation() {
    echo "Testing Import Validation...\n\n";
    
    try {
        $importController = new ImportController();
        
        // Create test data with valid status and lead source
        $testData = [
            [
                'company' => 'Test Company 1',
                'email' => 'test1@example.com',
                'status' => 'New Lead',
                'lead_source' => 'Website'
            ],
            [
                'company' => 'Test Company 2',
                'email' => 'test2@example.com',
                'status' => 'Qualified',
                'lead_source' => 'Referral'
            ]
        ];
        
        // Use reflection to access private method
        $reflection = new ReflectionClass($importController);
        $validateMethod = $reflection->getMethod('validateImportData');
        $validateMethod->setAccessible(true);
        
        // This should not throw an exception
        $validateMethod->invoke($importController, $testData);
        echo "✓ Valid data validation passed\n";
        
        // Test with invalid status
        $invalidData = [
            [
                'company' => 'Test Company 3',
                'email' => 'test3@example.com',
                'status' => 'Invalid Status',
                'lead_source' => 'Website'
            ]
        ];
        
        try {
            $validateMethod->invoke($importController, $invalidData);
            echo "✗ Invalid status validation should have failed\n";
        } catch (Exception $e) {
            echo "✓ Invalid status validation correctly failed: " . substr($e->getMessage(), 0, 50) . "...\n";
        }
        
        // Test with invalid lead source
        $invalidLeadSourceData = [
            [
                'company' => 'Test Company 4',
                'email' => 'test4@example.com',
                'status' => 'New Lead',
                'lead_source' => 'Invalid Source'
            ]
        ];
        
        try {
            $validateMethod->invoke($importController, $invalidLeadSourceData);
            echo "✗ Invalid lead source validation should have failed\n";
        } catch (Exception $e) {
            echo "✓ Invalid lead source validation correctly failed: " . substr($e->getMessage(), 0, 50) . "...\n";
        }
        
    } catch (Exception $e) {
        echo "✗ Error testing import validation: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

// Test database structure
function testDatabaseStructure() {
    echo "Testing Database Structure...\n\n";
    
    try {
        $pdo = Database::getInstance()->getConnection();
        
        // Check if lead_sources table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'lead_sources'");
        if ($stmt->rowCount() > 0) {
            echo "✓ lead_sources table exists\n";
            
            // Check table structure
            $stmt = $pdo->query("DESCRIBE lead_sources");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo "✓ Table columns: " . implode(', ', $columns) . "\n";
            
            // Check for sample data
            $stmt = $pdo->query("SELECT COUNT(*) FROM lead_sources");
            $count = $stmt->fetchColumn();
            echo "✓ Lead sources records: " . $count . "\n";
            
            if ($count > 0) {
                $stmt = $pdo->query("SELECT * FROM lead_sources LIMIT 3");
                $sources = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo "Sample lead sources:\n";
                foreach ($sources as $source) {
                    echo "  - {$source['name']} (ID: {$source['id']}, Active: " . ($source['is_active'] ? 'Yes' : 'No') . ")\n";
                }
            }
        } else {
            echo "✗ lead_sources table does not exist\n";
        }
        
        // Check if lead_source_id column exists in leads table
        $stmt = $pdo->query("SHOW COLUMNS FROM leads LIKE 'lead_source_id'");
        if ($stmt->rowCount() > 0) {
            echo "✓ lead_source_id column exists in leads table\n";
        } else {
            echo "✗ lead_source_id column missing in leads table\n";
        }
        
        // Check foreign key constraint
        $stmt = $pdo->query("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_NAME = 'leads' 
            AND COLUMN_NAME = 'lead_source_id' 
            AND REFERENCED_TABLE_NAME = 'lead_sources'
        ");
        if ($stmt->rowCount() > 0) {
            echo "✓ Foreign key constraint exists for lead_source_id\n";
        } else {
            echo "✗ Foreign key constraint missing for lead_source_id\n";
        }
        
    } catch (Exception $e) {
        echo "✗ Error testing database structure: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

// Test helper function
function testHelperFunction() {
    echo "Testing Helper Functions...\n\n";
    
    try {
        // Test getIdByName function
        $testData = [
            ['id' => 1, 'name' => 'New Lead'],
            ['id' => 2, 'name' => 'Qualified'],
            ['id' => 3, 'name' => 'Converted']
        ];
        
        $id = getIdByName($testData, 'New Lead');
        if ($id === 1) {
            echo "✓ getIdByName function working correctly\n";
        } else {
            echo "✗ getIdByName function failed (expected 1, got {$id})\n";
        }
        
        // Test case insensitive matching
        $id = getIdByName($testData, 'new lead');
        if ($id === 1) {
            echo "✓ Case insensitive matching working\n";
        } else {
            echo "✗ Case insensitive matching failed\n";
        }
        
        // Test non-existent name
        try {
            getIdByName($testData, 'Non Existent');
            echo "✗ Should have thrown exception for non-existent name\n";
        } catch (Exception $e) {
            echo "✓ Correctly threw exception for non-existent name\n";
        }
        
    } catch (Exception $e) {
        echo "✗ Error testing helper functions: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

// Run all tests
echo "=== Lead Source Management Test ===\n\n";

testDatabaseStructure();
testLeadSourceModel();
testHelperFunction();
testImportValidation();

echo "=== Test Complete ===\n";
?>
