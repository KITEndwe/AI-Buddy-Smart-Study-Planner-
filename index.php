<?php
require_once __DIR__ . '/config/config.php';
if (is_logged_in()) {
    redirect(BASE_URL . '/student/dashboard.php');
} elseif (is_admin_logged_in()) {
    redirect(BASE_URL . '/admin/dashboard.php');
} else {
    redirect(BASE_URL . '/student/auth/login.php');
}
