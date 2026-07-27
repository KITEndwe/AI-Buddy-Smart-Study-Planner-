<?php
require_once __DIR__ . '/../../config/config.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sid = current_student_id();
    $courseId = (int)$_POST['course_id'];
    $progress = max(0, min(100, (int)$_POST['progress_percent']));
    $stmt = $pdo->prepare("UPDATE student_courses SET progress_percent=? WHERE student_id=? AND course_id=?");
    $stmt->execute([$progress, $sid, $courseId]);
    set_flash('success', 'Progress updated.');
}
redirect(BASE_URL . '/student/courses/index.php');
