<?php
require_once __DIR__ . '/../../config/config.php';
require_admin();
$pageTitle = 'Edit student';
$activeNav = 'students';

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM students WHERE id=?");
$stmt->execute([$id]);
$stud = $stmt->fetch();
if (!$stud) { set_flash('error', 'Student not found.'); redirect(BASE_URL . '/admin/students/index.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first = trim($_POST['first_name'] ?? '');
    $last = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $programmeId = (int)$_POST['programme_id'];
    $year = (int)$_POST['year_of_study'];
    $status = $_POST['status'] === 'suspended' ? 'suspended' : 'active';

    $pdo->prepare("UPDATE students SET first_name=?, last_name=?, email=?, programme_id=?, year_of_study=?, status=? WHERE id=?")
        ->execute([$first, $last, $email, $programmeId, $year, $status, $id]);
    set_flash('success', 'Student updated.');
    redirect(BASE_URL . '/admin/students/index.php');
}

$programmes = $pdo->query("SELECT * FROM programmes ORDER BY name")->fetchAll();
include __DIR__ . '/../../includes/admin_header.php';
?>
<div class="topline"><div><h1>Edit student</h1><div class="desc"><?= e($stud['first_name'].' '.$stud['last_name']) ?></div></div></div>
<div class="card" style="max-width:560px;">
  <form method="post">
    <input type="hidden" name="id" value="<?= $stud['id'] ?>">
    <div class="form-row-2">
      <div class="field"><label>First name</label><input type="text" name="first_name" value="<?= e($stud['first_name']) ?>" required></div>
      <div class="field"><label>Last name</label><input type="text" name="last_name" value="<?= e($stud['last_name']) ?>" required></div>
    </div>
    <div class="field"><label>Email</label><input type="email" name="email" value="<?= e($stud['email']) ?>" required></div>
    <div class="form-row-2">
      <div class="field"><label>Programme</label>
        <select name="programme_id">
          <?php foreach ($programmes as $p): ?><option value="<?= $p['id'] ?>" <?= $p['id']==$stud['programme_id']?'selected':'' ?>><?= e($p['name']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="field"><label>Year of study</label>
        <select name="year_of_study"><?php for ($y=1;$y<=6;$y++): ?><option value="<?= $y ?>" <?= $y==$stud['year_of_study']?'selected':'' ?>><?= $y ?></option><?php endfor; ?></select>
      </div>
    </div>
    <div class="field"><label>Status</label>
      <select name="status">
        <option value="active" <?= $stud['status']==='active'?'selected':'' ?>>Active</option>
        <option value="suspended" <?= $stud['status']==='suspended'?'selected':'' ?>>Suspended</option>
      </select>
    </div>
    <button class="pill-btn teal" type="submit">Save changes</button>
  </form>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
