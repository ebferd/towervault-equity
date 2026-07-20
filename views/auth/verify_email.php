<p class="card-eyebrow">One last step</p>
<h2 class="card-title">Verify your email</h2>
<p class="card-sub">We sent a 6-digit code to <strong><?= htmlspecialchars($email ?? '') ?></strong></p>

<div id="verify-alert"></div>

<form id="verify-form">
  <input type="hidden" name="_token" value="<?= csrf_token() ?>"/>
  <input type="hidden" name="email" value="<?= htmlspecialchars($email ?? '') ?>"/>
  <div class="otp-row">
    <?php for ($i = 0; $i < 6; $i++): ?><input class="otp-cell" maxlength="1" inputmode="numeric"/><?php endfor; ?>
  </div>
  <input type="hidden" name="otp" id="otp-val"/>
  <button type="submit" class="btn-primary" id="verify-btn">
    <span id="verify-btn-label">Verify email</span>
  </button>
</form>

<button class="btn-outline" id="resend-btn" style="margin-top:.75rem">Resend code</button>
<p class="form-footer"><a href="/login" class="flink">Back to sign in</a></p>

<script>
(function () {
  var card = document.querySelector('.form-card');
  function showAlert(targetId, type, message) {
    var icons = {
      err: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
      ok:  '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>'
    };
    document.getElementById(targetId).innerHTML = '<div class="alert-banner ' + type + '">' + icons[type] + '<span>' + message + '</span></div>';
  }

  document.getElementById('verify-form').addEventListener('submit', async function (e) {
    e.preventDefault();
    var cells = document.querySelectorAll('#verify-form .otp-cell');
    var otp = Array.prototype.map.call(cells, function (c) { return c.value; }).join('');
    if (otp.length < 6) {
      showAlert('verify-alert', 'err', 'Please enter all 6 digits.');
      return;
    }
    document.getElementById('otp-val').value = otp;

    var btn = document.getElementById('verify-btn');
    var label = document.getElementById('verify-btn-label');
    document.getElementById('verify-alert').innerHTML = '';
    btn.disabled = true;
    label.innerHTML = '<span class="spinner"></span> Verifying…';

    var fd = new FormData(e.target);
    var data = await post('/verify-email', fd, true);

    if (data.success) {
      label.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Verified';
      window.location.href = data.redirect;
    } else {
      btn.disabled = false;
      label.textContent = 'Verify email';
      showAlert('verify-alert', 'err', data.error || 'Invalid code. Please try again.');
      cells.forEach(function (c) { c.value = ''; });
      cells[0].focus();
      card.classList.add('shake');
      setTimeout(function () { card.classList.remove('shake'); }, 400);
    }
  });

  document.getElementById('resend-btn').addEventListener('click', async function () {
    var btn = this;
    var original = btn.textContent;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner" style="border-color:rgba(71,85,105,.3);border-top-color:var(--mist-600)"></span> Sending…';
    var fd = new FormData();
    fd.append('email', <?= json_encode($email ?? '') ?>);
    var data = await post('/verify-email/resend', fd, true);
    btn.disabled = false;
    btn.textContent = original;
    showAlert('verify-alert', data.success ? 'ok' : 'err', data.success ? (data.message || 'Code resent successfully.') : (data.error || 'Failed to resend code.'));
  });
})();
</script>
