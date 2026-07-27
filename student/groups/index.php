<?php
require_once __DIR__ . '/../../config/config.php';
require_login();
$sid = current_student_id();
$pageTitle = 'Study groups';
$activeNav = 'groups';

$courses = $pdo->prepare("SELECT c.* FROM student_courses sc JOIN courses c ON c.id=sc.course_id WHERE sc.student_id=? ORDER BY c.name");
$courses->execute([$sid]);
$courses = $courses->fetchAll();

$groups = $pdo->prepare("SELECT g.*, c.name AS course_name,
    (SELECT COUNT(*) FROM group_members gm WHERE gm.group_id=g.id) AS member_count,
    (SELECT COUNT(*) FROM group_messages gmsg WHERE gmsg.group_id=g.id) AS message_count
    FROM study_groups g
    JOIN courses c ON c.id = g.course_id
    JOIN group_members my ON my.group_id = g.id AND my.student_id = ?
    ORDER BY g.created_at DESC");
$groups->execute([$sid]);
$groups = $groups->fetchAll();

include __DIR__ . '/../../includes/header.php';
?>
<div class="topline"><div><h1>Study groups</h1><div class="desc">Team up, share notes, keep each other accountable.</div></div>
  <button class="pill-btn" onclick="document.getElementById('group-form').classList.toggle('open')">+ Create group</button>
</div>

<div class="inline-form" id="group-form">
  <form method="post" action="<?= BASE_URL ?>/student/groups/create.php">
    <div class="form-row-2">
      <div class="field"><label>Group name</label><input type="text" name="name" required></div>
      <div class="field"><label>Course</label>
        <select name="course_id" required><?php foreach ($courses as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?></select>
      </div>
    </div>
    <button class="pill-btn teal" type="submit">Create group</button>
  </form>
</div>

<div class="grid-3" style="margin-top:18px;">
<?php foreach ($groups as $g): ?>
  <a href="<?= BASE_URL ?>/student/groups/view.php?id=<?= $g['id'] ?>" class="card group-card" style="text-decoration:none;color:inherit;display:block;">
    <div style="font-weight:600;font-size:14.5px;margin-bottom:3px;"><?= e($g['name']) ?></div>
    <div style="font-size:12px;color:var(--ink-soft);"><?= e($g['course_name']) ?></div>
    <div style="margin-top:12px;font-size:12px;color:var(--ink-soft);"><?= e($g['member_count']) ?> members · <?= e($g['message_count']) ?> messages</div>
  </a>
<?php endforeach; ?>
<?php if (!$groups): ?><div style="color:var(--ink-soft);font-size:13px;">You haven't joined any groups yet.</div><?php endif; ?>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
