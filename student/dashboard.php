<?php
require_once __DIR__ . '/../config/config.php';
require_login();

$sid = current_student_id();
$pageTitle = 'Dashboard';
$activeNav = 'dashboard';

// Stats
$hoursThisWeek = $pdo->prepare("SELECT COALESCE(SUM(TIME_TO_SEC(TIMEDIFF(end_time,start_time)))/3600,0) AS hrs
    FROM study_sessions WHERE student_id=? AND status='completed' AND YEARWEEK(session_date,1)=YEARWEEK(CURDATE(),1)");
$hoursThisWeek->execute([$sid]);
$hours = round($hoursThisWeek->fetch()['hrs'], 1);

$completed = $pdo->prepare("SELECT COUNT(*) c FROM assignments WHERE student_id=? AND status='completed'");
$completed->execute([$sid]); $completedCount = $completed->fetch()['c'];

$pending = $pdo->prepare("SELECT COUNT(*) c FROM assignments WHERE student_id=? AND status='pending'");
$pending->execute([$sid]); $pendingCount = $pending->fetch()['c'];

$avgProgress = $pdo->prepare("SELECT COALESCE(AVG(progress_percent),0) a FROM student_courses WHERE student_id=?");
$avgProgress->execute([$sid]); $overallProgress = round($avgProgress->fetch()['a']);

$deadlines = $pdo->prepare("SELECT a.*, c.name AS course_name FROM assignments a
    JOIN courses c ON c.id=a.course_id
    WHERE a.student_id=? AND a.status='pending' ORDER BY a.due_date ASC LIMIT 3");
$deadlines->execute([$sid]);
$deadlineRows = $deadlines->fetchAll();

$courseProgress = $pdo->prepare("SELECT c.name, sc.progress_percent FROM student_courses sc
    JOIN courses c ON c.id = sc.course_id WHERE sc.student_id=?");
$courseProgress->execute([$sid]);
$courseRows = $courseProgress->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
<div class="topline">
  <div><h1>Good to see you, <?= e($student['first_name']) ?> 👋</h1><div class="desc">Here's how your week is shaping up.</div></div>
  <div class="date-chip mono"><?= date('l, j F Y') ?></div>
</div>

<div class="grid-4" style="margin-bottom:18px;">
  <div class="card stat-card"><div class="label">Hours studied this week</div><div class="value"><?= e($hours) ?><small> hrs</small></div></div>
  <div class="card stat-card"><div class="label">Tasks completed</div><div class="value"><?= e($completedCount) ?></div></div>
  <div class="card stat-card"><div class="label">Tasks pending</div><div class="value"><?= e($pendingCount) ?></div></div>
  <div class="card stat-card"><div class="label">Overall course progress</div><div class="value"><?= e($overallProgress) ?><small>%</small></div></div>
</div>

<div class="grid-2">
  <div class="card">
    <div class="block-head"><h3>Upcoming deadlines</h3><a class="pill-btn ghost small" href="<?= BASE_URL ?>/student/assignments/index.php">View all</a></div>
    <?php if ($deadlineRows): foreach ($deadlineRows as $d): $dd = days_until($d['due_date']); ?>
      <div class="item-row">
        <div class="item-dot" style="background:<?= $dd<=3 ? 'var(--coral)' : 'var(--amber)' ?>"></div>
        <div style="flex:1;"><div class="title"><?= e($d['title']) ?></div><div class="meta"><?= e($d['course_name']) ?> · due <?= format_date($d['due_date']) ?></div></div>
        <span class="tag <?= $dd<=3 ? 'coral' : 'amber' ?>"><?= e($dd) ?> day<?= $dd==1?'':'s' ?></span>
      </div>
    <?php endforeach; else: ?>
      <div style="color:var(--ink-soft);font-size:13px;">Nothing due — you're all caught up.</div>
    <?php endif; ?>
  </div>
  <div class="card">
    <div class="block-head"><h3>Progress by course</h3></div>
    <?php foreach ($courseRows as $c): ?>
      <div style="margin-bottom:14px;">
        <div style="display:flex;justify-content:space-between;font-size:12.5px;margin-bottom:6px;"><span style="font-weight:600;"><?= e($c['name']) ?></span><span class="mono"><?= e($c['progress_percent']) ?>%</span></div>
        <div class="progress-track"><div class="progress-fill amber" style="width:<?= e($c['progress_percent']) ?>%;"></div></div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
