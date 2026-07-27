<?php
require_once __DIR__ . '/../../config/config.php';
if (is_logged_in()) redirect(BASE_URL . '/student/dashboard.php');

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM students WHERE email = ?");
    $stmt->execute([$email]);
    $student = $stmt->fetch();

    if ($student && password_verify($password, $student['password_hash'])) {
        if ($student['status'] === 'suspended') {
            $error = 'This account has been suspended. Contact your administrator.';
        } else {
            $_SESSION['student_id'] = $student['id'];
            redirect(BASE_URL . '/student/dashboard.php');
        }
    } else {
        $error = 'Incorrect email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Log in · Smart Buddy</title>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="auth-split-body">
  <div class="auth-side">
    <div class="brand-mark">
      <span class="sundial"> <img style="border-radius: 20px;" width="100px" src="cavendish-university-zambia.jpg" alt="CUZ"> </span>
      Smart Buddy · Cavendish University Zambia
    </div>
    <div class="auth-hero">
      <h1>Your study time, mapped like the sun tracks the sky.</h1>
      <p>Smart Buddy plans your week, keeps every deadline in view, and studies alongside you so you always know what's next.</p>
    </div>
    <div class="auth-quote">
      "The students who finish strong aren't the ones who study longest they're the ones who know exactly what to study next."
      <span>Academic Success Begins at Cavendish University Zambia</span>
    </div>
  </div>

  <div class="auth-form-wrap">
    <div class="auth-card">
      <div class="tabbar">
        <a href="<?= BASE_URL ?>/student/auth/login.php" class="active"> Student Login</a>
        <a href="<?= BASE_URL ?>/admin/login.php">Admin Login</a>
      </div>

      <h2>Welcome back</h2>
      <div class="sub">Log in to pick up where you left off.</div>
      <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
      <form method="post">
        <div class="field"><label>Student email</label><input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required></div>
        <div class="field"><label>Password</label><input type="password" name="password" required></div>
        <div style="text-align:right;margin-bottom:4px;"><a href="<?= BASE_URL ?>/student/auth/forgot_password.php" style="font-size:12.5px;color:var(--ink-soft);">Forgot password?</a></div>
        <button class="btn-primary" type="submit">Log in</button>
      </form>
      <div class="info-note">New here? Student accounts are created by your programme administrator. Ask them for your login email and temporary password, then use "Forgot password?" above to set your own.</div>
    </div>
  </div>
</body>
</html>
