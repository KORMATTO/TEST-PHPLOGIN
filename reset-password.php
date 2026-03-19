<?php
require_once 'config.php'; require_once 'security.php';
secure_session(); $errors=[]; $success=''; $token=$_GET['token']??'';

if($_SERVER['REQUEST_METHOD']==='POST' && verify_csrf($_POST['csrf']??'')) {
    $password=$_POST['password']??'';
    $confirm=$_POST['confirm']??'';
    $token=$_POST['token']??'';
    
    if(strlen($password)<12) $errors[]='12+ CHARS';
    elseif($password!==$confirm) $errors[]='NO MATCH';
    elseif(strlen($token)!==128) $errors[]='BAD LINK';
    else {
        $hash=hash('sha256', $token);
        $stmt=$pdo->prepare('SELECT u.id FROM password_resets pr JOIN users u ON pr.user_id=u.id WHERE pr.token_hash=? AND pr.used=0 AND pr.expires_at>NOW() AND pr.ip_address=?');
        $stmt->execute([$hash, $_SERVER['REMOTE_ADDR']??'']);
        $user=$stmt->fetch();
        
        if($user) {
            $pdo->beginTransaction();
            try {
                $pdo->prepare('UPDATE users SET password=? WHERE id=?')->execute([
                    password_hash($password, PASSWORD_ARGON2ID, ['memory_cost'=>131072]), $user['id']
                ]);
                $pdo->prepare('UPDATE password_resets SET used=1 WHERE token_hash=?')->execute([$hash]);
                $pdo->commit(); $success='PASSWORD CHANGED! <a href="login.php">LOGIN</a>';
            } catch(Exception $e) { $pdo->rollBack(); $errors[]='ERROR'; }
        } else $errors[]='DEAD LINK';
    }
}

$valid=(strlen($token)===128 && $pdo->prepare('SELECT 1 FROM password_resets WHERE token_hash=? AND used=0 AND expires_at>NOW() AND ip_address=?')
    ->execute([hash('sha256',$token), $_SERVER['REMOTE_ADDR']??'']) && $pdo->fetch());
?>
<!DOCTYPE html><html><head><title>Reset 🥷</title></head><body>
<h2>🥷 NEW PASSWORD</h2>
<?php if($success): ?><?= $success ?><?php 
elseif(!$valid): ?><p style="color:#ff4444">DEAD LINK <a href="forgot-password.php">NEW</a></p><?php 
else: ?>
<?php foreach($errors as $e): ?><p style="color:#ff4444"><?= h($e) ?></p><?php endforeach ?>
<form method="POST">
<input type="hidden" name="csrf" value="<?= csrf_token() ?>">
<input type="hidden" name="token" value="<?= h($token) ?>">
<p>Password: <input type="password" name="password" minlength="12" required></p>
<p>Confirm: <input type="password" name="confirm" minlength="12" required></p>
<button>LOCK IT DOWN</button>
</form><?php endif ?>
<p><a href="login.php">LOGIN</a></p>
</body></html>

