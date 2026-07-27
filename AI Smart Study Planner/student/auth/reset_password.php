<?php
require_once __DIR__ . '/../../config/config.php';

$token = $_GET['token'] ?? $_POST['token'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW() ORDER BY id DESC LIMIT 1");
$stmt->execute([$token]);
$reset = $stmt->fetch();

$error = null;
$success = false;

if (!$reset) {
    $error = 'This reset link is invalid or has expired. Please request a new one.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    if (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $pdo->prepare("UPDATE students SET password_hash = ? WHERE email = ?")->execute([$hash, $reset['email']]);
        $pdo->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$reset['email']]);
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Set new password · Smart Buddy</title>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="auth-body">
<div class="auth-card-standalone">
  <h2>Set a new password</h2>
  <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
  <?php if ($success): ?>
    <div class="alert alert-success">Your password has been updated.</div>
    <div class="auth-skip"><a href="<?= BASE_URL ?>/student/auth/login.php">Continue to login</a></div>
  <?php elseif ($reset): ?>
    <form method="post">
      <input type="hidden" name="token" value="<?= e($token) ?>">
      <div class="field"><label>New password</label><input type="password" name="password" id="reset-password" oninput="checkPasswordStrength(this,'reset-pw-fill','reset-pw-label')" required></div>
      <div class="strength-meter">
        <div class="strength-track"><div class="strength-fill" id="reset-pw-fill"></div></div>
        <div class="strength-label" id="reset-pw-label"></div>
      </div>
      <div class="field"><label>Confirm new password</label><input type="password" name="confirm_password" required></div>
      <button class="btn-primary" type="submit">Update password</button>
    </form>
  <?php endif; ?>
</div>
<script src="<?= BASE_URL ?>/assets/js/app.js"></script>
</body>
</html>
