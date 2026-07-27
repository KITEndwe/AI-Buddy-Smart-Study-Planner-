<?php
require_once __DIR__ . '/../../config/config.php';
require_login();
$sid = current_student_id();
$pageTitle = 'AI study buddy';
$activeNav = 'buddy';

$history = $pdo->prepare("SELECT * FROM chat_messages WHERE student_id=? ORDER BY created_at ASC LIMIT 50");
$history->execute([$sid]);
$history = $history->fetchAll();

if (!$history) {
    $welcome = "Hi " . ($student['first_name'] ?? 'there') . " 👋 I'm your study buddy. Ask me what to focus on, or just tell me how you're feeling about your workload.";
    $pdo->prepare("INSERT INTO chat_messages (student_id, sender, message) VALUES (?, 'buddy', ?)")->execute([$sid, $welcome]);
    $history = [['sender' => 'buddy', 'message' => $welcome]];
}

include __DIR__ . '/../../includes/header.php';
?>
<div class="topline">
  <div>
    <h1>AI study buddy</h1>
    <div class="desc">Ask about your workload, get unstuck, or just talk through your plan.</div>
  </div>
</div>

<?php if (!defined('GEMINI_API_KEY') || !GEMINI_API_KEY): ?>
<div class="empty-note">Demo mode: no Gemini API key configured — replies below come from a local rules-based responder. Add <code>GEMINI_API_KEY</code> in <code>config/config.php</code> for live AI conversation.</div>
<?php endif; ?>

<div class="card chat-panel">
  <div class="chat-log" id="chat-log">
    <?php foreach ($history as $m): ?>
      <?php if ($m['sender'] === 'buddy'): ?>
        <div class="chat-msg">
          <div class="buddy-avatar">🤖</div>
          <div>
            <div class="who">Study Buddy</div>
            <div class="bubble"><?= nl2br(e($m['message'])) ?></div>
          </div>
        </div>
      <?php else: ?>
        <div class="chat-msg me">
          <div>
            <div class="who">You</div>
            <div class="bubble"><?= e($m['message']) ?></div>
          </div>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>

  <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:8px;">
    <button class="suggestion-chip" type="button" onclick="sendChatMsg('What should I study next?')">What should I study next?</button>
    <button class="suggestion-chip" type="button" onclick="sendChatMsg('I feel behind on Database Systems')">I feel behind on Database Systems</button>
    <button class="suggestion-chip" type="button" onclick="sendChatMsg('Give me a break reminder')">Give me a break reminder</button>
  </div>

  <div class="chat-input-row">
    <input id="chat-input" placeholder="Ask your study buddy anything…" onkeydown="if(event.key==='Enter'){sendChatMsg()}">
    <button class="pill-btn" id="send-btn" type="button" onclick="sendChatMsg()">Send</button>
  </div>
</div>

<script>
const CHAT_ENDPOINT = "<?= BASE_URL ?>/student/buddy/send.php";

function sendChatMsg(preset) {
  const input = document.getElementById('chat-input');
  const sendBtn = document.getElementById('send-btn');
  const text = preset || input.value.trim();
  if (!text) return;

  const log = document.getElementById('chat-log');

  const safeUserText = text.replace(/</g, '&lt;').replace(/>/g, '&gt;');
  log.insertAdjacentHTML('beforeend', `<div class="chat-msg me"><div><div class="who">You</div><div class="bubble">${safeUserText}</div></div></div>`);

  input.value = '';
  input.disabled = true;
  sendBtn.disabled = true;

  const loadingId = 'loading-' + Date.now();
  log.insertAdjacentHTML('beforeend', `<div class="chat-msg" id="${loadingId}"><div class="buddy-avatar">🤖</div><div><div class="who">Study Buddy</div><div class="bubble"><em>Thinking…</em></div></div></div>`);
  log.scrollTop = log.scrollHeight;

  fetch(CHAT_ENDPOINT, {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: 'message=' + encodeURIComponent(text)
  })
  .then(r => r.json())
  .then(data => {
    document.getElementById(loadingId)?.remove();

    const rawReply = data.reply || "Sorry, I couldn't process that right now.";
    const formattedReply = rawReply
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/\n/g, '<br>')
      .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

    log.insertAdjacentHTML('beforeend', `<div class="chat-msg"><div class="buddy-avatar">🤖</div><div><div class="who">Study Buddy</div><div class="bubble">${formattedReply}</div></div></div>`);
    log.scrollTop = log.scrollHeight;
  })
  .catch(() => {
    document.getElementById(loadingId)?.remove();
    log.insertAdjacentHTML('beforeend', `<div class="chat-msg"><div class="buddy-avatar">🤖</div><div><div class="who">Study Buddy</div><div class="bubble">Connection error. Please try again.</div></div></div>`);
  })
  .finally(() => {
    input.disabled = false;
    sendBtn.disabled = false;
    input.focus();
  });
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
