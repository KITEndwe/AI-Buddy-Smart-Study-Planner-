<?php
require_once __DIR__ . '/../../config/config.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sid = current_student_id();
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $stmt = $pdo->prepare("SELECT password_hash FROM students WHERE id=?");
    $stmt->execute([$sid]);
    $row = $stmt->fetch();

    if (!$row || !password_verify($current, $row['password_hash'])) {
        set_flash('error', 'Current password is incorrect.');
    } elseif (strlen($new) < 8) {
        set_flash('error', 'New password must be at least 8 characters.');
    } elseif ($new !== $confirm) {
        set_flash('error', 'New passwords do not match.');
    } else {
        $hash = password_hash($new, PASSWORD_BCRYPT);
        $pdo->prepare("UPDATE students SET password_hash=? WHERE id=?")->execute([$hash, $sid]);
        set_flash('success', 'Password updated.');
    }
}
redirect(BASE_URL . '/student/profile/index.php');
