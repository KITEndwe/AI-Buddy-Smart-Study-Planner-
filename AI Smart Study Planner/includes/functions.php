<?php
/**
 * Shared helper functions
 */

// Polyfill for PHP < 8.0 (str_contains was added in PHP 8.0)
if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle) {
        return $needle === '' || strpos($haystack, $needle) !== false;
    }
}

function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect($path) {
    header("Location: " . $path);
    exit;
}

function is_logged_in() {
    return isset($_SESSION['student_id']);
}

function is_admin_logged_in() {
    return isset($_SESSION['admin_id']);
}

function require_login() {
    if (!is_logged_in()) {
        redirect(BASE_URL . '/student/auth/login.php');
    }
}

function require_admin() {
    if (!is_admin_logged_in()) {
        redirect(BASE_URL . '/admin/login.php');
    }
}

function current_student_id() {
    return $_SESSION['student_id'] ?? null;
}

function set_flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash() {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function days_until($dateStr) {
    $today = new DateTime('today');
    $date = new DateTime($dateStr);
    return (int) $today->diff($date)->format('%r%a');
}

function format_date($dateStr) {
    return date('j M', strtotime($dateStr));
}

/**
 * Fetch the logged-in student's full row, or null.
 */
function get_current_student($pdo) {
    if (!is_logged_in()) return null;
    $stmt = $pdo->prepare("SELECT s.*, p.name AS programme_name FROM students s
                            JOIN programmes p ON p.id = s.programme_id
                            WHERE s.id = ?");
    $stmt->execute([current_student_id()]);
    return $stmt->fetch();
}

function initials($first, $last) {
    return strtoupper(substr($first, 0, 1) . substr($last, 0, 1));
}
