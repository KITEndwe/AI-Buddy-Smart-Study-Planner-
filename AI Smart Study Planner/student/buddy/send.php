<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../includes/gemini_client.php';
require_login();

header('Content-Type: application/json');

$sid = current_student_id();
$message = trim($_POST['message'] ?? '');

if (!$message) {
    echo json_encode(['reply' => "I didn't catch that — try again?"]);
    exit;
}

// 1. Save student's message
$pdo->prepare("INSERT INTO chat_messages (student_id, sender, message) VALUES (?, 'student', ?)")
    ->execute([$sid, $message]);

// 2. Fetch pending assignments for grounding/context
$contextStmt = $pdo->prepare("SELECT a.title, c.name AS course, a.due_date
    FROM assignments a
    JOIN courses c ON c.id=a.course_id
    WHERE a.student_id=? AND a.status='pending'
    ORDER BY a.due_date ASC LIMIT 5");
$contextStmt->execute([$sid]);
$upcoming = $contextStmt->fetchAll(PDO::FETCH_ASSOC);

$reply = null;

// 3. Try live Gemini API first
if (defined('GEMINI_API_KEY') && GEMINI_API_KEY) {
    // Last 10 turns of chat history, for conversational context
    $historyStmt = $pdo->prepare("SELECT sender, message FROM chat_messages WHERE student_id=? ORDER BY created_at ASC LIMIT 10");
    $historyStmt->execute([$sid]);
    $history = $historyStmt->fetchAll(PDO::FETCH_ASSOC);

    $systemInstruction = "You are a friendly, encouraging AI study buddy for a student at Cavendish University Zambia. "
        . "The student's current upcoming pending assignments are: " . json_encode($upcoming) . ". "
        . "Help them plan study time, stay motivated, break down workload, and stay on top of deadlines. "
        . "Keep responses clear, warm, concise, and structured with bullet points when planning.";

    $reply = gemini_generate($message, $systemInstruction, $history);
}

// 4. Local rules-based fallback (demo mode, or if the API call above failed —
//    check your Apache error log for the reason if this keeps happening with a key set)
if (!$reply) {
    $m = strtolower($message);
    if (str_contains($m, 'next')) {
        $stmt = $pdo->prepare("SELECT a.title, c.name AS course, a.due_date FROM assignments a
            JOIN courses c ON c.id=a.course_id WHERE a.student_id=? AND a.status='pending' ORDER BY a.due_date ASC LIMIT 1");
        $stmt->execute([$sid]);
        $next = $stmt->fetch();
        $reply = $next
            ? "Based on your deadlines, I'd focus on {$next['course']} next — \"{$next['title']}\" is due " . (function_exists('format_date') ? format_date($next['due_date']) : $next['due_date']) . "."
            : "You're all caught up on assignments right now — nice work! Maybe review notes for your next exam.";
    } elseif (str_contains($m, 'behind') || str_contains($m, 'database')) {
        $reply = "You're not as behind as it feels. Try one focused 50-minute block on the topic today, then check back in — small steady sessions add up fast.";
    } elseif (str_contains($m, 'break')) {
        $reply = "Good call — take a 10 minute break. Stand up, get some water, and come back for your next study block.";
    } elseif (str_contains($m, 'stress') || str_contains($m, 'tired') || str_contains($m, 'overwhelm')) {
        $reply = "That's a lot on your plate right now. Let's break it into one task at a time — want me to pick the single most urgent thing for the next hour?";
    } elseif (str_contains($m, 'hi') || str_contains($m, 'hello')) {
        $reply = "Hey! Ready to plan your next study block, or want a quick summary of what's due this week?";
    } else {
        $reply = "Good question — once a Gemini API key is configured, I'll reason over your live courses, notes and deadlines for a tailored answer. For now, check your Assignments page for what's due next.";
    }
}

// 5. Save AI reply to database
$pdo->prepare("INSERT INTO chat_messages (student_id, sender, message) VALUES (?, 'buddy', ?)")
    ->execute([$sid, $reply]);

echo json_encode(['reply' => $reply]);
