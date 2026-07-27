<?php
/**
 * Self-registration is disabled by design: student accounts are created
 * by an administrator (see admin/students/add.php) and assigned to a
 * programme at creation time. This file just redirects anyone who lands
 * here back to the login page with an explanation.
 */
require_once __DIR__ . '/../../config/config.php';
set_flash('info', 'New student accounts are created by an administrator. Please contact your programme admin for your login details.');
redirect(BASE_URL . '/student/auth/login.php');
