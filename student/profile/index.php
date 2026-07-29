<?php
require_once __DIR__ . '/../../config/config.php';
require_login();
$pageTitle = 'Profile';
$activeNav = 'profile';
include __DIR__ . '/../../includes/header.php';
?>
<div class="topline"><div><h1>Profile</h1><div class="desc">Manage your account details.</div></div></div>
<div class="grid-2">
  <div class="card">
    <div style="display:flex;align-items:center;gap:16px;margin-bottom:22px;">
      <div class="avatar-circle" style="width:56px;height:56px;font-size:20px;"><?= e(initials($student['first_name'],$student['last_name'])) ?></div>
      <div><div style="font-weight:600;font-size:16px;"><?= e($student['first_name'].' '.$student['last_name']) ?></div>
      <div style="font-size:12.5px;color:var(--ink-soft);"><?= e($student['programme_name']) ?> · Year <?= e($student['year_of_study']) ?> · Semester <?= e($student['semester']) ?> · <?= e($student['email']) ?></div></div>
    </div>
    <form method="post" action="<?= BASE_URL ?>/student/profile/update.php">
      <div class="form-row-2">
        <div class="field"><label>First name</label><input type="text" name="first_name" value="<?= e($student['first_name']) ?>"></div>
        <div class="field"><label>Last name</label><input type="text" name="last_name" value="<?= e($student['last_name']) ?>"></div>
      </div>
      <div class="field"><label>Student email</label><input type="email" name="email" value="<?= e($student['email']) ?>"></div>
      <div class="form-row-2">
        <div class="field"><label>Year of study</label><input type="number" name="year_of_study" min="1" max="6" value="<?= e($student['year_of_study']) ?>"></div>
        <div class="field"><label>Semester</label>
          <select name="semester">
            <option value="1" <?= (int)$student['semester'] === 1 ? 'selected' : '' ?>>Semester 1</option>
            <option value="2" <?= (int)$student['semester'] === 2 ? 'selected' : '' ?>>Semester 2</option>
          </select>
        </div>
      </div>
      <button class="pill-btn teal" type="submit">Save changes</button>
    </form>
  </div>
  <div class="card">
    <div class="block-head"><h3>Change password</h3></div>
    <form method="post" action="<?= BASE_URL ?>/student/profile/change_password.php">
      <div class="field"><label>Current password</label><input type="password" name="current_password" required></div>
      <div class="field"><label>New password</label><input type="password" name="new_password" id="new-password" oninput="checkPasswordStrength(this,'profile-pw-fill','profile-pw-label')" required></div>
      <div class="strength-meter">
        <div class="strength-track"><div class="strength-fill" id="profile-pw-fill"></div></div>
        <div class="strength-label" id="profile-pw-label"></div>
      </div>
      <div class="field"><label>Confirm new password</label><input type="password" name="confirm_password" required></div>
      <button class="pill-btn ghost" type="submit">Update password</button>
    </form>
  </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>