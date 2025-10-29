<?php
// setup.php - Database setup and demo data
require_once __DIR__ . '/core/Database.php';

try {
    // Create database connection
    $pdo = new PDO("mysql:host=localhost", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // // Read and execute schema
    // $schema = file_get_contents(__DIR__ . '/dbschema.sql');
    // $statements = explode(';', $schema);
    
    // foreach ($statements as $statement) {
    //     $statement = trim($statement);
    //     if (!empty($statement)) {
    //         $pdo->exec($statement);
    //     }
    // }
    
    // echo "Database schema created successfully!\n";
    
    // Insert demo users
    $users = [
        [
            'username' => 'admin',
            'email' => 'admin@crm.com',
            'password' => password_hash('admin123', PASSWORD_BCRYPT),
            'full_name' => 'System Administrator',
            'role' => 'admin'
        ],
        [
            'username' => 'manager',
            'email' => 'manager@crm.com',
            'password' => password_hash('manager123', PASSWORD_BCRYPT),
            'full_name' => 'John Manager',
            'role' => 'manager'
        ],
        [
            'username' => 'sdr',
            'email' => 'sdr@crm.com',
            'password' => password_hash('sdr123', PASSWORD_BCRYPT),
            'full_name' => 'Jane SDR',
            'role' => 'sdr'
        ]
    ];
    
    $stmt = $pdo->prepare('INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, ?)');
    foreach ($users as $user) {
        $stmt->execute([$user['username'], $user['email'], $user['password'], $user['full_name'], $user['role']]);
    }
    
    echo "Demo users created successfully!\n";
    
    // Insert demo leads
    $leads = [
        [
            'lead_id' => 'SDR3-00001',
            'name' => 'John Smith',
            'company' => 'Acme Corporation',
            'email' => 'john.smith@acme.com',
            'phone' => '+1-555-0123',
            'linkedin' => 'https://linkedin.com/in/johnsmith',
            'website' => 'https://acme.com',
            'clutch' => 'https://clutch.co/profile/acme-corp',
            'sdr_id' => 3,
            'duplicate_status' => 'unique',
            'notes' => 'Interested in our web development services',
            'created_by' => 3
        ],
        [
            'lead_id' => 'SDR3-00002',
            'name' => 'Jane Doe',
            'company' => 'Tech Solutions Inc',
            'email' => 'jane@techsolutions.com',
            'phone' => '+1-555-0456',
            'linkedin' => 'https://linkedin.com/in/janedoe',
            'website' => 'https://techsolutions.com',
            'clutch' => '',
            'sdr_id' => 3,
            'duplicate_status' => 'unique',
            'notes' => 'Looking for mobile app development',
            'created_by' => 3
        ],
        [
            'lead_id' => 'SDR3-00003',
            'name' => 'Bob Johnson',
            'company' => 'StartupXYZ',
            'email' => 'bob@startupxyz.com',
            'phone' => '+1-555-0789',
            'linkedin' => 'https://linkedin.com/in/bobjohnson',
            'website' => 'https://startupxyz.com',
            'clutch' => 'https://clutch.co/profile/startupxyz',
            'sdr_id' => 3,
            'duplicate_status' => 'unique',
            'notes' => 'Need e-commerce platform development',
            'created_by' => 3
        ],
        [
            'lead_id' => '', // No SDR number - for testing
            'name' => 'Alice Wilson',
            'company' => 'Digital Agency',
            'email' => 'alice@digitalagency.com',
            'phone' => '+1-555-0321',
            'linkedin' => 'https://linkedin.com/in/alicewilson',
            'website' => 'https://digitalagency.com',
            'clutch' => '',
            'sdr_id' => 3,
            'duplicate_status' => 'incomplete',
            'notes' => 'Needs SDR number generation',
            'created_by' => 3
        ]
    ];
    
    $stmt = $pdo->prepare('INSERT INTO leads (lead_id, name, company, email, phone, linkedin, website, clutch, sdr_id, duplicate_status, notes, created_by, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    foreach ($leads as $lead) {
        $stmt->execute([
            $lead['lead_id'], $lead['name'], $lead['company'], $lead['email'], 
            $lead['phone'], $lead['linkedin'], $lead['website'], $lead['clutch'], 
            $lead['sdr_id'], $lead['duplicate_status'], $lead['notes'], $lead['created_by'], 'New Lead'
        ]);
    }
    
    echo "Demo leads created successfully!\n";
    
    // Insert demo notes
    $notes = [
        [1, 3, 'call', 'Initial call made. Lead is interested in our services.'],
        [1, 3, 'email', 'Sent proposal document via email.'],
        [2, 3, 'note', 'Lead mentioned budget constraints but still interested.'],
        [3, 3, 'call', 'Follow-up call scheduled for next week.']
    ];
    
    $stmt = $pdo->prepare('INSERT INTO lead_notes (lead_id, user_id, type, content) VALUES (?, ?, ?, ?)');
    foreach ($notes as $note) {
        $stmt->execute($note);
    }
    
    echo "Demo notes created successfully!\n";
    echo "\n=== Setup Complete ===\n";
    echo "Demo accounts created:\n";
    echo "- Admin: admin / admin123\n";
    echo "- Manager: manager / manager123\n";
    echo "- SDR: sdr / sdr123\n";
    echo "\nYou can now access the CRM at: http://localhost/LeadManager/\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
