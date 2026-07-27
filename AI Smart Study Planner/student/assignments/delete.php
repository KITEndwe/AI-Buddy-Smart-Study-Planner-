<?php
require_once __DIR__ . '/../../config/config.php';
require_login();
$sid = current_student_id();
$id = (int)($_GET['id'] ?? 0);
$pdo->prepare("DELETE FROM assignments WHERE id=? AND student_id=?")->execute([$id, $sid]);
set_flash('success', 'Assignment deleted.');
redirect(BASE_URL . '/student/assignments/index.php');
