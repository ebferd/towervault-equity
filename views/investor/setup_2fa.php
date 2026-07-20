<?php
$pName  = platform_setting('platform_name','NexVest');
$secret = htmlspecialchars($user['two_fa_secret'] ?? '');
$qrUrl  = $qrUrl ?? '';
?>
<style>
.tfa-wrap{max-width:480px;margin:0 auto;padding:0 0 2rem}
.tfa-card{background:#fff;border:1px solid var(--mist-200);border-radius:16px;padding:2rem;margin-bottom:1rem}
.tfa-eyebrow{font-size:11px;font-weight:700;letter-spacing:.09em;text-transform:uppercase;color:var(--mist-400);margin-bottom:.65rem}
.tfa-title{font-family:'Fraunces',serif;font-size:1.6rem;font-weight:900;color:var(--mist-900);letter-spacing:-.4px;line-height:1.2;margin-bottom:.65rem}
.tfa-sub{font-size:13.5px;color:var(--mist-500);line-height:1.6;margin-bottom:1.5rem}
.tfa-qr-box{display:flex;align-items:center;justify-content:center;background:var(--mist-50);border:1px solid var(--mist-200);border-radius:12px;padding:1.5rem;margin-bottom:1.25rem}
.tfa-secret-row{display:flex;align-items:center;gap:.6rem;background:var(--mist-50);border:1px solid var(--mist-200);border-radius:10px;padding:.75rem 1rem;margin-bottom:1.5rem}
.tfa-secret-key{font-family:monospace;font-size:14px;font-weight:700;color:var(--mist-900);letter-spacing:3px;flex:1;word-break:break-all}
.tfa-info{display:flex;gap:.65rem;align-items:flex-start;background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:.85rem 1rem;margin-bottom:1.5rem;font-size:13px;color:#1e40af;line-height:1.55}
.otp-grid{display:flex;gap:.5rem;justify-content:center;margin:1rem 0 1.5rem}
.otp-cell{width:48px;height:56px;text-align:center;font-size:22px;font-weight:700;border:1.5px solid var(--mist-200);border-radius:10px;outline:none;background:#fff;color:var(--mist-900);transition:border-color .15s,box-shadow .15s;font-family:inherit}
.otp-cell:focus{border-color:var(--em-500);box-shadow:0 0 0 3px rgba(16,185,129,.15)}
@media(max-width:400px){.otp-cell{width:40px;height:50px;font-size:18px;gap:.35rem}}
</style>

<div class="page-header">
  <div>
    <div class="tfa-eyebrow">Step 2 of 3 — Security</div>
    <h1 class="greet">Set Up Two-Factor Authentication</h1>
    <p class="greet-sub">Scan the QR code with Google Authenticator, Authy, or Microsoft Authenticator.</p>
  </div>
</div>

<div class="tfa-wrap">
  <div class="tfa-card">
    <div class="tfa-info">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" style="flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      2FA adds an extra layer of security. You can skip this step and enable it later from your profile.
    </div>

    <div id="tfa-alert"></div>

    <!-- QR code -->
    <div class="tfa-qr-box">
      <?php if (!empty($qrSvg)): ?>
        <div style="width:200px;height:200px;border-radius:8px;overflow:hidden;background:#fff"><?= $qrSvg ?></div>
      <?php else: ?>
        <div style="text-align:center;font-size:12px;color:var(--mist-400);padding:1rem">QR unavailable.<br>Use the manual key below.</div>
      <?php endif; ?>
    </div>

    <!-- Manual key -->
    <div style="font-size:11.5px;color:var(--text3);text-align:center;margin-bottom:.65rem">Can't scan? Use this key manually:</div>
    <div class="tfa-secret-row">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--mist-400)" stroke-width="1.8"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
      <div class="tfa-secret-key"><?= $secret ?></div>
      <button class="qbtn outline" style="height:32px;padding:0 .75rem;font-size:12px;flex-shrink:0" data-copy="<?= $secret ?>">Copy</button>
    </div>

    <!-- OTP entry -->
    <form id="setup-form">
      <input type="hidden" name="_token" value="<?= csrf_token() ?>"/>
      <input type="hidden" name="action" value="enable"/>
      <input type="hidden" name="code" id="setup-code"/>
      <label class="fl" style="display:block;text-align:center;margin-bottom:.25rem">Enter the 6-digit code to confirm</label>
      <div class="otp-grid">
        <?php for ($i = 0; $i < 6; $i++): ?><input class="otp-cell" maxlength="1" inputmode="numeric" pattern="[0-9]"/><?php endfor; ?>
      </div>
      <button type="submit" class="qbtn primary" style="width:100%;height:44px" id="setup-btn"><span>Enable &amp; Continue</span></button>
    </form>

    <?php if ($user['two_fa_enabled']): ?>
      <div id="disable-alert" style="margin-top:.75rem"></div>
      <form id="disable-form" style="margin-top:.5rem">
        <input type="hidden" name="_token" value="<?= csrf_token() ?>"/>
        <input type="hidden" name="action" value="disable"/>
        <button type="submit" class="qbtn outline" style="width:100%;height:40px;color:#dc2626;border-color:#fca5a5" id="disable-btn">Disable 2FA</button>
      </form>
    <?php else: ?>
      <a href="/investor/kyc" class="qbtn outline" style="width:100%;height:40px;margin-top:.5rem;display:flex">Skip for now</a>
    <?php endif; ?>
  </div>
</div>

<script>
// OTP auto-advance
const otpCells = document.querySelectorAll('.otp-cell');
otpCells.forEach((cell, i) => {
  cell.addEventListener('input', e => {
    const v = e.target.value.replace(/\D/g, '').slice(-1);
    e.target.value = v;
    if (v && i < otpCells.length - 1) otpCells[i+1].focus();
  });
  cell.addEventListener('keydown', e => {
    if (e.key === 'Backspace' && !cell.value && i > 0) otpCells[i-1].focus();
  });
  cell.addEventListener('paste', e => {
    e.preventDefault();
    const pasted = (e.clipboardData||window.clipboardData).getData('text').replace(/\D/g,'').slice(0,6);
    [...pasted].forEach((ch,j) => { if (otpCells[j]) otpCells[j].value = ch; });
    const idx = Math.min(pasted.length, 5);
    otpCells[idx]?.focus();
  });
});

document.getElementById('setup-form').addEventListener('submit', async e => {
  e.preventDefault();
  const code = [...otpCells].map(c => c.value).join('');
  if (code.length < 6) {
    document.getElementById('tfa-alert').innerHTML = '<div class="alert-banner err" style="margin-bottom:1rem">Please enter all 6 digits.</div>';
    return;
  }
  document.getElementById('setup-code').value = code;
  const btn = document.getElementById('setup-btn');
  btn.disabled = true;
  btn.querySelector('span').innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation:spin .7s linear infinite"><path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0" stroke-dasharray="56" stroke-dashoffset="56"/></svg> Verifying…';
  const data = await post('/investor/setup-2fa', new FormData(e.target), true);
  btn.disabled = false;
  btn.querySelector('span').textContent = 'Enable & Continue';
  if (data.success) {
    document.getElementById('tfa-alert').innerHTML = '<div class="alert-banner ok" style="margin-bottom:1rem">2FA enabled! Redirecting…</div>';
    setTimeout(() => window.location.href = data.redirect || '/investor/kyc', 1200);
  } else {
    document.getElementById('tfa-alert').innerHTML = '<div class="alert-banner err" style="margin-bottom:1rem">' + (data.error || 'Invalid code. Please try again.') + '</div>';
    otpCells.forEach(c => c.value = '');
    otpCells[0].focus();
  }
});

<?php if ($user['two_fa_enabled']): ?>
document.getElementById('disable-form').addEventListener('submit', async e => {
  e.preventDefault();
  if (!confirm('Are you sure you want to disable two-factor authentication?')) return;
  const btn = document.getElementById('disable-btn');
  btn.disabled = true; btn.textContent = 'Disabling…';
  const data = await post('/investor/setup-2fa', new FormData(e.target), true);
  btn.disabled = false; btn.textContent = 'Disable 2FA';
  if (data.success) window.location.href = '/investor/profile';
  else document.getElementById('disable-alert').innerHTML = '<div class="alert-banner err">' + (data.error || 'Failed.') + '</div>';
});
<?php endif; ?>
</script>
