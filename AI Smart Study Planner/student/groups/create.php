<?php
require_once __DIR__ . '/../../config/config.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sid = current_student_id();
    $name = trim($_POST['name'] ?? '');
    $courseId = (int)$_POST['course_id'];
    if ($name && $courseId) {
        $pdo->prepare("INSERT INTO study_groups (course_id, name, created_by) VALUES (?, ?, ?)")->execute([$courseId, $name, $sid]);
        $groupId = $pdo->lastInsertId();
        $pdo->prepare("INSERT INTO group_members (group_id, student_id) VALUES (?, ?)")->execute([$groupId, $sid]);
        set_flash('success', 'Group created.');
        redirect(BASE_URL . '/student/groups/view.php?id=' . $groupId);
    }
}
redirect(BASE_URL . '/student/groups/index.php');
