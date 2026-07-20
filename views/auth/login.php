<p class="card-eyebrow">Welcome back</p>
<h2 class="card-title">Sign in to your account</h2>
<p class="card-sub">Enter your credentials to access your portfolio.</p>

<div id="login-alert"></div>

<form id="login-form">
  <input type="hidden" name="_token" value="<?= csrf_token() ?>"/>

  <div class="field">
    <label for="email" class="flabel">Email address</label>
    <div class="input-wrap">
      <svg class="input-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M22 6 12 13 2 6"/><rect x="2" y="4" width="20" height="16" rx="2"/></svg>
      <input id="email" name="email" type="email" autocomplete="email" required placeholder="you@example.com" class="finput"/>
    </div>
  </div>

  <div class="field">
    <div class="flabel-row">
      <label for="password" class="flabel" style="margin-bottom:0">Password</label>
      <a href="/forgot-password" class="flink">Forgot password?</a>
    </div>
    <div class="input-wrap">
      <svg class="input-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
      <input id="password" name="password" type="password" autocomplete="current-password" required placeholder="••••••••" class="finput pwd"/>
      <button type="button" class="pwd-toggle" data-toggle-password="password" aria-label="Show password">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
      </button>
    </div>
  </div>

  <button type="submit" class="btn-primary" id="login-btn">
    <span id="login-btn-label">Sign in</span>
  </button>
</form>

<div class="divider">
  <div class="divider-line"></div>
  <span class="divider-txt">New to <?= htmlspecialchars(platform_setting('platform_name','NexVest')) ?>?</span>
  <div class="divider-line"></div>
</div>

<a href="/register" class="btn-outline">Create an account</a>

<div class="trust-row">
  <span>
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
    256-bit encryption
  </span>
  <span>
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
    KYC / AML compliant
  </span>
</div>

<!-- 2FA step (shown in-place when the account requires it) -->
<div id="twofa-card" style="display:none">
  <button type="button" id="twofa-back" class="back-link">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
    Back to sign in
  </button>
  <p class="card-eyebrow">Step 2 of 2</p>
  <h2 class="card-title">Enter verification code</h2>
  <p class="card-sub">Open your authenticator app and enter the 6-digit code.</p>

  <div id="twofa-alert"></div>

  <form id="twofa-form">
    <input type="hidden" name="_token" value="<?= csrf_token() ?>"/>
    <div class="otp-row">
      <?php for ($i = 0; $i < 6; $i++): ?><input class="otp-cell" maxlength="1" inputmode="numeric"/><?php endfor; ?>
    </div>
    <input type="hidden" name="code" id="twofa-code"/>
    <button type="submit" class="btn-primary" id="twofa-btn">
      <span id="twofa-btn-label">Verify code</span>
    </button>
  </form>
</div>

<script>
(function () {
  var loginCard  = document.querySelectorAll('#login-form, .divider, .trust-row, a.btn-outline');
  var twofaCard  = document.getElementById('twofa-card');
  var formCard   = document.querySelector('.form-card');

  function showAlert(targetId, type, message) {
    var icons = {
      err:  '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
      ok:   '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>',
      warn: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4M12 17h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>'
    };
    document.getElementById(targetId).innerHTML =
      '<div class="alert-banner ' + type + '">' + icons[type] + '<span>' + message + '</span></div>';
  }
  function clearAlert(targetId) { document.getElementById(targetId).innerHTML = ''; }
  function shake() {
    formCard.classList.add('shake');
    setTimeout(function () { formCard.classList.remove('shake'); }, 400);
  }

  document.getElementById('login-form').addEventListener('submit', async function (e) {
    e.preventDefault();
    var btn = document.getElementById('login-btn');
    var label = document.getElementById('login-btn-label');
    clearAlert('login-alert');
    btn.disabled = true;
    label.innerHTML = '<span class="spinner"></span> Signing in…';

    var fd = new FormData(e.target);
    var data = await post('/login', fd, true);

    if (data.success && data.action === '2fa') {
      loginCard.forEach(function (el) { el.style.display = 'none'; });
      document.querySelector('.card-eyebrow').style.display = 'none';
      document.querySelector('.card-title').style.display = 'none';
      document.querySelector('.card-sub').style.display = 'none';
      twofaCard.style.display = 'block';
      setTimeout(function () { document.querySelector('#twofa-form .otp-cell')?.focus(); }, 50);
      btn.disabled = false;
      label.textContent = 'Sign in';
    } else if (data.success && data.redirect) {
      label.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg> Success';
      window.location.href = data.redirect;
    } else {
      btn.disabled = false;
      label.textContent = 'Sign in';
      if (data.action === 'verify_email') {
        showAlert('login-alert', 'warn', (data.error || 'Please verify your email first.') +
          ' <a href="/verify-email?email=' + encodeURIComponent(data.email || '') + '" style="font-weight:600">Verify now</a>');
      } else {
        showAlert('login-alert', 'err', data.error || 'Login failed. Please check your credentials.');
      }
      shake();
    }
  });

  document.getElementById('twofa-back').addEventListener('click', function () {
    twofaCard.style.display = 'none';
    loginCard.forEach(function (el) { el.style.display = ''; });
    document.querySelector('.card-eyebrow').style.display = '';
    document.querySelector('.card-title').style.display = '';
    document.querySelector('.card-sub').style.display = '';
    clearAlert('twofa-alert');
  });

  document.getElementById('twofa-form').addEventListener('submit', async function (e) {
    e.preventDefault();
    var cells = document.querySelectorAll('#twofa-form .otp-cell');
    var code = Array.prototype.map.call(cells, function (c) { return c.value; }).join('');
    document.getElementById('twofa-code').value = code;

    var btn = document.getElementById('twofa-btn');
    var label = document.getElementById('twofa-btn-label');
    clearAlert('twofa-alert');
    btn.disabled = true;
    label.innerHTML = '<span class="spinner"></span> Verifying…';

    var fd = new FormData(e.target);
    fd.set('code', code);
    var data = await post('/login/2fa', fd, true);
    btn.disabled = false;
    label.textContent = 'Verify code';

    if (data.success) {
      window.location.href = data.redirect || '/investor/dashboard';
    } else {
      showAlert('twofa-alert', 'err', data.error || 'Invalid code. Please try again.');
      cells.forEach(function (c) { c.value = ''; });
      cells[0].focus();
      shake();
    }
  });
})();
</script>
