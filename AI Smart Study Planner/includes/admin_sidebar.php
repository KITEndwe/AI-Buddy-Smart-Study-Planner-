<?php
$adminNav = [
    'dashboard'  => ['label' => 'Overview',            'href' => '/admin/dashboard.php'],
    'students'   => ['label' => 'Students',            'href' => '/admin/students/index.php'],
    'programmes' => ['label' => 'Programmes',          'href' => '/admin/programmes/index.php'],
    'courses'    => ['label' => 'Courses',             'href' => '/admin/courses/index.php'],
];
?>
<aside class="sidebar">
  <div class="sidebar-brand">
    <span class="sundial"><img width="40px" style="border-radius: 10px;" src="<?= BASE_URL ?>/assets/images/cavendish-university-zambia.jpg" alt="Cavendish University Zambia"></span>
    <div class="name">Smart Buddy<small>Admin console</small></div>
  </div>
  <nav class="nav-group">
    <?php foreach ($adminNav as $key => $item): ?>
      <a class="nav-item <?= ($activeNav ?? '') === $key ? 'active' : '' ?>" href="<?= BASE_URL . $item['href'] ?>">
        <?= e($item['label']) ?>
      </a>
    <?php endforeach; ?>
  </nav>
  <div class="sidebar-foot">
    <div class="avatar-circle">A</div>
    <div class="who"><b><?= e($_SESSION['admin_name'] ?? 'Admin') ?></b><span>Administrator</span></div>
    <a class="logout-btn" href="<?= BASE_URL ?>/admin/logout.php" title="Log out">⏻</a>
  </div>
</aside>