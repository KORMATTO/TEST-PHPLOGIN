<?php
require_once 'config.php'; require_once 'security.php';
secure_session();
$errors=[]; $identifier='';

if($_SERVER['REQUEST_METHOD']==='POST' && rate_limit_ip('login') && verify_csrf($_POST['csrf']??'')) {
    $identifier=ninja_filter($_POST['identifier']);
    $password=$_POST['password']??'';
    
    if(!$identifier || strlen($password)<6) $errors[]='WRONG INFO';
    else {
        // CHECK LOCKOUT + GET USER
        $stmt=$pdo->prepare('SELECT id,username,password,failed_attempts,lockout_until FROM users WHERE username=? OR email=?');
        $stmt->execute([$identifier, $identifier]);
        $user=$stmt->fetch();
        
        if($user && (!$user['lockout_until'] || strtotime($user['lockout_until'])<time()) 
            && password_verify($password, $user['password'])) {
            
            // NUCLEAR SESSION UPGRADE
            $_SESSION['user_id']=$user['id'];
            $_SESSION['username']=$user['username'];
            $_SESSION['fingerprint']=hash('sha256', $_SERVER['REMOTE_ADDR'].
                substr($_SERVER['HTTP_USER_AGENT']??'',0,100).session_id());
            session_regenerate_id(true);
            
            // RESET ATTEMPTS
            $pdo->prepare('UPDATE users SET failed_attempts=0,lockout_until=NULL,last_login=NOW() WHERE id=?')
                ->execute([$user['id']]);
            
            header('Location: index.php'); exit;
        } else {
            // LOCKOUT + LOG
            if($user) {
                $attempts=$user['failed_attempts']+1;
                $lockout=($attempts>=3) ? date('Y-m-d H:i:s', time()+900) : null;
                $pdo->prepare('UPDATE users SET failed_attempts=?,lockout_until=? WHERE id=?')
                    ->execute([$attempts, $lockout, $user['id']]);
            }
            $errors[]='LOCKED OR WRONG';
        }
    }
}
?>
<!DOCTYPE html><html><head><title>Login 🥷</title></head><body>
<h2>🥷 NINJA LOGIN</h2>
<?php foreach($errors as $e): ?><p style="color:#ff4444"><?= h($e) ?></p><?php endforeach ?>
<form method="POST">
<input type="hidden" name="csrf" value="<?= csrf_token() ?>">
<p>Username/Email: <input type="text" name="identifier" value="<?= h($identifier) ?>" required></p>
<p>Password: <input type="password" name="password" required></p>
<button style="background:#4444ff">ENTER</button>
</form>
<p><a href="register.php">REGISTER</a> | <a href="forgot-password.php">FORGOT?</a></p>
</body></html>
