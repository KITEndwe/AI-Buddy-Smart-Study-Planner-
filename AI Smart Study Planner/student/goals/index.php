<?php
require_once __DIR__ . '/../../config/config.php';
require_login();
$sid = current_student_id();
$pageTitle = 'Goal tracking';
$activeNav = 'goals';
$tab = $_GET['tab'] ?? 'weekly';

$stmt = $pdo->prepare("SELECT * FROM goals WHERE student_id=? AND goal_type=? ORDER BY created_at DESC");
$stmt->execute([$sid, $tab]);
$goals = $stmt->fetchAll();

include __DIR__ . '/../../includes/header.php';
?>
<div class="topline"><div><h1>Goal tracking</h1><div class="desc">Weekly, monthly, and semester targets.</div></div>
  <button class="pill-btn" onclick="document.getElementById('goal-form').classList.toggle('open')">+ Add goal</button>
</div>

<div class="inline-form" id="goal-form">
  <form method="post" action="<?= BASE_URL ?>/student/goals/add.php">
    <div class="form-row-2">
      <div class="field"><label>Goal</label><input type="text" name="title" required></div>
      <div class="field"><label>Type</label>
        <select name="goal_type"><option value="weekly">Weekly</option><option value="monthly">Monthly</option><option value="semester">Semester</option></select>
      </div>
    </div>
    <div class="form-row-2">
      <div class="field"><label>Target</label><input type="number" step="0.1" name="target_value" required></div>
      <div class="field"><label>Unit</label><input type="text" name="unit" placeholder="hrs / quizzes / %"></div>
    </div>
    <button class="pill-btn teal" type="submit">Add goal</button>
  </form>
</div>

<div class="subtabs">
  <a href="?tab=weekly" class="<?= $tab==='weekly'?'active':'' ?>" style="text-decoration:none;">Weekly</a>
  <a href="?tab=monthly" class="<?= $tab==='monthly'?'active':'' ?>" style="text-decoration:none;">Monthly</a>
  <a href="?tab=semester" class="<?= $tab==='semester'?'active':'' ?>" style="text-decoration:none;">Semester</a>
</div>

<div class="card">
<?php foreach ($goals as $g): $pct = $g['target_value']>0 ? min(100, round(($g['current_value']/$g['target_value'])*100)) : 0; ?>
  <div class="goal-card">
    <div class="goal-top"><span class="g-title"><?= e($g['title']) ?></span><span class="g-val"><?= e($g['current_value']) ?>/<?= e($g['target_value']) ?> <?= e($g['unit']) ?></span></div>
    <div class="progress-track"><div class="progress-fill" style="width:<?= $pct ?>%;"></div></div>
    <form method="post" action="<?= BASE_URL ?>/student/goals/update.php" style="display:flex;gap:8px;margin-top:8px;">
      <input type="hidden" name="id" value="<?= $g['id'] ?>">
      <input type="number" step="0.1" name="current_value" value="<?= e($g['current_value']) ?>" style="width:90px;padding:6px;border:1px solid var(--line);border-radius:6px;">
      <button class="pill-btn ghost small" type="submit">Update progress</button>
    </form>
  </div>
<?php endforeach; ?>
<?php if (!$goals): ?><div style="color:var(--ink-soft);font-size:13px;">No <?= e($tab) ?> goals yet.</div><?php endif; ?>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
