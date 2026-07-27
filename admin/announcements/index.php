<?php
require_once __DIR__ . '/../../config/config.php';
require_admin();
$pageTitle = 'Announcements';
$activeNav = 'announcements';

$programmes = $pdo->query("SELECT id, name, code FROM programmes ORDER BY name")->fetchAll();

$history = $pdo->query("SELECT a.*, ad.name AS admin_name, p.name AS programme_name
    FROM announcements a
    JOIN admins ad ON ad.id = a.admin_id
    LEFT JOIN programmes p ON p.id = a.programme_id
    ORDER BY a.created_at DESC LIMIT 30")->fetchAll();

include __DIR__ . '/../../includes/admin_header.php';
?>
<div class="topline"><div><h1>Announcements</h1><div class="desc">Remind students to submit work, or broadcast anything important — targeted so it only reaches the students it's meant for.</div></div></div>

<div class="grid-2">
  <div class="card">
    <div class="block-head"><h3>New announcement</h3></div>
    <form method="post" action="<?= BASE_URL ?>/admin/announcements/send.php">
      <div class="field"><label>Title</label><input type="text" name="title" placeholder="e.g. Assignment 2 due Friday" required></div>
      <div class="field"><label>Message</label><textarea rows="4" name="message" placeholder="Write the announcement…" required></textarea></div>

      <div class="field">
        <label>Audience</label>
        <div style="display:flex;gap:6px;margin-bottom:10px;">
          <label style="display:flex;align-items:center;gap:6px;font-size:13px;font-weight:500;">
            <input type="radio" name="audience" value="all" checked onchange="document.getElementById('audience-filters').style.display='none';"> All students
          </label>
          <label style="display:flex;align-items:center;gap:6px;font-size:13px;font-weight:500;margin-left:18px;">
            <input type="radio" name="audience" value="filtered" onchange="document.getElementById('audience-filters').style.display='grid';"> Specific audience
          </label>
        </div>
      </div>

      <div id="audience-filters" class="form-row-2" style="display:none;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:14px;">
        <div class="field">
          <label>Programme</label>
          <select name="programme_id">
            <option value="">Any programme</option>
            <?php foreach ($programmes as $p): ?>
              <option value="<?= $p['id'] ?>"><?= e($p['name']) ?> (<?= e($p['code']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label>Year of study</label>
          <select name="year_of_study">
            <option value="">Any year</option>
            <?php for ($y=1;$y<=6;$y++): ?><option value="<?= $y ?>">Year <?= $y ?></option><?php endfor; ?>
          </select>
        </div>
        <div class="field">
          <label>Semester</label>
          <select name="semester">
            <option value="">Any semester</option>
            <option value="1">Semester 1</option>
            <option value="2">Semester 2</option>
          </select>
        </div>
      </div>
      <div style="font-size:11.5px;color:var(--ink-faint);margin-bottom:14px;">Leave any of these on "Any" to include every value for that field. Choose "Specific audience" and leave all three on "Any" to still narrow nothing — pick at least one to actually restrict who receives it.</div>

      <button class="pill-btn teal" type="submit">Send announcement</button>
    </form>
  </div>

  <div class="card">
    <div class="block-head"><h3>Sent history</h3></div>
    <?php foreach ($history as $a): ?>
      <div class="item-row" style="align-items:flex-start;">
        <div class="item-dot" style="background:var(--amber);margin-top:5px;"></div>
        <div style="flex:1;">
          <div class="title"><?= e($a['title']) ?></div>
          <div class="meta" style="margin-bottom:4px;"><?= e($a['message']) ?></div>
          <div class="meta mono" style="font-size:11px;">
            <?php
              $target = [];
              if ($a['programme_name']) $target[] = e($a['programme_name']);
              if ($a['year_of_study']) $target[] = 'Year ' . e($a['year_of_study']);
              if ($a['semester']) $target[] = 'Semester ' . e($a['semester']);
              echo $target ? implode(' · ', $target) : 'All students';
            ?>
             · <?= e($a['recipient_count']) ?> reached · <?= e(date('j M, g:ia', strtotime($a['created_at']))) ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if (!$history): ?><div style="color:var(--ink-soft);font-size:13px;">No announcements sent yet.</div><?php endif; ?>
  </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>