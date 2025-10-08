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