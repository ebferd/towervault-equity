<?php /* Profile — $user, $sessions */ ?>
<style>
.prof-hero{display:flex;align-items:center;gap:1rem;flex-wrap:wrap;background:#fff;border:1px solid var(--mist-200);border-radius:16px;padding:1.25rem 1.4rem;margin-bottom:1rem}
.prof-av{width:56px;height:56px;border-radius:50%;background:var(--em-600);display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:700;color:#fff;flex-shrink:0}
.prof-av-info{flex:1;min-width:160px}
.prof-av-name{font-size:17px;font-weight:700;color:var(--mist-900)}
.prof-av-email{font-size:13px;color:var(--mist-400);margin-top:2px;word-break:break-all}
.prof-av-badges{display:flex;gap:.4rem;flex-wrap:wrap;margin-top:.55rem}
.prof-stats{display:flex;gap:.6rem;flex-wrap:wrap;width:100%}
.prof-stat{flex:1;min-width:90px;background:var(--mist-50);border:1px solid var(--mist-100);border-radius:10px;padding:.7rem 1rem;text-align:center}
.prof-stat-val{font-size:1.1rem;font-weight:700;color:var(--mist-900)}
.prof-stat-lbl{font-size:10.5px;color:var(--mist-400);margin-top:.2rem;text-transform:uppercase;letter-spacing:.06em;font-weight:600}

.p-card{background:#fff;border:1px solid var(--mist-200);border-radius:14px;overflow:hidden;margin-bottom:1rem}
.p-card-head{display:flex;align-items:center;gap:.65rem;padding:1rem 1.25rem;border-bottom:1px solid var(--mist-100)}
.p-card-icon{width:30px;height:30px;border-radius:8px;background:var(--mist-100);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.p-card-title{font-size:13.5px;font-weight:600;color:var(--mist-900)}
.p-card-body{padding:1.1rem 1.25rem}

.frow2{display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.75rem}
@media(max-width:560px){.frow2{grid-template-columns:1fr}}

.ref-link-box{display:flex;align-items:center;gap:.5rem;background:var(--mist-50);border:1px solid var(--mist-200);border-radius:9px;padding:.6rem .9rem;margin-bottom:.65rem}
.ref-link-text{font-size:11px;font-family:monospace;color:var(--mist-600);flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}

.sec-divider{font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--mist-400);margin-bottom:.75rem}
.tfa-row{display:flex;align-items:center;justify-content:space-between;gap:.75rem;flex-wrap:wrap;background:var(--mist-50);border-radius:10px;padding:.9rem 1rem;margin-top:.85rem}
.tfa-label{font-size:13px;font-weight:600;color:var(--mist-900)}
.tfa-sub{font-size:11.5px;color:var(--mist-400);margin-top:.15rem}

.sess-row{display:flex;align-items:center;gap:.75rem;padding:.8rem 0;border-bottom:1px solid var(--mist-100)}
.sess-row:last-child{border-bottom:none}
.sess-icon{width:34px;height:34px;border-radius:8px;background:var(--mist-100);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.sess-device{font-size:13px;font-weight:600;color:var(--mist-900)}
.sess-meta{font-size:11px;color:var(--mist-400);margin-top:.15rem}
</style>

<div class="page-header">
  <div>
    <h1 class="greet">My Profile</h1>
    <p class="greet-sub">Manage your personal information, security, and account settings.</p>
  </div>
</div>

<!-- Hero -->
<div class="prof-hero">
  <div class="prof-av"><?= strtoupper(substr($user['first_name'],0,1).substr($user['last_name'],0,1)) ?></div>
  <div class="prof-av-info">
    <div class="prof-av-name"><?= htmlspecialchars($user['first_name'].' '.$user['last_name']) ?></div>
    <div class="prof-av-email"><?= htmlspecialchars($user['email']) ?></div>
    <div class="prof-av-badges">
      <span class="badge <?= htmlspecialchars($user['kyc_status']) ?>">KYC <?= ucfirst(str_replace('_',' ',$user['kyc_status'])) ?></span>
      <?php if ($user['two_fa_enabled']): ?>
      <span class="badge" style="background:#eff6ff;color:#1e40af">2FA On</span>
      <?php endif; ?>
    </div>
  </div>
  <div class="prof-stats">
    <div class="prof-stat">
      <div class="prof-stat-val"><?= fmt_currency((float)($user['wallet_balance']??0)) ?></div>
      <div class="prof-stat-lbl">Balance</div>
    </div>
    <div class="prof-stat">
      <div class="prof-stat-val"><?= date('Y', strtotime($user['created_at'])) ?></div>
      <div class="prof-stat-lbl">Member since</div>
    </div>
  </div>
</div>

<!-- Referral link -->
<div class="p-card">
  <div class="p-card-head">
    <div class="p-card-icon">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--mist-500)" stroke-width="1.8"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
    </div>
    <div class="p-card-title">Your referral link</div>
  </div>
  <div class="p-card-body">
    <?php $refLink = 'https://'.($_SERVER['HTTP_HOST']??'nexvest.com').'/register?ref='.($user['referral_code']??'') ?>
    <div class="ref-link-box">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="var(--mist-400)" stroke-width="1.8"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
      <span class="ref-link-text"><?= htmlspecialchars($refLink) ?></span>
    </div>
    <div style="display:flex;gap:.5rem;flex-wrap:wrap">
      <button class="qbtn outline" style="flex:1;min-width:120px;height:36px;font-size:12.5px" data-copy="<?= htmlspecialchars($refLink) ?>">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
        Copy link
      </button>
      <button onclick="if(navigator.share){navigator.share({url:'<?= htmlspecialchars($refLink) ?>'}).catch(()=>{})}else{copyText('<?= htmlspecialchars($refLink) ?>',this)}" class="qbtn outline" style="flex:1;min-width:120px;height:36px;font-size:12.5px">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
        Share
      </button>
    </div>
  </div>
</div>

<!-- Personal information -->
<div class="p-card">
  <div class="p-card-head">
    <div class="p-card-icon">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--mist-500)" stroke-width="1.8"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
    </div>
    <div class="p-card-title">Personal information</div>
  </div>
  <div class="p-card-body">
    <div id="profile-alert"></div>
    <form id="profile-form">
      <input type="hidden" name="_token" value="<?= csrf_token() ?>"/>
      <div class="frow2">
        <div class="fg"><label class="fl">First name</label><input class="fi" type="text" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required/></div>
        <div class="fg"><label class="fl">Last name</label><input class="fi" type="text" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required/></div>
      </div>
      <div class="frow2">
        <div class="fg"><label class="fl">Email <span style="color:var(--mist-400);font-weight:400">(read-only)</span></label><input class="fi" type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled style="opacity:.5;cursor:not-allowed"/></div>
        <div class="fg"><label class="fl">Phone number</label><input class="fi" type="tel" name="phone" value="<?= htmlspecialchars($user['phone']??'') ?>" placeholder="+1 555 000 0000"/></div>
      </div>
      <div class="frow2">
        <div class="fg"><label class="fl">Country</label><input class="fi" type="text" name="country" value="<?= htmlspecialchars($user['country']??'') ?>" placeholder="Your country"/></div>
        <div class="fg"><label class="fl">Date of birth</label><input class="fi" type="date" name="dob" value="<?= htmlspecialchars($user['date_of_birth']??'') ?>" max="<?= date('Y-m-d', strtotime('-18 years')) ?>"/></div>
      </div>
      <button type="submit" class="qbtn primary" style="height:40px;width:100%"><span>Save changes</span></button>
    </form>
  </div>
</div>

<!-- Security -->
<div class="p-card">
  <div class="p-card-head">
    <div class="p-card-icon">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--mist-500)" stroke-width="1.8"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
    </div>
    <div class="p-card-title">Security</div>
  </div>
  <div class="p-card-body">
    <div class="sec-divider">Change password</div>
    <div id="pwd-alert"></div>
    <form id="pwd-form">
      <input type="hidden" name="_token" value="<?= csrf_token() ?>"/>
      <div class="fg" style="margin-bottom:.75rem">
        <label class="fl">Current password</label>
        <input class="fi" type="password" name="current_password" placeholder="••••••••" required/>
      </div>
      <div class="frow2">
        <div class="fg"><label class="fl">New password</label><input class="fi" type="password" name="new_password" placeholder="Min. 8 characters" required/></div>
        <div class="fg"><label class="fl">Confirm new password</label><input class="fi" type="password" name="confirm_password" placeholder="••••••••" required/></div>
      </div>
      <button type="submit" class="qbtn outline" style="height:38px"><span>Update password</span></button>
    </form>

    <div class="tfa-row">
      <div style="display:flex;align-items:center;gap:.75rem">
        <div style="width:36px;height:36px;border-radius:9px;background:#fff;border:1px solid var(--mist-200);display:flex;align-items:center;justify-content:center;flex-shrink:0">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="<?= $user['two_fa_enabled'] ? 'var(--em-600)' : 'var(--mist-400)' ?>" stroke-width="1.8"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
        </div>
        <div>
          <div class="tfa-label">Two-factor authentication</div>
          <div class="tfa-sub"><?= $user['two_fa_enabled'] ? 'Enabled — using authenticator app' : 'Add an extra layer of security' ?></div>
        </div>
      </div>
      <a href="/investor/setup-2fa" class="qbtn <?= $user['two_fa_enabled'] ? 'outline' : 'primary' ?>" style="height:34px;font-size:12px;white-space:nowrap">
        <?= $user['two_fa_enabled'] ? 'Manage' : 'Enable 2FA' ?>
      </a>
    </div>
  </div>
</div>

<!-- Active Sessions -->
<div class="p-card">
  <div class="p-card-head">
    <div class="p-card-icon">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--mist-500)" stroke-width="1.8"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
    </div>
    <div class="p-card-title">Active sessions</div>
  </div>
  <div class="p-card-body" style="padding-top:.25rem;padding-bottom:.25rem">
    <?php if (empty($sessions)): ?>
      <div style="padding:1.5rem 0;text-align:center;font-size:13px;color:var(--mist-400)">No active sessions found.</div>
    <?php else: foreach ($sessions as $sess): ?>
      <div class="sess-row">
        <div class="sess-icon">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--mist-500)" stroke-width="1.8"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
        </div>
        <div style="flex:1;min-width:0">
          <div class="sess-device"><?= htmlspecialchars($sess['device'] ?? $sess['user_agent'] ?? 'Unknown device') ?></div>
          <div class="sess-meta"><?= htmlspecialchars($sess['ip_address']) ?> &middot; Active <?= time_ago($sess['last_active']) ?></div>
        </div>
      </div>
    <?php endforeach; endif; ?>
  </div>
