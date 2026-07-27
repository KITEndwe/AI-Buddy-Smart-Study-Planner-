<?php
require_once __DIR__ . '/../../config/config.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sid = current_student_id();
    $fields = ['push_enabled','email_enabled','deadline_alerts','exam_countdowns'];
    $values = [];
    foreach ($fields as $f) { $values[$f] = isset($_POST[$f]) ? 1 : 0; }
    $stmt = $pdo->prepare("UPDATE notification_preferences SET push_enabled=?, email_enabled=?, deadline_alerts=?, exam_countdowns=? WHERE student_id=?");
    $stmt->execute([$values['push_enabled'], $values['email_enabled'], $values['deadline_alerts'], $values['exam_countdowns'], $sid]);
    set_flash('success', 'Preferences saved.');
}
redirect(BASE_URL . '/student/notifications/index.php');
