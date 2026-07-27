<?php
require_once __DIR__ . '/../../config/config.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sid = current_student_id();
    $title = trim($_POST['title'] ?? '');
    $courseId = (int)$_POST['course_id'];
    $due = $_POST['due_date'] ?? '';
    if ($title && $courseId && $due) {
        $stmt = $pdo->prepare("INSERT INTO assignments (student_id, course_id, title, due_date) VALUES (?, ?, ?, ?)");
        $stmt->execute([$sid, $courseId, $title, $due]);
        $pdo->prepare("INSERT INTO notifications (student_id, type, message) VALUES (?, 'assignment', ?)")
            ->execute([$sid, "New assignment added: {$title}"]);
        set_flash('success', 'Assignment added.');
    }
}
redirect(BASE_URL . '/student/assignments/index.php');