</div>

<!-- Data & Privacy -->
<div class="p-card">
  <div class="p-card-head">
    <div class="p-card-icon">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--mist-500)" stroke-width="1.8"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
    </div>
    <div class="p-card-title">Data &amp; Privacy (GDPR)</div>
  </div>
  <div class="p-card-body">
    <p style="font-size:13px;color:var(--mist-500);margin-bottom:1rem;line-height:1.65">You have the right to access, download, and delete all personal data we hold about you. Downloads include your profile, transactions, holdings, and support tickets in JSON format.</p>
    <div style="display:flex;gap:.6rem;flex-wrap:wrap;margin-bottom:1rem">
      <a href="/investor/data-export" class="qbtn outline" style="height:38px;font-size:12.5px">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:5px"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        Download My Data
      </a>
      <a href="/investor/compliance" class="qbtn outline" style="height:38px;font-size:12.5px">View Legal &amp; Compliance</a>
    </div>
    <div style="background:var(--mist-50);border:1px solid var(--mist-100);border-radius:10px;padding:1rem">
      <div style="font-size:13px;font-weight:600;color:var(--mist-800);margin-bottom:.35rem">Request Account Deletion</div>
      <p style="font-size:12px;color:var(--mist-500);margin-bottom:.85rem;line-height:1.5">This submits a deletion request to our team. Active investments must be closed before deletion can be processed.</p>
      <div id="deletion-result" style="margin-bottom:.5rem"></div>
      <button id="deletion-btn" class="qbtn" style="background:var(--red-bg);color:var(--red);border:1px solid var(--red-b);height:36px;font-size:12.5px" onclick="requestDeletion()">
        <span>Request Account Deletion</span>
      </button>
    </div>
  </div>
