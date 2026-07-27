<?php
require_once __DIR__ . '/../../config/config.php';
require_login();
$sid = current_student_id();
$groupId = (int)($_GET['id'] ?? 0);
$pageTitle = 'Study group';
$activeNav = 'groups';

$stmt = $pdo->prepare("SELECT g.*, c.name AS course_name FROM study_groups g JOIN courses c ON c.id=g.course_id WHERE g.id=?");
$stmt->execute([$groupId]);
$group = $stmt->fetch();
if (!$group) { set_flash('error', 'Group not found.'); redirect(BASE_URL . '/student/groups/index.php'); }

$isMember = $pdo->prepare("SELECT 1 FROM group_members WHERE group_id=? AND student_id=?");
$isMember->execute([$groupId, $sid]);
$isMember = (bool)$isMember->fetch();

$members = $pdo->prepare("SELECT s.id, s.first_name, s.last_name FROM group_members gm JOIN students s ON s.id=gm.student_id WHERE gm.group_id=?");
$members->execute([$groupId]);
$members = $members->fetchAll();

// Classmates (same course) not already in the group — eligible to be added
$eligibleToAdd = [];
if ($isMember) {
    $elig = $pdo->prepare("SELECT s.id, s.first_name, s.last_name FROM student_courses sc
        JOIN students s ON s.id = sc.student_id
        WHERE sc.course_id = ? AND s.id NOT IN (SELECT student_id FROM group_members WHERE group_id=?)
        ORDER BY s.first_name");
    $elig->execute([$group['course_id'], $groupId]);
    $eligibleToAdd = $elig->fetchAll();
}

$notes = $pdo->prepare("SELECT gn.*, s.first_name FROM group_notes gn JOIN students s ON s.id=gn.shared_by WHERE gn.group_id=? ORDER BY gn.created_at DESC");
$notes->execute([$groupId]);
$notes = $notes->fetchAll();

$messages = $pdo->prepare("SELECT gm.*, s.first_name FROM group_messages gm JOIN students s ON s.id=gm.student_id WHERE gm.group_id=? ORDER BY gm.created_at ASC");
$messages->execute([$groupId]);
$messages = $messages->fetchAll();

$fileIcons = ['pdf' => '📄', 'doc' => '📝', 'docx' => '📝', 'ppt' => '📊', 'pptx' => '📊'];

include __DIR__ . '/../../includes/header.php';
?>
<div class="topline">
  <div><h1><?= e($group['name']) ?></h1><div class="desc"><?= e($group['course_name']) ?></div></div>
  <?php if (!$isMember): ?>
    <a class="pill-btn" href="<?= BASE_URL ?>/student/groups/join.php?id=<?= $groupId ?>">Join group</a>
  <?php elseif ((int)$group['created_by'] === $sid): ?>
    <a class="pill-btn ghost" style="color:#c0392b;" href="<?= BASE_URL ?>/student/groups/delete_group.php?id=<?= $groupId ?>" onclick="return confirm('Delete this group permanently? This removes it for every member, along with all messages and shared documents. This cannot be undone.');">Delete group</a>
  <?php else: ?>
    <a class="pill-btn ghost" href="<?= BASE_URL ?>/student/groups/leave_group.php?id=<?= $groupId ?>" onclick="return confirm('Leave this group? You can rejoin later if it\'s still open, or ask a member to add you back.');">Leave group</a>
  <?php endif; ?>
</div>

<div class="grid-2">
  <div class="card">
    <div class="block-head"><h3>Shared documents & notes</h3></div>
    <?php if ($isMember): ?>
    <form method="post" action="<?= BASE_URL ?>/student/groups/add_note.php" enctype="multipart/form-data" style="margin-bottom:14px;">
      <input type="hidden" name="group_id" value="<?= $groupId ?>">
      <input type="text" name="title" placeholder="Title…" required style="width:100%;padding:8px 12px;border:1px solid var(--line);border-radius:8px;margin-bottom:8px;">
      <div style="display:flex;gap:8px;align-items:center;">
        <input type="file" name="attachment" accept=".pdf,.doc,.docx,.ppt,.pptx" style="flex:1;font-size:12.5px;">
        <button class="pill-btn small" type="submit">Share</button>
      </div>
      <div style="font-size:11px;color:var(--ink-faint);margin-top:4px;">PDF, Word or PowerPoint · 20MB max · optional — you can also just share a text note title</div>
    </form>
    <?php endif; ?>
    <?php foreach ($notes as $n): ?>
      <div class="item-row">
        <div class="item-dot" style="background:var(--amber);"></div>
        <div style="flex:1;">
          <div class="title"><?= e($n['title']) ?></div>
          <div class="meta">Shared by <?= e($n['first_name']) ?></div>
        </div>
        <?php if ($n['file_path']): ?>
          <a href="<?= BASE_URL . '/' . e($n['file_path']) ?>" download="<?= e($n['file_name']) ?>" class="pill-btn ghost small" title="<?= e($n['file_name']) ?>">
            <?= $fileIcons[$n['file_type']] ?? '📎' ?> Download
          </a>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
    <?php if (!$notes): ?><div style="color:var(--ink-soft);font-size:13px;">Nothing shared yet.</div><?php endif; ?>

    <div style="margin-top:16px;font-weight:600;font-size:13px;">Members (<?= count($members) ?>)</div>
    <div class="avatars-row" style="margin-top:8px;">
      <?php foreach ($members as $m): ?><div class="mini-avatar" title="<?= e($m['first_name'] . ' ' . $m['last_name']) ?>"><?= e(initials($m['first_name'],$m['last_name'])) ?></div><?php endforeach; ?>
    </div>

    <?php if ($isMember): ?>
    <div style="margin-top:16px;">
      <form method="post" action="<?= BASE_URL ?>/student/groups/add_member.php" style="display:flex;gap:8px;">
        <input type="hidden" name="group_id" value="<?= $groupId ?>">
        <select name="student_id" required style="flex:1;padding:8px 12px;border:1px solid var(--line);border-radius:8px;font-size:13px;">
          <?php if ($eligibleToAdd): ?>
            <option value="">Add a classmate…</option>
            <?php foreach ($eligibleToAdd as $c): ?>
              <option value="<?= $c['id'] ?>"><?= e($c['first_name'] . ' ' . $c['last_name']) ?></option>
            <?php endforeach; ?>
          <?php else: ?>
            <option value="">No classmates available to add</option>
          <?php endif; ?>
        </select>
        <button class="pill-btn ghost small" type="submit" <?= $eligibleToAdd ? '' : 'disabled' ?>>Add</button>
      </form>
      <div style="font-size:11px;color:var(--ink-faint);margin-top:4px;">Only students taking <?= e($group['course_name']) ?> can be added.</div>
    </div>
    <?php endif; ?>
  </div>

  <div class="card">
    <div class="block-head"><h3>Discussion</h3></div>
    <div class="chat-thread">
      <?php foreach ($messages as $m): $isMe = $m['student_id'] == $sid; ?>
        <div class="chat-msg <?= $isMe?'me':'' ?>"><div><div class="who"><?= $isMe ? 'You' : e($m['first_name']) ?></div><div class="bubble"><?= e($m['message']) ?></div></div></div>
      <?php endforeach; ?>
      <?php if (!$messages): ?><div style="color:var(--ink-soft);font-size:13px;">No messages yet — start the discussion.</div><?php endif; ?>
    </div>
    <?php if ($isMember): ?>
    <form method="post" action="<?= BASE_URL ?>/student/groups/post_message.php" style="display:flex;gap:8px;">
      <input type="hidden" name="group_id" value="<?= $groupId ?>">
      <input type="text" name="message" placeholder="Write a message…" required style="flex:1;padding:9px 12px;border:1px solid var(--line);border-radius:8px;">
      <button class="pill-btn small" type="submit">Post</button>
    </form>
    <?php endif; ?>
  </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>