// Small shared behaviours (inline forms are toggled via onclick in the PHP views).

document.addEventListener('DOMContentLoaded', function () {
  console.log('Smart Buddy loaded');
});

/**
 * Password strength meter.
 * Call from a password field's oninput: checkPasswordStrength(this, 'fill-id', 'label-id')
 * Markup expected nearby:
 *   <div class="strength-meter">
 *     <div class="strength-track"><div class="strength-fill" id="fill-id"></div></div>
 *     <div class="strength-label" id="label-id"></div>
 *   </div>
 */
function checkPasswordStrength(input, fillId, labelId) {
  const val = input.value;
  const fill = document.getElementById(fillId);
  const label = document.getElementById(labelId);
  if (!fill || !label) return;

  if (!val) {
    fill.style.width = '0%';
    label.textContent = '';
    return;
  }

  let score = 0;
  if (val.length >= 8) score++;
  if (val.length >= 12) score++;
  if (/[A-Z]/.test(val)) score++;
  if (/[0-9]/.test(val)) score++;
  if (/[^A-Za-z0-9]/.test(val)) score++;

  let level, width, color;
  if (score <= 2) {
    level = 'Weak password'; width = '33%'; color = '#E2574C';
  } else if (score <= 3) {
    level = 'Fair password'; width = '66%'; color = '#E8A33D';
  } else {
    level = 'Strong password'; width = '100%'; color = '#2F9E8F';
  }

  fill.style.width = width;
  fill.style.background = color;
  label.textContent = level;
  label.style.color = color;
}
