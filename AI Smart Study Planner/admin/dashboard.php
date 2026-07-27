<?php
require_once __DIR__ . '/../config/config.php';
require_admin();
$pageTitle = 'Admin overview';
$activeNav = 'dashboard';

$totalStudents = $pdo->query("SELECT COUNT(*) c FROM students")->fetch()['c'];
$totalProgrammes = $pdo->query("SELECT COUNT(*) c FROM programmes")->fetch()['c'];
$totalCourses = $pdo->query("SELECT COUNT(*) c FROM courses")->fetch()['c'];
$totalAssignments = $pdo->query("SELECT COUNT(*) c FROM assignments")->fetch()['c'];

$byProgramme = $pdo->query("SELECT p.name, COUNT(s.id) AS student_count FROM programmes p
    LEFT JOIN students s ON s.programme_id = p.id GROUP BY p.id ORDER BY p.name")->fetchAll();

$recentStudents = $pdo->query("SELECT s.*, p.name AS programme_name FROM students s
    JOIN programmes p ON p.id=s.programme_id ORDER BY s.created_at DESC LIMIT 6")->fetchAll();

include __DIR__ . '/../includes/admin_header.php';
?>
<div class="topline"><div><h1>Admin overview</h1><div class="desc">Cavendish University Zambia · Smart Buddy console</div></div></div>

<div class="grid-4" style="margin-bottom:18px;">
  <div class="card stat-card"><div class="label">Total students</div><div class="value"><?= e($totalStudents) ?></div></div>
  <div class="card stat-card"><div class="label">Programmes</div><div class="value"><?= e($totalProgrammes) ?></div></div>
  <div class="card stat-card"><div class="label">Courses</div><div class="value"><?= e($totalCourses) ?></div></div>
  <div class="card stat-card"><div class="label">Assignments logged</div><div class="value"><?= e($totalAssignments) ?></div></div>
</div>

<div class="grid-2">
  <div class="card">
    <div class="block-head"><h3>Students by programme</h3></div>
    <?php foreach ($byProgramme as $row): $pct = $totalStudents ? round($row['student_count']/$totalStudents*100) : 0; ?>
      <div style="margin-bottom:14px;">
        <div style="display:flex;justify-content:space-between;font-size:12.5px;margin-bottom:6px;"><span style="font-weight:600;"><?= e($row['name']) ?></span><span class="mono"><?= e($row['student_count']) ?> students</span></div>
        <div class="progress-track"><div class="progress-fill amber" style="width:<?= $pct ?>%;"></div></div>
      </div>
    <?php endforeach; ?>
  </div>
  <div class="card">
    <div class="block-head"><h3>Recently added students</h3><a class="pill-btn ghost small" href="<?= BASE_URL ?>/admin/students/index.php">View all</a></div>
    <?php foreach ($recentStudents as $s): ?>
      <div class="item-row"><div class="item-dot" style="background:var(--teal);"></div>
        <div style="flex:1;"><div class="title"><?= e($s['first_name'].' '.$s['last_name']) ?></div><div class="meta"><?= e($s['programme_name']) ?> · <?= e($s['email']) ?></div></div>
        <?php if ($s['added_by_admin']): ?><span class="tag navy">Admin added</span><?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
