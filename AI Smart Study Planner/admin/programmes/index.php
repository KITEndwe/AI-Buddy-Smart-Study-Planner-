<?php
require_once __DIR__ . '/../../config/config.php';
require_admin();
$pageTitle = 'Programmes';
$activeNav = 'programmes';

$programmes = $pdo->query("SELECT p.*, (SELECT COUNT(*) FROM students s WHERE s.programme_id=p.id) AS student_count,
    (SELECT COUNT(*) FROM courses c WHERE c.programme_id=p.id) AS course_count
    FROM programmes p ORDER BY p.school, p.name")->fetchAll();

// Group by school for display, matching Cavendish University Zambia's four Schools
$grouped = [];
foreach ($programmes as $p) {
    $school = $p['school'] !== '' ? $p['school'] : 'Other programmes';
    $grouped[$school][] = $p;
}

$schoolOptions = [
    'School of Business and Information Technology',
    'School of Law',
    'School of Arts, Education and Social Sciences',
    'School of Medicine',
];

include __DIR__ . '/../../includes/admin_header.php';
?>
<div class="topline"><div><h1>Programmes</h1><div class="desc">Degree programmes students can be assigned to, grouped by School.</div></div>
  <button class="pill-btn" onclick="document.getElementById('prog-form').classList.toggle('open')">+ Add programme</button>
</div>
<div class="inline-form" id="prog-form">
  <form method="post" action="<?= BASE_URL ?>/admin/programmes/add.php">
    <div class="form-row-2">
      <div class="field"><label>Programme name</label><input type="text" name="name" placeholder="e.g. BSc Data Science" required></div>
      <div class="field"><label>Code</label><input type="text" name="code" placeholder="e.g. BSCDS" required></div>
    </div>
    <div class="field">
      <label>School</label>
      <select name="school" required>
        <option value="">Select school</option>
        <?php foreach ($schoolOptions as $s): ?>
          <option value="<?= e($s) ?>"><?= e($s) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button class="pill-btn teal" type="submit">Add programme</button>
  </form>
</div>

<?php foreach ($grouped as $school => $progs): ?>
  <h3 style="margin:22px 0 10px;font-size:15px;"><?= e($school) ?></h3>
  <div class="grid-3">
  <?php foreach ($progs as $p): ?>
    <div class="card">
      <div class="code"><?= e($p['code']) ?></div>
      <div style="font-weight:600;font-size:14.5px;margin:4px 0 12px;"><?= e($p['name']) ?></div>
      <div style="font-size:12.5px;color:var(--ink-soft);"><?= e($p['student_count']) ?> students · <?= e($p['course_count']) ?> courses</div>
      <div class="course-actions">
        <a class="pill-btn ghost small" href="<?= BASE_URL ?>/admin/students/index.php?programme_id=<?= $p['id'] ?>">View students</a>
        <a class="pill-btn ghost small" href="<?= BASE_URL ?>/admin/programmes/delete.php?id=<?= $p['id'] ?>" onclick="return confirm('Delete this programme? Students in it must be reassigned first.');">Delete</a>
      </div>
    </div>
  <?php endforeach; ?>
  </div>
<?php endforeach; ?>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
