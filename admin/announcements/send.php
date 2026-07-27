<?php
require_once __DIR__ . '/../../config/config.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/admin/announcements/index.php');
}

$adminId = current_admin_id();
$title = trim($_POST['title'] ?? '');
$message = trim($_POST['message'] ?? '');
$audience = $_POST['audience'] ?? 'all';

// Only "filtered" honours these — "all" always means every active student,
// regardless of anything submitted alongside it, so a stray form value can
// never accidentally broaden or narrow an "all students" send.
$programmeId = null;
$yearOfStudy = null;
$semester = null;

if ($audience === 'filtered') {
    $programmeId = !empty($_POST['programme_id']) ? (int)$_POST['programme_id'] : null;
    $yearOfStudy = !empty($_POST['year_of_study']) ? (int)$_POST['year_of_study'] : null;
    $semester    = !empty($_POST['semester']) ? (int)$_POST['semester'] : null;
}

if (!$title || !$message) {
    set_flash('error', 'Please provide both a title and a message.');
    redirect(BASE_URL . '/admin/announcements/index.php');
}

// Build the recipient query explicitly — only students matching every
// specified filter receive it. Any filter left blank/"Any" is simply not
// applied, rather than defaulting to "everyone", so a half-filled form
// can't silently widen the audience.
$where = ["status = 'active'"];
$params = [];
if ($programmeId) { $where[] = "programme_id = ?"; $params[] = $programmeId; }
if ($yearOfStudy) { $where[] = "year_of_study = ?"; $params[] = $yearOfStudy; }
if ($semester)    { $where[] = "semester = ?"; $params[] = $semester; }

$sql = "SELECT id FROM students WHERE " . implode(' AND ', $where);
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$recipientIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (!$recipientIds) {
    set_flash('error', 'No students match that audience — nothing was sent.');
    redirect(BASE_URL . '/admin/announcements/index.php');
}

$pdo->beginTransaction();
try {
    $insertAnnouncement = $pdo->prepare("INSERT INTO announcements (admin_id, title, message, programme_id, year_of_study, semester, recipient_count) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $insertAnnouncement->execute([$adminId, $title, $message, $programmeId, $yearOfStudy, $semester, count($recipientIds)]);

    $notifMessage = $title . ' — ' . $message;
    $insertNotif = $pdo->prepare("INSERT INTO notifications (student_id, type, message) VALUES (?, 'announcement', ?)");
    foreach ($recipientIds as $studentId) {
        $insertNotif->execute([$studentId, $notifMessage]);
    }

    $pdo->commit();
    set_flash('success', 'Announcement sent to ' . count($recipientIds) . ' student' . (count($recipientIds) === 1 ? '' : 's') . '.');
} catch (Exception $e) {
    $pdo->rollBack();
    error_log('Announcement send failed: ' . $e->getMessage());
    set_flash('error', 'Something went wrong sending the announcement. Please try again.');
}

redirect(BASE_URL . '/admin/announcements/index.php');