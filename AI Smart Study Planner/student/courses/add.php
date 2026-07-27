<?php
require_once __DIR__ . '/../../config/config.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sid = current_student_id();
    $courseId = (int)$_POST['course_id'];
    $stmt = $pdo->prepare("INSERT IGNORE INTO student_courses (student_id, course_id, progress_percent) VALUES (?, ?, 0)");
    $stmt->execute([$sid, $courseId]);
    set_flash('success', 'Course added.');
}
redirect(BASE_URL . '/student/courses/index.php');
