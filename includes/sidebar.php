<?php
$navItems = [
    'dashboard'     => ['label' => 'Dashboard',            'href' => '/student/dashboard.php'],
    'groups'        => ['label' => 'Study groups',         'href' => '/student/groups/index.php'],
    'assignments'   => ['label' => 'Assignments & exams',  'href' => '/student/assignments/index.php'],
    'planner'       => ['label' => 'AI study planner',     'href' => '/student/planner/index.php'],
    'buddy'         => ['label' => 'AI study buddy',       'href' => '/student/buddy/index.php'],
    'goals'         => ['label' => 'Goal tracking',        'href' => '/student/goals/index.php'],
    'courses'       => ['label' => 'Courses',               'href' => '/student/courses/index.php'],
    'notes'         => ['label' => 'Notes',                 'href' => '/student/notes/index.php'],
    'notifications' => ['label' => 'Notifications',         'href' => '/student/notifications/index.php'],
    'profile'       => ['label' => 'Profile',                'href' => '/student/profile/index.php'],
];

$unreadCount = 0;
if (function_exists('current_student_id') && current_student_id()) {
    $unreadStmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE student_id=? AND is_read=0");
    $unreadStmt->execute([current_student_id()]);
    $unreadCount = (int)$unreadStmt->fetchColumn();
}
?>
<aside class="sidebar">
  <div class="sidebar-brand">
    <span class="sundial"><img width="40px" style="border-radius: 10px;" src="<?= BASE_URL ?>/assets/images/cavendish-university-zambia.jpg" alt="Cavendish University Zambia"></span>
    <div class="name">Smart Buddy<small>Cavendish University Zambia</small></div>
  </div>
  <nav class="nav-group">
    <?php foreach ($navItems as $key => $item): ?>
      <a class="nav-item <?= ($activeNav ?? '') === $key ? 'active' : '' ?>" href="<?= BASE_URL . $item['href'] ?>">
        <span><?= e($item['label']) ?></span>
        <?php if ($key === 'notifications' && $unreadCount > 0): ?>
          <span class="notif-badge"><?= $unreadCount > 9 ? '9+' : $unreadCount ?></span>
        <?php endif; ?>
      </a>
    <?php endforeach; ?>
  </nav>
  <div class="sidebar-foot">
    <div class="avatar-circle"><?= e(initials($student['first_name'] ?? 'S', $student['last_name'] ?? 'B')) ?></div>
    <div class="who">
      <b><?= e(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? '')) ?></b>
      <span><?= e($student['programme_name'] ?? '') ?></span>
    </div>
    <a class="logout-btn" href="<?= BASE_URL ?>/student/auth/logout.php" title="Log out">⏻</a>
  </div>
</aside>