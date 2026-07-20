<p class="card-eyebrow">Account recovery</p>
<h2 class="card-title">Set a new password</h2>
<p class="card-sub">Choose a strong password you haven't used before.</p>

<div id="rp-alert"></div>

<form id="rp-form">
  <input type="hidden" name="_token" value="<?= csrf_token() ?>"/>
  <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>"/>

  <div class="field">
    <label for="password" class="flabel">New password</label>
    <div class="input-wrap">
      <svg class="input-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
      <input id="password" class="finput pwd" type="password" name="password" placeholder="Min. 8 characters" required autocomplete="new-password"/>
      <button type="button" class="pwd-toggle" data-toggle-password="password" aria-label="Show password">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
      </button>
    </div>
    <div class="pwd-reqs">
      <span class="pwd-req" data-rule="len"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>8+ characters</span>
      <span class="pwd-req" data-rule="case"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>Upper &amp; lowercase</span>
      <span class="pwd-req" data-rule="num"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>A number</span>
    </div>
  </div>

  <div class="field">
    <label for="confirm_password" class="flabel">Confirm new password</label>
    <div class="input-wrap">
      <svg class="input-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
      <input id="confirm_password" class="finput pwd" type="password" name="confirm_password" placeholder="••••••••" required autocomplete="new-password"/>
    </div>
    <div id="pwd-match-hint" class="fhelp"></div>
  </div>

  <button type="submit" class="btn-primary" id="rp-btn">
    <span id="rp-btn-label">Update password</span>
  </button>
</form>

<script>
(function () {
  var pwd = document.getElementById('password');
  var confirm = document.getElementById('confirm_password');
  var hint = document.getElementById('pwd-match-hint');
  var card = document.querySelector('.form-card');

  function checkReqs() {
    var v = pwd.value;
    var rules = { len: v.length >= 8, case: /[a-z]/.test(v) && /[A-Z]/.test(v), num: /[0-9]/.test(v) };
    Object.keys(rules).forEach(function (k) {
      document.querySelector('.pwd-req[data-rule="' + k + '"]').classList.toggle('ok', rules[k]);
    });
  }
  pwd.addEventListener('input', checkReqs);

  function checkMatch() {
    if (!confirm.value) { hint.textContent = ''; return; }
    if (confirm.value === pwd.value) { hint.textContent = 'Passwords match'; hint.style.color = 'var(--em-600)'; }
    else { hint.textContent = 'Passwords do not match'; hint.style.color = 'var(--red-700)'; }
  }
  confirm.addEventListener('input', checkMatch);
  pwd.addEventListener('input', checkMatch);

  function showAlert(message) {
    document.getElementById('rp-alert').innerHTML =
      '<div class="alert-banner err"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg><span>' + message + '</span></div>';
  }

  document.getElementById('rp-form').addEventListener('submit', async function (e) {
    e.preventDefault();
    var btn = document.getElementById('rp-btn');
    var label = document.getElementById('rp-btn-label');
    document.getElementById('rp-alert').innerHTML = '';
    btn.disabled = true;
    label.innerHTML = '<span class="spinner"></span> Updating…';

    var fd = new FormData(e.target);
    var data = await post('/reset-password', fd, true);

    if (data.success) {
      label.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Updated';
      window.location.href = data.redirect;
    } else {
      btn.disabled = false;
      label.textContent = 'Update password';
      showAlert(data.error || 'Something went wrong. Please try again.');
      card.classList.add('shake');
      setTimeout(function () { card.classList.remove('shake'); }, 400);
    }
  });
})();
</script>
