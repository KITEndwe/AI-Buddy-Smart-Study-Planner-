<?php
require_once __DIR__ . '/../../config/config.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $code = strtoupper(trim($_POST['code'] ?? ''));
    $school = trim($_POST['school'] ?? '');
    if ($name && $code) {
        try {
            $pdo->prepare("INSERT INTO programmes (name, code, school) VALUES (?, ?, ?)")->execute([$name, $code, $school]);
            set_flash('success', 'Programme added.');
        } catch (PDOException $e) {
            set_flash('error', 'That programme code already exists.');
        }
    }
}
redirect(BASE_URL . '/admin/programmes/index.php');
