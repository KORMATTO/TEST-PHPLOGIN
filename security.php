<?php
// security.php - PENTAGON LEVEL
function secure_session() {
    if(session_status()!==PHP_SESSION_ACTIVE) {
        session_set_cookie_params([
            'lifetime'=>1200, 'path'=>'/', 'secure'=>!empty($_SERVER['HTTPS']),
            'httponly'=>true, 'samesite'=>'Strict'
        ]);
        session_start();
        if(!isset($_SESSION['ninja_seal'])) {
            session_regenerate_id(true);
            $_SESSION['ninja_seal']=true;
            $_SESSION['fingerprint']=hash('sha256', $_SERVER['REMOTE_ADDR'].
                substr($_SERVER['HTTP_USER_AGENT']??'',0,100).session_id());
        }
    }
}

function validate_session() {
    return isset($_SESSION['user_id']) && hash_equals($_SESSION['fingerprint'],
        hash('sha256', $_SERVER['REMOTE_ADDR'].substr($_SERVER['HTTP_USER_AGENT']??'',0,100).session_id())
    );
}

function csrf_token() {
    return $_SESSION['csrf']??($_SESSION['csrf']=bin2hex(random_bytes(64)));
}

function verify_csrf($token) {
    $valid=hash_equals($_SESSION['csrf']??'', $token);
    if($valid) $_SESSION['csrf']=bin2hex(random_bytes(64));
    return $valid;
}

function h($s) { return htmlspecialchars($s??'', ENT_QUOTES | ENT_HTML5, 'UTF-8'); }

function ninja_filter($input) {
    $input=trim($input??'');
    return (strlen($input)>=2 && strlen($input)<=100 && preg_match('/^[a-zA-Z0-9@._-]+$/', $input)) ? $input : null;
}

function rate_limit_ip($action='login') {
    $ip=$_SERVER['REMOTE_ADDR'];
    $key="rate_{$action}_{$ip}";
    if(!isset($_SESSION[$key])) $_SESSION[$key]=['count'=>0,'time'=>time()];
    $data=&$_SESSION[$key];
    if((time()-$data['time'])<3600 && $data['count']>=5) return false;
    $data['count']++; return true;
}
