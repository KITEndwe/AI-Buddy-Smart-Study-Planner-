<?php
require_once __DIR__ . '/../../config/config.php';
require_admin();
$pageTitle = 'Courses';
$activeNav = 'courses';

$programmes = $pdo->query("SELECT * FROM programmes ORDER BY name")->fetchAll();
$courses = $pdo->query("SELECT c.*, p.name AS programme_name FROM courses c JOIN programmes p ON p.id=c.programme_id ORDER BY p.name, c.name")->fetchAll();

include __DIR__ . '/../../includes/admin_header.php';
?>
<div class="topline"><div><h1>Courses</h1><div class="desc">Courses offered under each programme.</div></div>
  <button class="pill-btn" onclick="document.getElementById('course-form').classList.toggle('open')">+ Add course</button>
</div>
<div class="inline-form" id="course-form">
  <form method="post" action="<?= BASE_URL ?>/admin/courses/add.php">
    <div class="form-row-2">
      <div class="field"><label>Course name</label><input type="text" name="name" required></div>
      <div class="field"><label>Course code</label><input type="text" name="code" required></div>
    </div>
    <div class="form-row-2">
      <div class="field"><label>Programme</label>
        <select name="programme_id" required><?php foreach ($programmes as $p): ?><option value="<?= $p['id'] ?>"><?= e($p['name']) ?></option><?php endforeach; ?></select>
      </div>
      <div class="field"><label>Credit hours</label><input type="number" name="credit_hours" value="3" min="1" max="6"></div>
    </div>
    <button class="pill-btn teal" type="submit">Add course</button>
  </form>
</div>
<div class="card" style="margin-top:18px;">
  <table>
    <thead><tr><th>Code</th><th>Course</th><th>Programme</th><th>Credits</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($courses as $c): ?>
      <tr>
        <td class="mono"><?= e($c['code']) ?></td>
        <td class="title"><?= e($c['name']) ?></td>
        <td><?= e($c['programme_name']) ?></td>
        <td><?= e($c['credit_hours']) ?></td>
        <td><a href="<?= BASE_URL ?>/admin/courses/delete.php?id=<?= $c['id'] ?>" onclick="return confirm('Delete this course?');" style="color:var(--coral);font-size:12px;">Delete</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
