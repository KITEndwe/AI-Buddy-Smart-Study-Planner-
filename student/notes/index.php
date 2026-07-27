<?php
require_once __DIR__ . '/../../config/config.php';
require_login();
$sid = current_student_id();
$pageTitle = 'Notes';
$activeNav = 'notes';
$filterCourse = $_GET['course_id'] ?? 'all';
$search = trim($_GET['q'] ?? '');

$courses = $pdo->prepare("SELECT c.* FROM student_courses sc JOIN courses c ON c.id=sc.course_id WHERE sc.student_id=? ORDER BY c.name");
$courses->execute([$sid]);
$courses = $courses->fetchAll();

$sql = "SELECT n.*, c.name AS course_name FROM notes n JOIN courses c ON c.id=n.course_id WHERE n.student_id=?";
$params = [$sid];
if ($filterCourse !== 'all') { $sql .= " AND n.course_id=?"; $params[] = (int)$filterCourse; }
if ($search !== '') { $sql .= " AND n.title LIKE ?"; $params[] = "%{$search}%"; }
$sql .= " ORDER BY n.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$allNotes = $stmt->fetchAll();

// Split into study materials (has an uploaded document) vs plain text notes,
// so uploaded documents get their own clickable library instead of being
// buried as a small tag inside a note card.
$materials = array_values(array_filter($allNotes, function($n) { return !empty($n['file_path']); }));
$textNotes = array_values(array_filter($allNotes, function($n) { return empty($n['file_path']); }));

$fileIcons = ['pdf' => '📄', 'doc' => '📝', 'docx' => '📝', 'ppt' => '📊', 'pptx' => '📊'];

include __DIR__ . '/../../includes/header.php';
?>
<div class="topline"><div><h1>Notes</h1><div class="desc">Organised by course, searchable, with attachments.</div></div>
  <button class="pill-btn" onclick="document.getElementById('note-form').classList.toggle('open')">+ New note / upload material</button>
</div>

<div class="inline-form" id="note-form">
  <form method="post" action="<?= BASE_URL ?>/student/notes/add.php" enctype="multipart/form-data">
    <div class="form-row-2">
      <div class="field"><label>Title</label><input type="text" name="title" required></div>
      <div class="field"><label>Course</label><select name="course_id" required><?php foreach ($courses as $c): ?><option value="<?= $c['id'] ?>"<?= $filterCourse!=='all' && (string)$filterCourse===(string)$c['id'] ? ' selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
    </div>
    <div class="field"><label>Content (optional if you're just uploading a document)</label><textarea rows="3" name="content" placeholder="Write your note…"></textarea></div>
    <div class="form-row-2">
      <div class="field"><label>YouTube tutorial link (optional)</label><input type="text" name="youtube_link" placeholder="https://youtube.com/..."></div>
      <div class="field"><label>Study material — PDF, Word or PowerPoint (optional)</label><input type="file" name="attachment" accept=".pdf,.doc,.docx,.ppt,.pptx"></div>
    </div>
    <label style="font-size:13px;display:flex;gap:6px;align-items:center;margin-bottom:12px;"><input type="checkbox" name="is_practice" value="1"> Mark as practice questions</label>
    <button class="pill-btn teal" type="submit">Save</button>
  </form>
</div>

<div class="notes-layout" style="margin-top:18px;">
  <div class="card course-filter-list">
    <a href="?" style="display:block;padding:9px 12px;border-radius:8px;text-decoration:none;<?= $filterCourse==='all'?'background:var(--navy-900);color:#fff;':'color:var(--ink-soft);' ?>">All courses</a>
    <?php foreach ($courses as $c): ?>
      <a href="?course_id=<?= $c['id'] ?>" style="display:block;padding:9px 12px;border-radius:8px;text-decoration:none;<?= (string)$filterCourse===(string)$c['id']?'background:var(--navy-900);color:#fff;':'color:var(--ink-soft);' ?>"><?= e($c['name']) ?></a>
    <?php endforeach; ?>
  </div>
  <div>
    <form method="get" style="margin-bottom:14px;">
      <?php if ($filterCourse !== 'all'): ?><input type="hidden" name="course_id" value="<?= e($filterCourse) ?>"><?php endif; ?>
      <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search notes…" style="width:100%;padding:11px 14px;border:1px solid var(--line);border-radius:9px;">
    </form>

    <?php if ($materials): ?>
      <div style="font-weight:600;font-size:13px;margin-bottom:10px;">Study materials</div>
      <div class="grid-3" style="margin-bottom:22px;">
        <?php foreach ($materials as $n): $ext = strtolower(pathinfo($n['file_path'], PATHINFO_EXTENSION)); ?>
          <div class="card" style="display:flex;flex-direction:column;">
            <a href="<?= BASE_URL . '/' . e($n['file_path']) ?>" target="_blank" style="text-decoration:none;color:inherit;flex:1;">
              <div style="font-size:26px;margin-bottom:8px;"><?= $fileIcons[$ext] ?? '📎' ?></div>
              <div style="font-weight:600;font-size:13.5px;margin-bottom:4px;"><?= e($n['title']) ?></div>
              <div style="font-size:11.5px;color:var(--ink-faint);"><?= e($n['course_name']) ?></div>
              <?php if ($n['is_practice_question']): ?><span class="tag amber" style="margin-top:8px;display:inline-block;">Practice questions</span><?php endif; ?>
            </a>
            <a href="<?= BASE_URL ?>/student/notes/delete.php?id=<?= $n['id'] ?>" onclick="return confirm('Delete this study material?');" style="margin-top:10px;font-size:11.5px;color:var(--ink-faint);align-self:flex-end;">Delete</a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if ($textNotes): ?>
      <div style="font-weight:600;font-size:13px;margin-bottom:10px;">Notes</div>
    <?php endif; ?>
    <?php foreach ($textNotes as $n): ?>
      <div class="card note-card">
        <div class="n-title"><?= e($n['title']) ?></div>
        <div style="font-size:11.5px;color:var(--ink-faint);margin-bottom:6px;"><?= e($n['course_name']) ?></div>
        <div class="n-snip"><?= nl2br(e($n['content'])) ?></div>
        <div class="note-meta-row">
          <?php if ($n['is_practice_question']): ?><span class="tag amber">Practice questions</span><?php endif; ?>
          <?php if ($n['youtube_link']): ?><a class="tag coral" style="text-decoration:none;" href="<?= e($n['youtube_link']) ?>" target="_blank">YouTube tutorial</a><?php endif; ?>
          <a href="<?= BASE_URL ?>/student/notes/delete.php?id=<?= $n['id'] ?>" onclick="return confirm('Delete this note?');" style="margin-left:auto;color:var(--ink-faint);font-size:12px;">Delete</a>
        </div>
      </div>
    <?php endforeach; ?>

    <?php if (!$allNotes): ?><div style="color:var(--ink-soft);font-size:13px;">No notes or study materials yet.</div><?php endif; ?>
  </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>