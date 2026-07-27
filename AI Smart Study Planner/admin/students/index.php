<?php
require_once __DIR__ . '/../../config/config.php';
require_admin();
$pageTitle = 'Students';
$activeNav = 'students';

$programmeFilter = $_GET['programme_id'] ?? 'all';
$search = trim($_GET['q'] ?? '');

$programmes = $pdo->query("SELECT * FROM programmes ORDER BY name")->fetchAll();

$sql = "SELECT s.*, p.name AS programme_name FROM students s JOIN programmes p ON p.id = s.programme_id WHERE 1=1";
$params = [];
if ($programmeFilter !== 'all') { $sql .= " AND s.programme_id = ?"; $params[] = (int)$programmeFilter; }
if ($search !== '') { $sql .= " AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.email LIKE ?)"; $params = array_merge($params, ["%$search%","%$search%","%$search%"]); }
$sql .= " ORDER BY s.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();

include __DIR__ . '/../../includes/admin_header.php';
?>
<div class="topline">
  <div><h1>Students</h1><div class="desc">All registered students, filterable by programme.</div></div>
  <a class="pill-btn" href="<?= BASE_URL ?>/admin/students/add.php">+ Add student</a>
</div>

<div class="card" style="margin-bottom:18px;">
  <form method="get" class="form-row-2" style="align-items:flex-end;">
    <div class="field" style="margin-bottom:0;">
      <label>Programme</label>
      <select name="programme_id" onchange="this.form.submit()">
        <option value="all" <?= $programmeFilter==='all'?'selected':'' ?>>All programmes</option>
        <?php foreach ($programmes as $p): ?>
          <option value="<?= $p['id'] ?>" <?= (string)$programmeFilter===(string)$p['id']?'selected':'' ?>><?= e($p['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="field" style="margin-bottom:0;">
      <label>Search</label>
      <input type="text" name="q" value="<?= e($search) ?>" placeholder="Name or email…">
    </div>
    <button class="pill-btn ghost small" type="submit" style="margin-bottom:16px;">Filter</button>
  </form>
</div>

<div class="card">
  <table>
    <thead><tr><th>Student</th><th>Email</th><th>Programme</th><th>Year</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($students as $s): ?>
      <tr>
        <td class="title"><?= e($s['first_name'].' '.$s['last_name']) ?> <?= $s['added_by_admin'] ? '<span class="tag navy" style="margin-left:6px;">Admin added</span>' : '' ?></td>
        <td><?= e($s['email']) ?></td>
        <td><?= e($s['programme_name']) ?></td>
        <td><?= e($s['year_of_study']) ?></td>
        <td><span class="tag <?= $s['status']==='active'?'teal':'coral' ?>"><?= e(ucfirst($s['status'])) ?></span></td>
        <td style="display:flex;gap:10px;">
          <a href="<?= BASE_URL ?>/admin/students/edit.php?id=<?= $s['id'] ?>" style="font-size:12px;color:var(--ink-soft);">Edit</a>
          <a href="<?= BASE_URL ?>/admin/students/delete.php?id=<?= $s['id'] ?>" onclick="return confirm('Remove this student account?');" style="font-size:12px;color:var(--coral);">Remove</a>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$students): ?><tr><td colspan="6" style="color:var(--ink-soft);">No students match.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
