<?php
require_once __DIR__ . '/../../config/config.php';
require_admin();
$id = (int)($_GET['id'] ?? 0);
$pdo->prepare("DELETE FROM students WHERE id=?")->execute([$id]);
set_flash('success', 'Student removed.');
redirect(BASE_URL . '/admin/students/index.php');
