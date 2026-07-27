<?php
// Exams are shared per course (added by an admin/lecturer in a full deployment).
// Kept here so the schema/feature is complete; wire this up to an admin-only form if needed.
require_once __DIR__ . '/../../config/config.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courseId = (int)$_POST['course_id'];
    $date = $_POST['exam_date'] ?? '';
    $venue = trim($_POST['venue'] ?? '');
    if ($courseId && $date) {
        $pdo->prepare("INSERT INTO exams (course_id, exam_date, venue) VALUES (?, ?, ?)")->execute([$courseId, $date, $venue]);
        set_flash('success', 'Exam added.');
    }
}
redirect(BASE_URL . '/student/assignments/index.php?tab=exams');
