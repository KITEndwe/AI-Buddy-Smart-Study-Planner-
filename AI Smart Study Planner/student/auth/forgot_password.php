<?php
require_once __DIR__ . '/../../config/config.php';

$sent = false;
$resetLink = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $stmt = $pdo->prepare("SELECT id FROM students WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)")
            ->execute([$email, $token, $expires]);

        // In production this link is emailed via PHPMailer/SMTP. Shown here for demo purposes.
        $resetLink = BASE_URL . '/student/auth/reset_password.php?token=' . $token;
    }
    $sent = true; // Always show a generic success message (don't reveal whether the email exists)
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset password · Smart Buddy</title>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="auth-body">
<div class="auth-card-standalone">
  <h2>Reset your password</h2>
  <div class="sub">Enter your student email and we'll send you a reset link.</div>
  <?php if ($sent): ?>
    <div class="alert alert-success">If that email is registered, a reset link has been sent.</div>
    <?php if ($resetLink): ?>
      <div class="alert alert-info" style="word-break:break-all;">
        Demo mode (no SMTP configured) — link: <a href="<?= e($resetLink) ?>"><?= e($resetLink) ?></a>
      </div>
    <?php endif; ?>
  <?php else: ?>
    <form method="post">
      <div class="field"><label>Student email</label><input type="email" name="email" required></div>
      <button class="btn-primary" type="submit">Send reset link</button>
    </form>
  <?php endif; ?>
  <div class="auth-skip"><a href="<?= BASE_URL ?>/student/auth/login.php">Back to login</a></div>
</div>
</body>
</html>
