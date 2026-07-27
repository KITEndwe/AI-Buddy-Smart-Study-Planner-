<?php
require_once __DIR__ . '/../../config/config.php';
require_login();
$sid = current_student_id();
$courseId = (int)($_GET['course_id'] ?? 0);
$stmt = $pdo->prepare("DELETE FROM student_courses WHERE student_id=? AND course_id=?");
$stmt->execute([$sid, $courseId]);
set_flash('success', 'Course removed.');
redirect(BASE_URL . '/student/courses/index.php');
