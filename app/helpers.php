<?php
// app/helpers.php
function base_url($path = ''){
$cfg = require __DIR__.'/config.php';
return rtrim($cfg['app']['base_url'], '/') . '/' . ltrim($path, '/');
}


function auth_user(){
return $_SESSION['user'] ?? null;
}


function require_role($roles = []){
$user = auth_user();
if(!$user) {
header('Location: /index.php?action=login'); exit;
}
if(!in_array($user['role'], (array)$roles)){
http_response_code(403); echo 'Forbidden'; exit;
}
}


function normalize($v){
return trim(strtolower(preg_replace('/\s+/', ' ', $v)));
}


function normalize_phone($p){
return preg_replace('/[^0-9+]/', '', $p);
}


function pr(...$pre){
    print_r($pre);

    if(end($pre) == 1){
        die('end of response');
    }
}


// Generate next SDR number for a user
function generateNextSDR($userId) {
    $pdo = Database::getInstance()->getConnection();
    
    // Fetch the latest SDR ID for this user
    $stmt = $pdo->prepare('
        SELECT lead_id 
        FROM leads 
        WHERE lead_id LIKE ? 
        ORDER BY id DESC 
        LIMIT 1
    ');
    $pattern = "SDR{$userId}-%";
    $stmt->execute([ $pattern]);
    $latestLead = $stmt->fetch();
    
    $nextNumber = 1;
    
    if ($latestLead && !empty($latestLead['lead_id'])) {
        // Extract numeric part using regex
        if (preg_match('/SDR' . $userId . '-(\d+)/', $latestLead['lead_id'], $matches)) {
            $lastNumber = (int) $matches[1];
            $nextNumber = $lastNumber + 1;
        }
    }
    
    // Pad with leading zeros to 5 digits (matching existing format)
    $formattedNumber = str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    
    // Final format: SDR{user_id}-00001
    $newSDR = "SDR{$userId}-{$formattedNumber}";
    
    return $newSDR;
}

// Generate SDR number for a specific lead
function generateSDRNumber($leadId, $userId) {
    $pdo = Database::getInstance()->getConnection();
    
    // Check if lead already has an SDR number
    $stmt = $pdo->prepare('SELECT lead_id FROM leads WHERE id = ?');
    $stmt->execute([$leadId]);
    $lead = $stmt->fetch();
    
    if ($lead && !empty($lead['lead_id'])) {
        return $lead['lead_id']; // Already has SDR number
    }
    
    // Generate new SDR number
    $sdrNumber = generateNextSDR($userId);
    
    // Update the lead with the new SDR number
    $stmt = $pdo->prepare('UPDATE leads SET lead_id = ? WHERE id = ?');
    $stmt->execute([$sdrNumber, $leadId]);
    
    return $sdrNumber;
}
