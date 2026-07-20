<a href="/login" class="back-link">
  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
  Back to sign in
</a>

<p class="card-eyebrow">Account recovery</p>
<h2 class="card-title">Reset your password</h2>
<p class="card-sub">Enter the email on your account and we'll send a reset link.</p>

<div id="fp-alert"></div>

<form id="fp-form">
  <input type="hidden" name="_token" value="<?= csrf_token() ?>"/>
  <div class="field">
    <label for="email" class="flabel">Email address</label>
    <div class="input-wrap">
      <svg class="input-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M22 6 12 13 2 6"/><rect x="2" y="4" width="20" height="16" rx="2"/></svg>
      <input id="email" class="finput" type="email" name="email" placeholder="you@example.com" required autocomplete="email"/>
    </div>
  </div>

  <button type="submit" class="btn-primary" id="fp-btn">
    <span id="fp-btn-label">Send reset link</span>
  </button>
</form>

<p class="form-footer">Remembered your password? <a href="/login" class="flink">Sign in</a></p>

<script>
(function () {
  var card = document.querySelector('.form-card');
  function showAlert(type, message) {
    var icons = {
      err: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
      ok:  '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>'
    };
    document.getElementById('fp-alert').innerHTML = '<div class="alert-banner ' + type + '">' + icons[type] + '<span>' + message + '</span></div>';
  }

  document.getElementById('fp-form').addEventListener('submit', async function (e) {
    e.preventDefault();
    var btn = document.getElementById('fp-btn');
    var label = document.getElementById('fp-btn-label');
    document.getElementById('fp-alert').innerHTML = '';
    btn.disabled = true;
    label.innerHTML = '<span class="spinner"></span> Sending…';

    var fd = new FormData(e.target);
    var data = await post('/forgot-password', fd, true);
    btn.disabled = false;
    label.textContent = 'Send reset link';

    if (data.success) {
      showAlert('ok', data.message);
      e.target.reset();
    } else {
      showAlert('err', data.error || 'Something went wrong. Please try again.');
      card.classList.add('shake');
      setTimeout(function () { card.classList.remove('shake'); }, 400);
    }
  });
})();
</script>
