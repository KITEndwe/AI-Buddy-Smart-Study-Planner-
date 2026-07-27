<?php
require_once __DIR__ . '/../../config/config.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/student/groups/index.php');
}

$sid = current_student_id();
$groupId = (int)$_POST['group_id'];
$title = trim($_POST['title'] ?? '');

// Must be a member of the group to share anything into it
$isMember = $pdo->prepare("SELECT 1 FROM group_members WHERE group_id=? AND student_id=?");
$isMember->execute([$groupId, $sid]);
if (!$isMember->fetch()) {
    set_flash('error', 'You must be a member of this group to share notes.');
    redirect(BASE_URL . '/student/groups/view.php?id=' . $groupId);
}

if (!$title) {
    set_flash('error', 'Please give your note a title.');
    redirect(BASE_URL . '/student/groups/view.php?id=' . $groupId);
}

$allowedExt = ['pdf', 'doc', 'docx', 'ppt', 'pptx'];
$maxBytes = 20 * 1024 * 1024; // 20MB

$filePath = null;
$fileName = null;
$fileType = null;

if (!empty($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['attachment'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        set_flash('error', 'File upload failed. Please try again.');
        redirect(BASE_URL . '/student/groups/view.php?id=' . $groupId);
    }
    if ($file['size'] > $maxBytes) {
        set_flash('error', 'File is too large — 20MB maximum.');
        redirect(BASE_URL . '/student/groups/view.php?id=' . $groupId);
    }

    $origName = $file['name'];
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowedExt, true)) {
        set_flash('error', 'Only PDF, Word (.doc/.docx) and PowerPoint (.ppt/.pptx) files are allowed.');
        redirect(BASE_URL . '/student/groups/view.php?id=' . $groupId);
    }

    $uploadDir = __DIR__ . '/../../uploads/group_notes/';
    if (!is_dir($uploadDir)) { mkdir($uploadDir, 0755, true); }

    $storedName = 'g' . $groupId . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    $destPath = $uploadDir . $storedName;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        set_flash('error', 'Could not save the uploaded file. Please try again.');
        redirect(BASE_URL . '/student/groups/view.php?id=' . $groupId);
    }

    $filePath = 'uploads/group_notes/' . $storedName;
    $fileName = $origName;
    $fileType = $ext;
}

$pdo->prepare("INSERT INTO group_notes (group_id, title, shared_by, file_path, file_name, file_type) VALUES (?, ?, ?, ?, ?, ?)")
    ->execute([$groupId, $title, $sid, $filePath, $fileName, $fileType]);

set_flash('success', $filePath ? 'Document shared with the group.' : 'Note shared.');
redirect(BASE_URL . '/student/groups/view.php?id=' . $groupId);