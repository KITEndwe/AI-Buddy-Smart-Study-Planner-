<?php
require_once __DIR__ . '/../../config/config.php';
require_login();
$sid = current_student_id();
$id = (int)($_GET['id'] ?? 0);
$pdo->prepare("DELETE FROM notes WHERE id=? AND student_id=?")->execute([$id, $sid]);
set_flash('success', 'Note deleted.');
redirect(BASE_URL . '/student/notes/index.php');
