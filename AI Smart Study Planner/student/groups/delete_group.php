<?php
require_once __DIR__ . '/../../config/config.php';
require_login();
$sid = current_student_id();
$groupId = (int)($_GET['id'] ?? 0);

// Only the student who created the group can delete it.
// Cascading foreign keys on group_members, group_notes and group_messages
// take care of cleaning those up automatically.
$stmt = $pdo->prepare("DELETE FROM study_groups WHERE id=? AND created_by=?");
$stmt->execute([$groupId, $sid]);

if ($stmt->rowCount() > 0) {
    set_flash('success', 'Group deleted.');
    redirect(BASE_URL . '/student/groups/index.php');
} else {
    set_flash('error', 'Only the person who created this group can delete it.');
    redirect(BASE_URL . '/student/groups/view.php?id=' . $groupId);
}