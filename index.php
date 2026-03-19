<?php
require_once 'config.php'; require_once 'security.php';
secure_session();
if(!validate_session()) { header('Location: login.php'); exit; }
?>
<!DOCTYPE html><html><head><title>Naruto Game 🥷</title>
<meta charset="UTF-8" name="viewport" content="width=device-width,initial-scale=1">
</head><body style="font-family:Arial">
<h1>🥷 NINJA WELCOME <?= strtoupper(h($_SESSION['username'])) ?> 🥷</h1>
<p>🔥 Your secret Naruto game starts here...</p>
<p><a href="logout.php" style="background:#ff4444;color:white;padding:10px;">LOGOUT</a></p>
</body></html>
