<?php
/**
 * Gemini API client.
 * Used by the AI study planner and the AI study buddy chatbot.
 * Returns null on any failure so callers can fall back to mock/demo behaviour.
 *
 * gemini_generate($message, $systemInstruction = null, $history = [])
 *   $message           the latest user message / prompt
 *   $systemInstruction optional string grounding the model's behaviour
 *   $history           optional array of ['sender' => 'student'|'buddy', 'message' => '...']
 *                       for prior turns, oldest first (used by the chatbot for context)
 */
function gemini_generate($message, $systemInstruction = null, $history = []) {
    if (!defined('GEMINI_API_KEY') || !GEMINI_API_KEY) return null;

    // Google is mid-rollout of a new "AQ." API key format (replacing the older
    // "AIzaSy..." keys). AQ.-format keys are unreliable when passed as a
    // ?key= query parameter, so we send the key via the x-goog-api-key header
    // instead, which works for both old and new key formats.
    $url = "https://generativelanguage.googleapis.com/v1beta/models/" . GEMINI_MODEL . ":generateContent";

    $contents = [];
    foreach ($history as $turn) {
        $role = ($turn['sender'] ?? '') === 'buddy' ? 'model' : 'user';
        $contents[] = ['role' => $role, 'parts' => [['text' => $turn['message'] ?? '']]];
    }
    $contents[] = ['role' => 'user', 'parts' => [['text' => $message]]];

    $payload = ['contents' => $contents];
    if ($systemInstruction) {
        $payload['systemInstruction'] = ['parts' => [['text' => $systemInstruction]]];
    }

    $ch = curl_init($url);
    $curlOptions = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'x-goog-api-key: ' . GEMINI_API_KEY,
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 25,
    ];

    // XAMPP on Windows frequently ships without a working CA bundle, which makes
    // cURL fail every HTTPS request with "SSL certificate problem: unable to get
    // local issuer certificate" — usually silently, from PHP's point of view.
    // We ship a real, current CA bundle alongside this file so it works out of the box.
    $caBundle = __DIR__ . '/cacert.pem';
    if (file_exists($caBundle)) {
        $curlOptions[CURLOPT_CAINFO] = $caBundle;
    }

    curl_setopt_array($ch, $curlOptions);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        error_log("Gemini API cURL error: {$curlError}");
        return null;
    }
    if ($httpCode !== 200 || !$response) {
        error_log("Gemini API HTTP {$httpCode}: " . substr((string)$response, 0, 500));
        return null;
    }

    $data = json_decode($response, true);
    $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
    if ($text === null) {
        error_log("Gemini API: no text in response: " . substr($response, 0, 500));
    }
    return $text;
}