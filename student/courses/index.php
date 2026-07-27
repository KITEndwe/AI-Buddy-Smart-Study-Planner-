<?php
require_once __DIR__ . '/../../config/config.php';
require_login();
$sid = current_student_id();
$pageTitle = 'Courses';
$activeNav = 'courses';

$stmt = $pdo->prepare("SELECT c.*, sc.progress_percent FROM student_courses sc
    JOIN courses c ON c.id = sc.course_id WHERE sc.student_id=? ORDER BY c.name");
$stmt->execute([$sid]);
$courses = $stmt->fetchAll();

// courses in the student's programme not yet enrolled in, for the add form
$avail = $pdo->prepare("SELECT c.* FROM courses c
    JOIN students s ON s.programme_id = c.programme_id
    WHERE s.id = ? AND c.id NOT IN (SELECT course_id FROM student_courses WHERE student_id=?)");
$avail->execute([$sid, $sid]);
$availableCourses = $avail->fetchAll();

include __DIR__ . '/../../includes/header.php';
?>
<div class="topline">
  <div><h1>Course management</h1><div class="desc">Every course you're taking this semester.</div></div>
  <button class="pill-btn" onclick="document.getElementById('course-form').classList.toggle('open')">+ Add course</button>
</div>

<div class="inline-form" id="course-form">
  <form method="post" action="<?= BASE_URL ?>/student/courses/add.php">
    <div class="field"><label>Course</label>
      <select name="course_id" required>
        <?php foreach ($availableCourses as $c): ?>
          <option value="<?= $c['id'] ?>"><?= e($c['name']) ?> (<?= e($c['code']) ?>)</option>
        <?php endforeach; ?>
      </select>
    </div>
    <button class="pill-btn teal" type="submit">Enrol</button>
  </form>
</div>

<div class="grid-3" style="margin-top:18px;">
<?php foreach ($courses as $c): ?>
  <div class="card course-card">
    <div class="code"><?= e($c['code']) ?></div>
    <div style="font-weight:600;font-size:14.5px;margin:4px 0 12px;"><?= e($c['name']) ?></div>
    <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:6px;"><span style="color:var(--ink-soft);">Progress</span><span class="mono"><?= e($c['progress_percent']) ?>%</span></div>
    <div class="progress-track"><div class="progress-fill amber" style="width:<?= e($c['progress_percent']) ?>%;"></div></div>
    <form method="post" action="<?= BASE_URL ?>/student/courses/edit.php" style="margin-top:14px;display:flex;gap:8px;">
      <input type="hidden" name="course_id" value="<?= $c['id'] ?>">
      <input type="number" name="progress_percent" min="0" max="100" value="<?= e($c['progress_percent']) ?>" style="width:70px;padding:6px;border:1px solid var(--line);border-radius:6px;">
      <button class="pill-btn ghost small" type="submit">Update %</button>
      <a class="pill-btn ghost small" href="<?= BASE_URL ?>/student/courses/delete.php?course_id=<?= $c['id'] ?>" onclick="return confirm('Remove this course from your list?');">Remove</a>
    </form>
  </div>
<?php endforeach; ?>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
