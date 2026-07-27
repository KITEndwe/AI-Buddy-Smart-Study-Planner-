<?php
require_once __DIR__ . '/../../config/config.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sid = current_student_id();
    $title = trim($_POST['title'] ?? '');
    $type = in_array($_POST['goal_type'] ?? '', ['weekly','monthly','semester']) ? $_POST['goal_type'] : 'weekly';
    $target = (float)($_POST['target_value'] ?? 0);
    $unit = trim($_POST['unit'] ?? 'units') ?: 'units';
    if ($title && $target > 0) {
        $pdo->prepare("INSERT INTO goals (student_id, goal_type, title, target_value, current_value, unit) VALUES (?, ?, ?, ?, 0, ?)")
            ->execute([$sid, $type, $title, $target, $unit]);
        set_flash('success', 'Goal added.');
    }
}
redirect(BASE_URL . '/student/goals/index.php');
