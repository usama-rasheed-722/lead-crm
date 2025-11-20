<?php
// setup.php - Database setup and demo data
require_once __DIR__ . '/core/Database.php';

try {
    // Create database connection
    $pdo = new PDO("mysql:host=localhost;dbname=crm_db", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // // Read and execute schema
    $schema = file_get_contents(__DIR__ . '/dbschema.sql');
    $statements = explode(';', $schema);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }
    
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

    // Insert demo meeting bookings
    $meetings = [
        [
            'client_name' => 'Sara Ahmed',
            'client_email' => 'sara.ahmed@example.com',
            'client_phone' => '+971501234567',
            'company_name' => 'Alifcode Ventures',
            'business_model' => 'Technology consulting and custom software development.',
            'meeting_agenda' => 'Discuss project scope for CRM integration and automation.',
            'preferred_date' => date('Y-m-d', strtotime('+3 days')),
            'preferred_time' => '10:00:00',
            'timezone' => 'Asia/Dubai',
            'client_feedback' => 'Looking for an agile partner to help scale operations.',
            'client_notes' => 'Prefer virtual meeting via Microsoft Teams.',
            'status' => 'scheduled',
            'admin_notes' => 'Assign to client success team for follow-up.',
        ],
        [
            'client_name' => 'John Miller',
            'client_email' => 'john.miller@example.com',
            'client_phone' => '+1 (415) 555-0182',
            'company_name' => 'Northwind Traders',
            'business_model' => 'E-commerce platform specializing in artisan goods.',
            'meeting_agenda' => 'Explore partnership opportunities and marketing automation.',
            'preferred_date' => date('Y-m-d', strtotime('+5 days')),
            'preferred_time' => '16:30:00',
            'timezone' => 'America/Los_Angeles',
            'client_feedback' => 'Interested in better lead attribution and reporting.',
            'client_notes' => 'Will share existing workflows before the call.',
            'status' => 'new',
            'admin_notes' => null,
        ],
    ];

    $stmt = $pdo->prepare('
        INSERT INTO client_meetings
        (client_name, client_email, client_phone, company_name, business_model, meeting_agenda, preferred_date, preferred_time, timezone, preferred_datetime_utc, client_feedback, client_notes, status, admin_notes)
        VALUES
        (:client_name, :client_email, :client_phone, :company_name, :business_model, :meeting_agenda, :preferred_date, :preferred_time, :timezone, :preferred_datetime_utc, :client_feedback, :client_notes, :status, :admin_notes)
    ');

    foreach ($meetings as $meeting) {
        try {
            $tz = new DateTimeZone($meeting['timezone']);
        } catch (Exception $e) {
            $tz = new DateTimeZone('UTC');
        }
        $dt = new DateTime($meeting['preferred_date'] . ' ' . $meeting['preferred_time'], $tz);
        $meeting['preferred_datetime_utc'] = $dt->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');

        $stmt->execute($meeting);
    }

    echo "Demo meetings created successfully!\n";
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
