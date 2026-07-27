<?php
require_once __DIR__ . '/../config/config.php';
if (is_admin_logged_in()) redirect(BASE_URL . '/admin/dashboard.php');

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();
    if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        redirect(BASE_URL . '/admin/dashboard.php');
    } else {
        $error = 'Incorrect email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin login · Smart Buddy</title>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body class="auth-split-body">
  <div class="auth-side">
    <div class="brand-mark">
      <span class="sundial"> <img style="border-radius: 20px;" width="100px" src="cavendish-university-zambia.jpg" alt="CUZ"> </span>
      Smart Buddy · Admin console
    </div>
    <div class="auth-hero">
      <h1>Every student, every programme, one console.</h1>
      <p>Add students to their programme, manage courses, and keep the academic side of Smart Buddy running smoothly.</p>
    </div>
    <div class="auth-quote">
      "Give a student their login, and their whole semester is mapped out for them from day one."
      <span>Academic Success Begins at Cavendish University Zambia</span>
    </div>
  </div>

  <div class="auth-form-wrap">
    <div class="auth-card">
      <div class="tabbar">
        <a href="<?= BASE_URL ?>/student/auth/login.php">Student Login</a>
        <a href="<?= BASE_URL ?>/admin/login.php" class="active">Admin Login</a>
      </div>

      <h2>Admin console</h2>
      <div class="sub">Log in to manage students, programmes, and courses.</div>
      <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
      <form method="post">
        <div class="field"><label>Admin email</label><input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required></div>
        <div class="field"><label>Password</label><input type="password" name="password" required></div>
        <button class="btn-primary" type="submit">Log in</button>
      </form>
    </div>
  </div>
</body>
</html>