</div>

<script>
async function requestDeletion() {
  if (!confirm('Submit a GDPR account deletion request? Our team will contact you within 30 days.')) return;
  const btn = document.getElementById('deletion-btn');
  btn.disabled = true;
  btn.querySelector('span').innerHTML = '<span class="spinner" style="border-color:rgba(192,57,43,.3);border-top-color:var(--red)"></span> Sending…';
  const fd = new FormData();
  fd.append('_token', '<?= csrf_token() ?>');
  const data = await post('/investor/request-deletion', fd, true);
  btn.disabled = false;
  btn.querySelector('span').textContent = 'Request Account Deletion';
  document.getElementById('deletion-result').innerHTML = data.success
    ? '<div class="alert-banner ok" style="margin-bottom:.5rem"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg><span>Deletion request submitted. Ref: <strong>' + data.reference + '</strong>. We\'ll confirm within 30 days.</span></div>'
    : '<div class="alert-banner err" style="margin-bottom:.5rem">' + (data.error || 'Failed.') + '</div>';
}
</script>

<script>
document.getElementById('profile-form').addEventListener('submit', async e => {
  e.preventDefault();
  const btn = e.target.querySelector('[type="submit"]');
  const label = btn.querySelector('span');
  btn.disabled = true;
  label.innerHTML = '<span class="spinner" style="border-color:rgba(255,255,255,.4);border-top-color:#fff"></span> Saving…';
  const data = await post('/investor/profile', new FormData(e.target), true);
  btn.disabled = false;
  label.textContent = 'Save changes';
  const alert = document.getElementById('profile-alert');
  alert.innerHTML = data.success
    ? `<div class="alert-banner ok" style="margin-bottom:.75rem"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg><span>${data.message}</span></div>`
    : `<div class="alert-banner err" style="margin-bottom:.75rem"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg><span>${data.error}</span></div>`;
});

document.getElementById('pwd-form').addEventListener('submit', async e => {
  e.preventDefault();
  const btn = e.target.querySelector('[type="submit"]');
  const label = btn.querySelector('span');
  btn.disabled = true;
  label.innerHTML = '<span class="spinner" style="border-color:rgba(5,150,105,.4);border-top-color:var(--em-600)"></span> Updating…';
  const data = await post('/investor/profile/password', new FormData(e.target), true);
  btn.disabled = false;
  label.textContent = 'Update password';
  document.getElementById('pwd-alert').innerHTML = data.success
    ? `<div class="alert-banner ok" style="margin-bottom:.75rem"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg><span>${data.message}</span></div>`
    : `<div class="alert-banner err" style="margin-bottom:.75rem"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg><span>${data.error}</span></div>`;
  if (data.success) e.target.reset();
});
</script>
