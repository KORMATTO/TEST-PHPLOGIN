<?php
require_once 'security.php';
secure_session();
error_log("NINJA LOGOUT: {$_SESSION['user_id']??'GUEST'} IP: {$_SERVER['REMOTE_ADDR']}");
$_SESSION=[]; 
$params=session_get_cookie_params();
setcookie(session_name(), '', time()-86400*365, $params['path'], $params['domain'], true, true);
session_destroy(); header('Location: login.php'); exit;
