<?php
require_once __DIR__ . '/../../config/config.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sid = current_student_id();
    $first = trim($_POST['first_name'] ?? '');
    $last = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $year = (int)($_POST['year_of_study'] ?? 1);
    if ($first && $last && $email) {
        $stmt = $pdo->prepare("UPDATE students SET first_name=?, last_name=?, email=?, year_of_study=? WHERE id=?");
        $stmt->execute([$first, $last, $email, $year, $sid]);
        set_flash('success', 'Profile updated.');
    }
}
redirect(BASE_URL . '/student/profile/index.php');
