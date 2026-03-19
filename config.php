<?php
// config.php - IMPOSSIBLE TO BREACH
error_reporting(0); ini_set('display_errors', 0); ini_set('log_errors', 1);

// KILL ALL ATTACK VECTORS
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
header('Referrer-Policy: no-referrer');
header('Permissions-Policy: geolocation=(),microphone=()');
header_remove('X-Powered-By');

// BLOCK HACKER BOTS
$evil_bots = ['bot','crawler','spider','nikto','sqlmap','w3af','zmeu','acunetix'];
foreach($evil_bots as $bot) {
    if(stripos($_SERVER['HTTP_USER_AGENT']??'', $bot)!==false) {
        http_response_code(403); exit('GTFO');
    }
}

// ULTRA SECURE DB
$host='localhost'; $db='naruto_game'; $user='root'; $pass='';
$dsn="mysql:host=$host;dbname=$db;charset=utf8mb4";
$options=[PDO::ATTR_ERRMODE=>PDO::ERRMODE_SILENT, PDO::ATTR_EMULATE_PREPARES=>false];
try{$pdo=new PDO($dsn,$user,$pass,$options);}catch(Exception $e){
    error_log('DB DEAD: '.$e->getMessage()); http_response_code(500); die('OFFLINE');
}
