<?php
require_once 'config.php'; require_once 'security.php';
secure_session();
if(!rate_limit_ip('reset')) die('TOO MANY REQUESTS');
$errors=[]; $success=''; $email='';

if($_SERVER['REQUEST_METHOD']==='POST' && verify_csrf($_POST['csrf']??'')) {
    $email=filter_var($_POST['email']??'', FILTER_VALIDATE_EMAIL);
    if(!$email) $errors[]='VALID EMAIL ONLY';
    else {
        $stmt=$pdo->prepare('SELECT id FROM users WHERE email=?');
        $stmt->execute([$email]);
        if($stmt->fetch()) {
            // 128-BIT ARMOR TOKEN
            $token=bin2hex(random_bytes(64));
            $hash=hash('sha256', $token);
            $expires=date('Y-m-d H:i:s', time()+1800);
            
            $pdo->prepare('DELETE FROM password_resets WHERE user_id=(SELECT id FROM users WHERE email=?)')
                ->execute([$email]);
            $pdo->prepare('INSERT INTO password_resets(user_id,token_hash,expires_at,ip_address) VALUES((SELECT id FROM users WHERE email=?),?,?,?)')
                ->execute([$email, $hash, $expires, $_SERVER['REMOTE_ADDR']??'']);
            
            // LOG FOR EMAIL (REPLACE WITH SMTP)
            file_put_contents('ninja-resets.log', date('Y-m-d H:i:s')." {$email}: reset.php?token=$token\n",
                FILE_APPEND|LOCK_EX);
            $success='CHECK EMAIL (30min)';
        } else usleep(250000); // TIMING ATTACK DEFENSE
    }
}
?>
<!DOCTYPE html><html><head><title>Forgot 🥷</title></head><body>
<h2>🥷 FORGOT PASSWORD</h2>
<?php if($success): ?><p style="color:#44ff44"><?= h($success) ?></p><?php endif ?>
<?php foreach($errors as $e): ?><p style="color:#ff4444"><?= h($e) ?></p><?php endforeach ?>
<form method="POST">
<input type="hidden" name="csrf" value="<?= csrf_token() ?>">
<p>Email: <input type="email" name="email" value="<?= h($email) ?>" required></p>
<button>RESET</button>
</form><p><a href="login.php">LOGIN</a></p>
</body></html>
