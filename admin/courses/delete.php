<?php
require_once __DIR__ . '/../../config/config.php';
require_admin();
$id = (int)($_GET['id'] ?? 0);
$pdo->prepare("DELETE FROM courses WHERE id=?")->execute([$id]);
set_flash('success', 'Course deleted.');
redirect(BASE_URL . '/admin/courses/index.php');
