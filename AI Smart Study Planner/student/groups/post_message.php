<?php
require_once __DIR__ . '/../../config/config.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sid = current_student_id();
    $groupId = (int)$_POST['group_id'];
    $message = trim($_POST['message'] ?? '');

    if ($message) {
        $pdo->prepare("INSERT INTO group_messages (group_id, student_id, message) VALUES (?, ?, ?)")->execute([$groupId, $sid, $message]);

        // Notify every other member of the group about the new message
        $groupInfo = $pdo->prepare("SELECT g.name AS group_name, s.first_name FROM study_groups g, students s WHERE g.id=? AND s.id=?");
        $groupInfo->execute([$groupId, $sid]);
        $groupInfo = $groupInfo->fetch();

        if ($groupInfo) {
            $preview = mb_strlen($message) > 60 ? mb_substr($message, 0, 60) . '…' : $message;
            $notifyMsg = $groupInfo['first_name'] . " in \"" . $groupInfo['group_name'] . "\": " . $preview;

            $others = $pdo->prepare("SELECT student_id FROM group_members WHERE group_id=? AND student_id != ?");
            $others->execute([$groupId, $sid]);
            $insertNotif = $pdo->prepare("INSERT INTO notifications (student_id, type, message) VALUES (?, 'group_message', ?)");
            foreach ($others->fetchAll(PDO::FETCH_COLUMN) as $memberId) {
                $insertNotif->execute([$memberId, $notifyMsg]);
            }
        }
    }
    redirect(BASE_URL . '/student/groups/view.php?id=' . $groupId);
}
redirect(BASE_URL . '/student/groups/index.php');