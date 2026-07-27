<?php
require_once __DIR__ . '/../../config/config.php';
require_login();
$sid = current_student_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $groupId = (int)$_POST['group_id'];
    $inviteeId = (int)$_POST['student_id'];

    // Only existing members can invite others
    $isMember = $pdo->prepare("SELECT 1 FROM group_members WHERE group_id=? AND student_id=?");
    $isMember->execute([$groupId, $sid]);
    if (!$isMember->fetch()) {
        set_flash('error', 'You must be a member of this group to add others.');
        redirect(BASE_URL . '/student/groups/view.php?id=' . $groupId);
    }

    // Only students taking the group's course can be added, to keep groups relevant
    $eligible = $pdo->prepare("SELECT 1 FROM study_groups g
        JOIN student_courses sc ON sc.course_id = g.course_id AND sc.student_id = ?
        WHERE g.id = ?");
    $eligible->execute([$inviteeId, $groupId]);

    if ($eligible->fetch()) {
        $pdo->prepare("INSERT IGNORE INTO group_members (group_id, student_id) VALUES (?, ?)")->execute([$groupId, $inviteeId]);

        // Notify the invitee they've been added to the group
        $groupInfo = $pdo->prepare("SELECT g.name AS group_name, s.first_name FROM study_groups g, students s WHERE g.id=? AND s.id=?");
        $groupInfo->execute([$groupId, $sid]);
        $groupInfo = $groupInfo->fetch();
        if ($groupInfo) {
            $msg = $groupInfo['first_name'] . " added you to the study group \"" . $groupInfo['group_name'] . "\".";
            $pdo->prepare("INSERT INTO notifications (student_id, type, message) VALUES (?, 'group_invite', ?)")
                ->execute([$inviteeId, $msg]);
        }

        set_flash('success', 'Student added to the group.');
    } else {
        set_flash('error', "That student isn't enrolled in this group's course.");
    }

    redirect(BASE_URL . '/student/groups/view.php?id=' . $groupId);
}
redirect(BASE_URL . '/student/groups/index.php');