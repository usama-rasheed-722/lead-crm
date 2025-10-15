<?php
// app/init.php
session_start();
$config = require __DIR__.'/config.php';


// PDO connection
$dsn = "mysql:host={$config['db']['host']};dbname={$config['db']['name']}";
try {
$pdo = new PDO($dsn, $config['db']['user'], $config['db']['pass'], [
PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
} catch (Exception $e) {
die('DB Connection error: '.$e->getMessage());
}


// autoloader (simple)
spl_autoload_register(function($class){
$paths = [__DIR__.'/models/', __DIR__.'/controllers/'];
foreach($paths as $p){
$file = $p.$class.'.php';
if(file_exists($file)) require $file;
}
});


// helper functions
require_once __DIR__.'/helpers.php';


// expose $pdo globally via container
$GLOBALS['pdo'] = $pdo;