<?php
require_once __DIR__ . '/../../config/config.php';
require_login();
$sid = current_student_id();
$pageTitle = 'Notifications';
$activeNav = 'notifications';

$prefs = $pdo->prepare("SELECT * FROM notification_preferences WHERE student_id=?");
$prefs->execute([$sid]);
$prefs = $prefs->fetch();
if (!$prefs) {
    $pdo->prepare("INSERT INTO notification_preferences (student_id) VALUES (?)")->execute([$sid]);
    $prefs = ['push_enabled'=>1,'email_enabled'=>1,'deadline_alerts'=>1,'exam_countdowns'=>1];
}

$feed = $pdo->prepare("SELECT * FROM notifications WHERE student_id=? ORDER BY created_at DESC LIMIT 15");
$feed->execute([$sid]);
$feed = $feed->fetchAll();

// Viewing this page counts as "seen" — clear the unread badge in the sidebar
$pdo->prepare("UPDATE notifications SET is_read=1 WHERE student_id=? AND is_read=0")->execute([$sid]);

include __DIR__ . '/../../includes/header.php';
?>
<div class="topline"><div><h1>Notifications</h1><div class="desc">Stay ahead of deadlines without checking the app constantly.</div></div></div>
<div class="grid-2">
  <div class="card">
    <div class="block-head"><h3>Preferences</h3></div>
    <form method="post" action="<?= BASE_URL ?>/student/notifications/preferences_update.php">
      <?php
      $toggles = [
        'push_enabled' => ['Push notifications', 'Alerts sent to this device'],
        'email_enabled' => ['Email reminders', 'Daily digest to your student email'],
        'deadline_alerts' => ['Deadline alerts', '48 hours before any submission'],
        'exam_countdowns' => ['Exam countdowns', 'Daily countdown in final week'],
      ];
      foreach ($toggles as $key => [$title, $desc]): ?>
      <div class="switch-row"><div><div class="st-title"><?= e($title) ?></div><div class="st-desc"><?= e($desc) ?></div></div>
        <label class="switch"><input type="checkbox" name="<?= $key ?>" value="1" <?= !empty($prefs[$key])?'checked':'' ?>><span class="slider-toggle"></span></label>
      </div>
      <?php endforeach; ?>
      <button class="pill-btn teal" type="submit" style="margin-top:14px;">Save preferences</button>
    </form>
  </div>
  <div class="card">
    <div class="block-head"><h3>Recent</h3></div>
    <?php
    $typeIcons = ['group_invite' => '👥', 'group_message' => '💬'];
    foreach ($feed as $n): ?>
      <div class="item-row"><div style="font-size:16px;"><?= $typeIcons[$n['type']] ?? '🔔' ?></div>
        <div style="flex:1;"><div class="title" style="font-weight:500;"><?= e($n['message']) ?></div></div>
        <div class="meta mono"><?= e(date('j M, g:ia', strtotime($n['created_at']))) ?></div>
      </div>
    <?php endforeach; ?>
    <?php if (!$feed): ?><div style="color:var(--ink-soft);font-size:13px;">No notifications yet.</div><?php endif; ?>
  </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>