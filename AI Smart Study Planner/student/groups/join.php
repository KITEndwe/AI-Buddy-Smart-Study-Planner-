<?php
require_once __DIR__ . '/../../config/config.php';
require_login();
$sid = current_student_id();
$groupId = (int)($_GET['id'] ?? 0);
$pdo->prepare("INSERT IGNORE INTO group_members (group_id, student_id) VALUES (?, ?)")->execute([$groupId, $sid]);
set_flash('success', 'You joined the group.');
redirect(BASE_URL . '/student/groups/view.php?id=' . $groupId);
