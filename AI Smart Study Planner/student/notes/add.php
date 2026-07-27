<?php
require_once __DIR__ . '/../../config/config.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sid = current_student_id();
    $title = trim($_POST['title'] ?? '');
    $courseId = (int)$_POST['course_id'];
    $content = trim($_POST['content'] ?? '');
    $yt = trim($_POST['youtube_link'] ?? '');
    $isPractice = isset($_POST['is_practice']) ? 1 : 0;
    $filePath = null;

    if (!empty($_FILES['attachment']['name'])) {
        $file = $_FILES['attachment'];
        $allowedExt = ['pdf', 'doc', 'docx', 'ppt', 'pptx'];
        $maxBytes = 20 * 1024 * 1024; // 20MB
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($file['error'] !== UPLOAD_ERR_OK) {
            set_flash('error', 'File upload failed. Please try again.');
            redirect(BASE_URL . '/student/notes/index.php');
        }
        if (!in_array($ext, $allowedExt, true)) {
            set_flash('error', 'Only PDF, Word (.doc/.docx) and PowerPoint (.ppt/.pptx) files are allowed as study materials.');
            redirect(BASE_URL . '/student/notes/index.php');
        }
        if ($file['size'] > $maxBytes) {
            set_flash('error', 'File is too large — 20MB maximum.');
            redirect(BASE_URL . '/student/notes/index.php');
        }

        $uploadDir = __DIR__ . '/../../uploads/notes/';
        if (!is_dir($uploadDir)) { mkdir($uploadDir, 0755, true); }

        $safeOriginal = preg_replace('/[^A-Za-z0-9._-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
        $storedName = $safeOriginal . '_' . bin2hex(random_bytes(6)) . '.' . $ext;

        if (move_uploaded_file($file['tmp_name'], $uploadDir . $storedName)) {
            $filePath = 'uploads/notes/' . $storedName;
        } else {
            set_flash('error', 'Could not save the uploaded file. Please try again.');
            redirect(BASE_URL . '/student/notes/index.php');
        }
    }

    // A title is still required, but if you're just uploading a document to
    // study from, you don't need to type any note content.
    if ($title && $courseId) {
        $stmt = $pdo->prepare("INSERT INTO notes (student_id, course_id, title, content, youtube_link, file_path, is_practice_question) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$sid, $courseId, $title, $content, $yt, $filePath, $isPractice]);
        set_flash('success', $filePath ? 'Study material uploaded.' : 'Note saved.');
    }
}
redirect(BASE_URL . '/student/notes/index.php');