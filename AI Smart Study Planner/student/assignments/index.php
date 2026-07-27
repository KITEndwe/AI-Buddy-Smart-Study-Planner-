<?php
require_once __DIR__ . '/../../config/config.php';
require_login();
$sid = current_student_id();
$pageTitle = 'Assignments & exams';
$activeNav = 'assignments';
$tab = $_GET['tab'] ?? 'assignments';

$courses = $pdo->prepare("SELECT c.* FROM student_courses sc JOIN courses c ON c.id=sc.course_id WHERE sc.student_id=? ORDER BY c.name");
$courses->execute([$sid]);
$courses = $courses->fetchAll();

$assignments = $pdo->prepare("SELECT a.*, c.name AS course_name FROM assignments a
    JOIN courses c ON c.id=a.course_id WHERE a.student_id=? ORDER BY a.due_date ASC");
$assignments->execute([$sid]);
$assignments = $assignments->fetchAll();

$exams = $pdo->prepare("SELECT e.*, c.name AS course_name FROM exams e
    JOIN courses c ON c.id=e.course_id
    JOIN student_courses sc ON sc.course_id = c.id
    WHERE sc.student_id=? ORDER BY e.exam_date ASC");
$exams->execute([$sid]);
$exams = $exams->fetchAll();

$sessions = $pdo->prepare("SELECT ss.*, c.name AS course_name FROM study_sessions ss
    JOIN courses c ON c.id=ss.course_id WHERE ss.student_id=? ORDER BY ss.session_date ASC, ss.start_time ASC");
$sessions->execute([$sid]);
$sessions = $sessions->fetchAll();

include __DIR__ . '/../../includes/header.php';
?>
<div class="topline"><div><h1>Assignments &amp; exams</h1><div class="desc">Every deadline, in one place.</div></div></div>

<div class="subtabs">
  <a href="?tab=assignments" class="<?= $tab==='assignments'?'active':'' ?>" style="text-decoration:none;">Assignments</a>
  <a href="?tab=exams" class="<?= $tab==='exams'?'active':'' ?>" style="text-decoration:none;">Exams</a>
  <a href="?tab=sessions" class="<?= $tab==='sessions'?'active':'' ?>" style="text-decoration:none;">Study sessions</a>
</div>

<?php if ($tab === 'assignments'): ?>
  <div class="card">
    <div class="block-head"><h3>Assignments</h3><button class="pill-btn small" onclick="document.getElementById('assignment-form').classList.toggle('open')">+ Add assignment</button></div>
    <div class="inline-form" id="assignment-form">
      <form method="post" action="<?= BASE_URL ?>/student/assignments/add.php">
        <div class="form-row-2">
          <div class="field"><label>Title</label><input type="text" name="title" required></div>
          <div class="field"><label>Course</label>
            <select name="course_id" required><?php foreach ($courses as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?></select>
          </div>
        </div>
        <div class="field"><label>Submission deadline</label><input type="date" name="due_date" required></div>
        <button class="pill-btn teal" type="submit">Add assignment</button>
      </form>
    </div>
    <table><thead><tr><th></th><th>Assignment</th><th>Course</th><th>Due</th><th>Status</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($assignments as $a): $dd = days_until($a['due_date']); ?>
        <tr class="<?= $a['status']==='completed' ? 'done' : '' ?>">
          <td>
            <form method="post" action="<?= BASE_URL ?>/student/assignments/toggle.php">
              <input type="hidden" name="id" value="<?= $a['id'] ?>">
              <input type="checkbox" class="checkbox" onchange="this.form.submit()" <?= $a['status']==='completed'?'checked':'' ?>>
            </form>
          </td>
          <td class="title"><?= e($a['title']) ?></td>
          <td><?= e($a['course_name']) ?></td>
          <td class="mono"><?= format_date($a['due_date']) ?></td>
          <td><?php if ($a['status']==='completed'): ?><span class="tag teal">Done</span><?php elseif ($dd<=3): ?><span class="tag coral">Due soon</span><?php else: ?><span class="tag amber">Pending</span><?php endif; ?></td>
          <td><a href="<?= BASE_URL ?>/student/assignments/delete.php?id=<?= $a['id'] ?>" onclick="return confirm('Delete this assignment?');" style="color:var(--ink-faint);font-size:12px;">Delete</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php elseif ($tab === 'exams'): ?>
  <div class="card">
    <div class="block-head"><h3>Exam schedule</h3></div>
    <table><thead><tr><th>Course</th><th>Exam date</th><th>Venue</th><th>Countdown</th></tr></thead>
      <tbody>
      <?php foreach ($exams as $ex): $dd = days_until($ex['exam_date']); ?>
        <tr><td><?= e($ex['course_name']) ?></td><td class="mono"><?= format_date($ex['exam_date']) ?></td><td><?= e($ex['venue']) ?></td>
          <td><span class="tag <?= $dd<=7?'coral':'amber' ?>"><?= e($dd) ?> days</span></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php else: ?>
  <div class="card">
    <div class="block-head"><h3>Planned study sessions</h3></div>
    <?php foreach ($sessions as $s): ?>
      <div class="item-row"><div class="item-dot" style="background:var(--teal);"></div>
        <div style="flex:1;"><div class="title"><?= e($s['focus_topic']) ?></div><div class="meta"><?= e($s['course_name']) ?> · <?= format_date($s['session_date']) ?>, <?= substr($s['start_time'],0,5) ?>–<?= substr($s['end_time'],0,5) ?></div></div>
        <span class="tag <?= $s['source']==='ai_planner'?'teal':'amber' ?>"><?= $s['source']==='ai_planner'?'AI planner':'Manual' ?></span>
      </div>
    <?php endforeach; ?>
    <?php if (!$sessions): ?><div style="color:var(--ink-soft);font-size:13px;">No sessions yet — generate a plan from the AI study planner.</div><?php endif; ?>
  </div>
<?php endif; ?>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
