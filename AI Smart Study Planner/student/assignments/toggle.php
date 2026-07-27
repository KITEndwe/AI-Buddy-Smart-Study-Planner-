<?php
require_once __DIR__ . '/../../config/config.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sid = current_student_id();
    $id = (int)$_POST['id'];
    $stmt = $pdo->prepare("SELECT status FROM assignments WHERE id=? AND student_id=?");
    $stmt->execute([$id, $sid]);
    $row = $stmt->fetch();
    if ($row) {
        $newStatus = $row['status'] === 'completed' ? 'pending' : 'completed';
        $pdo->prepare("UPDATE assignments SET status=? WHERE id=? AND student_id=?")->execute([$newStatus, $id, $sid]);
    }
}
redirect(BASE_URL . '/student/assignments/index.php');
