<?php
require_once __DIR__ . '/../../config/config.php';
require_admin();
$id = (int)($_GET['id'] ?? 0);
$count = $pdo->prepare("SELECT COUNT(*) c FROM students WHERE programme_id=?");
$count->execute([$id]);
if ($count->fetch()['c'] > 0) {
    set_flash('error', 'This programme still has students assigned to it — reassign them first.');
} else {
    $pdo->prepare("DELETE FROM programmes WHERE id=?")->execute([$id]);
    set_flash('success', 'Programme deleted.');
}
redirect(BASE_URL . '/admin/programmes/index.php');
