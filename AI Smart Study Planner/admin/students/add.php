<?php
require_once __DIR__ . '/../../config/config.php';
require_admin();
$pageTitle = 'Add student';
$activeNav = 'students';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first = trim($_POST['first_name'] ?? '');
    $last = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $programmeId = (int)($_POST['programme_id'] ?? 0);
    $year = (int)($_POST['year_of_study'] ?? 1);
    $password = $_POST['password'] ?? '';

    if (!$first || !$last || !$email || !$programmeId || !$password) {
        $errors[] = 'Please fill in every field, including selecting a programme.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Temporary password must be at least 8 characters.';
    }
    if (!$errors) {
        $check = $pdo->prepare("SELECT id FROM students WHERE email=?");
        $check->execute([$email]);
        if ($check->fetch()) $errors[] = 'A student with that email already exists.';
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO students (programme_id, first_name, last_name, email, password_hash, year_of_study, added_by_admin) VALUES (?, ?, ?, ?, ?, ?, 1)");
        $stmt->execute([$programmeId, $first, $last, $email, $hash, $year]);
        $newId = $pdo->lastInsertId();
        $pdo->prepare("INSERT INTO notification_preferences (student_id) VALUES (?)")->execute([$newId]);
        set_flash('success', "Student {$first} {$last} was added to the programme. Share the temporary password with them so they can log in and change it.");
        redirect(BASE_URL . '/admin/students/index.php');
    }
}

$programmes = $pdo->query("SELECT * FROM programmes ORDER BY name")->fetchAll();
include __DIR__ . '/../../includes/admin_header.php';
?>
<div class="topline"><div><h1>Add student</h1><div class="desc">Register a student directly and assign them to a programme.</div></div></div>
<div class="card" style="max-width:560px;">
  <?php foreach ($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>
  <form method="post">
    <div class="form-row-2">
      <div class="field"><label>First name</label><input type="text" name="first_name" value="<?= e($_POST['first_name'] ?? '') ?>" required></div>
      <div class="field"><label>Last name</label><input type="text" name="last_name" value="<?= e($_POST['last_name'] ?? '') ?>" required></div>
    </div>
    <div class="field"><label>Student email</label><input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required></div>
    <div class="form-row-2">
      <div class="field"><label>Programme</label>
        <select name="programme_id" required>
          <option value="">Select programme</option>
          <?php foreach ($programmes as $p): ?>
            <option value="<?= $p['id'] ?>" <?= (($_POST['programme_id'] ?? '')==$p['id'])?'selected':'' ?>><?= e($p['name']) ?> (<?= e($p['code']) ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field"><label>Year of study</label>
        <select name="year_of_study">
          <?php for ($y=1;$y<=6;$y++): ?><option value="<?= $y ?>"><?= $y ?></option><?php endfor; ?>
        </select>
      </div>
    </div>
    <div class="field"><label>Temporary password</label><input type="text" name="password" id="admin-temp-password" placeholder="Given to the student to log in with" oninput="checkPasswordStrength(this,'admin-pw-fill','admin-pw-label')" required></div>
    <div class="strength-meter">
      <div class="strength-track"><div class="strength-fill" id="admin-pw-fill"></div></div>
      <div class="strength-label" id="admin-pw-label"></div>
    </div>
    <button class="pill-btn teal" type="submit">Add student to programme</button>
  </form>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
