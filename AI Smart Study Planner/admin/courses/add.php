<?php
require_once __DIR__ . '/../../config/config.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $code = strtoupper(trim($_POST['code'] ?? ''));
    $programmeId = (int)$_POST['programme_id'];
    $credits = (int)($_POST['credit_hours'] ?? 3);
    if ($name && $code && $programmeId) {
        $pdo->prepare("INSERT INTO courses (programme_id, name, code, credit_hours) VALUES (?, ?, ?, ?)")
            ->execute([$programmeId, $name, $code, $credits]);
        set_flash('success', 'Course added.');
    }
}
redirect(BASE_URL . '/admin/courses/index.php');
