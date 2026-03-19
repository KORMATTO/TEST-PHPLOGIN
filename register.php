<?php
require_once 'config.php'; require_once 'security.php';
secure_session();
$errors=[]; $form=['username'=>'','email'=>''];

if($_SERVER['REQUEST_METHOD']==='POST' && rate_limit_ip('register') && verify_csrf($_POST['csrf']??'')) {
    $form['username']=ninja_filter($_POST['username']);
    $form['email']=filter_var($_POST['email']??'', FILTER_VALIDATE_EMAIL);
    $password=$_POST['password']??'';
    
    if(!$form['username'] || strlen($form['username'])<4 || strlen($form['username'])>16)
        $errors[]='Username: 4-16 chars only';
    elseif(!$form['email']) $errors[]='Valid email ONLY';
    elseif(strlen($password)<12) $errors[]='Password: 12+ chars MINIMUM';
    else {
        $stmt=$pdo->prepare('SELECT id FROM users WHERE username=? OR email=?');
        $stmt->execute([$form['username'], $form['email']]);
        if($stmt->fetch()) $errors[]='Username/Email TAKEN';
        else {
            $hash=password_hash($password, PASSWORD_ARGON2ID, ['memory_cost'=>131072]);
            $stmt=$pdo->prepare('INSERT INTO users(username,email,password,ip_address,user_agent_hash) VALUES(?,?,?,?,?)');
            if($stmt->execute([$form['username'], $form['email'], $hash, $_SERVER['REMOTE_ADDR']??'',
                hash('sha256', $_SERVER['HTTP_USER_AGENT']??'')] )) {
                header('Location: login.php?ninja=registered'); exit;
            }
            $errors[]='ERROR - TRY AGAIN';
        }
    }
}
?>
<!DOCTYPE html><html><head><title>Register 🥷</title></head><body>
<h2>🥷 NINJA REGISTRATION</h2>
<?php foreach($errors as $e): ?><p style="color:#ff4444"><?= h($e) ?></p><?php endforeach ?>
<form method="POST">
<input type="hidden" name="csrf" value="<?= csrf_token() ?>">
<p>Username: <input type="text" name="username" value="<?= h($form['username']) ?>" maxlength="16" required></p>
<p>Email: <input type="email" name="email" value="<?= h($form['email']) ?>" required></p>
<p>Password: <input type="password" name="password" minlength="12" required></p>
<button style="background:#44ff44">CREATE NINJA</button>
</form><p><a href="login.php">LOGIN</a></p>
</body></html>
