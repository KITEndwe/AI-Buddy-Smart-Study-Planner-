<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/gemini_client.php';
require_login();
$sid = current_student_id();

// Clear this student's upcoming AI-generated sessions so re-generating doesn't duplicate
$pdo->prepare("DELETE FROM study_sessions WHERE student_id=? AND source='ai_planner' AND session_date >= CURDATE()")->execute([$sid]);

// Pull the real signals the AI (or heuristic) reasons over
$assignments = $pdo->prepare("SELECT a.*, c.name AS course_name FROM assignments a JOIN courses c ON c.id=a.course_id
    WHERE a.student_id=? AND a.status='pending' ORDER BY a.due_date ASC");
$assignments->execute([$sid]);
$assignments = $assignments->fetchAll();

$courses = $pdo->prepare("SELECT c.id, c.name FROM student_courses sc JOIN courses c ON c.id=sc.course_id WHERE sc.student_id=?");
$courses->execute([$sid]);
$courses = $courses->fetchAll();

// Nothing to schedule against at all — be honest instead of faking success
if (!$assignments && !$courses) {
    set_flash('error', "You're not enrolled in any courses yet, so there's nothing to build a plan around. Add a course first.");
    redirect(BASE_URL . '/student/planner/index.php');
}

$plan = null;

if (GEMINI_API_KEY && ($assignments || $courses)) {
    $prompt = "You are a study planner. Given these pending assignments (title, course, due date): "
        . json_encode($assignments)
        . " and these enrolled courses: " . json_encode($courses)
        . ". Generate a 7-day study plan starting today. If there are no pending assignments, "
        . "schedule general revision sessions spread across the enrolled courses instead. "
        . "Respond ONLY as JSON, an array of objects with keys: "
        . "date (YYYY-MM-DD), start_time (HH:MM), end_time (HH:MM), course_id, focus_topic. No extra text.";
    $raw = gemini_generate($prompt);
    if ($raw) {
        $clean = trim(preg_replace('/```json|```/', '', $raw));
        $decoded = json_decode($clean, true);
        if (is_array($decoded)) $plan = $decoded;
    }
}

// Fallback heuristic if no API key or the API call failed.
// Prioritises pending assignments, but if there are none, falls back to
// general revision sessions spread across the student's enrolled courses
// instead of producing an empty (and falsely "successful") plan.
if (!$plan) {
    $plan = [];
    $timeSlots = ['08:00-10:00','10:00-12:00','14:00-16:00','16:00-18:00','19:00-21:00'];
    $poolIndex = 0;

    $pool = $assignments ?: array_map(function ($c) {
        return ['course_id' => $c['id'], 'title' => 'Revision — ' . $c['name']];
    }, $courses);

    if ($pool) {
        for ($day = 0; $day < 7; $day++) {
            $date = date('Y-m-d', strtotime("+{$day} day"));
            $sessionsToday = ($day % 2 === 0) ? 2 : 1;
            for ($i = 0; $i < $sessionsToday; $i++) {
                $item = $pool[$poolIndex % count($pool)];
                $poolIndex++;
                list($start, $end) = explode('-', $timeSlots[($day + $i) % count($timeSlots)]);
                $plan[] = [
                    'date' => $date,
                    'start_time' => $start,
                    'end_time' => $end,
                    'course_id' => $item['course_id'],
                    'focus_topic' => $item['title'],
                ];
            }
        }
    }
}

$insert = $pdo->prepare("INSERT INTO study_sessions (student_id, course_id, session_date, start_time, end_time, focus_topic, source) VALUES (?, ?, ?, ?, ?, ?, 'ai_planner')");
$savedCount = 0;
foreach ($plan as $p) {
    if (empty($p['date']) || empty($p['course_id'])) continue;
    $insert->execute([$sid, (int)$p['course_id'], $p['date'], $p['start_time'] ?? '08:00', $p['end_time'] ?? '09:00', $p['focus_topic'] ?? 'Study session']);
    $savedCount++;
}

// Only claim success if a plan was actually saved
if ($savedCount > 0) {
    set_flash('success', 'Your weekly plan has been generated.');
} else {
    set_flash('error', 'Could not generate a plan this time — please try again.');
}
redirect(BASE_URL . '/student/planner/index.php');

