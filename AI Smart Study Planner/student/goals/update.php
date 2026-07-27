<?php
require_once __DIR__ . '/../../config/config.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sid = current_student_id();
    $id = (int)$_POST['id'];
    $current = (float)$_POST['current_value'];
    $pdo->prepare("UPDATE goals SET current_value=? WHERE id=? AND student_id=?")->execute([$current, $id, $sid]);
}
redirect(BASE_URL . '/student/goals/index.php');
