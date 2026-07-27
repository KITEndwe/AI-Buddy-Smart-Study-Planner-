<?php
/**
 * Site-wide configuration
 */
session_start();

define('SITE_NAME', 'Smart Buddy');
define('BASE_URL', '/AI Smart Study Planner'); // must match your XAMPP htdocs folder name

// Add your Gemini API key here to activate the AI planner and study buddy chatbot.
// Get a key at https://aistudio.google.com/apikey
define('GEMINI_API_KEY', 'AQ.Ab8RN6KeLReu2bX2nBJFgvCv79W8CVFZDBUc8BNhXrcFNRo5AQ'); // leave blank to keep running in demo/mock mode
define('GEMINI_MODEL', 'gemini-3.6-flash'); // Google's Gemini model lineup keeps moving fast (2.0 -> 2.5 -> 3.5 -> 3.6, all within 2026) — if this ever 404s again with "no longer available", check https://ai.google.dev/gemini-api/docs/models for the current model name

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../includes/functions.php';