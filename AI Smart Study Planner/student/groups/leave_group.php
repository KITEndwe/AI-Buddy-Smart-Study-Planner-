<?php
require_once __DIR__ . '/../../config/config.php';
require_login();
$sid = current_student_id();
$groupId = (int)($_GET['id'] ?? 0);

// The creator can't "leave" their own group — they'd have no way back in
// since only members can add members. They can delete it instead.
$group = $pdo->prepare("SELECT created_by FROM study_groups WHERE id=?");
$group->execute([$groupId]);
$group = $group->fetch();

if ($group && (int)$group['created_by'] === $sid) {
    set_flash('error', "You created this group, so you can't leave it — you can delete it instead if you no longer want it.");
    redirect(BASE_URL . '/student/groups/view.php?id=' . $groupId);
}

$pdo->prepare("DELETE FROM group_members WHERE group_id=? AND student_id=?")->execute([$groupId, $sid]);
set_flash('success', 'You left the group.');
redirect(BASE_URL . '/student/groups/index.php');