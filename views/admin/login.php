<?php /* views/admin/login.php */ ?>
<div class="auth-eyebrow" style="color:var(--text3);font-size:10px;letter-spacing:2.5px;text-transform:uppercase;margin-bottom:.75rem">Administrator Access</div>
<h2 style="font-family:'DM Serif Display',serif;font-size:1.5rem;color:var(--text);margin-bottom:.35rem">Admin Sign In</h2>
<p style="font-size:13px;color:var(--text2);margin-bottom:1.5rem">Restricted access — authorised personnel only.</p>
<div id="al-alert"></div>
<form id="admin-login-form">
  <input type="hidden" name="_token" value="<?= csrf_token() ?>"/>
  <div class="fg"><label class="fl">Email</label><input class="fi" type="email" name="email" required autocomplete="email" placeholder="admin@nexvest.com"/></div>
  <div class="fg"><label class="fl">Password</label><input class="fi" type="password" name="password" required placeholder="••••••••"/></div>
  <button type="submit" class="btn btn-primary btn-block btn-lg" id="al-btn" style="background:#C0392B">Sign In to Admin Panel</button>
</form>
<script>
document.getElementById('admin-login-form').addEventListener('submit', async e => {
  e.preventDefault();
  const btn  = document.getElementById('al-btn');
  setLoading(btn, true);
  const fd   = new FormData(e.target);
  const data = await post('/admin/login', fd, true);
  setLoading(btn, false);
  if (data.success) window.location.href = data.redirect || '/admin/dashboard';
  else document.getElementById('al-alert').innerHTML = '<div class="alert alert-err">' + (data.error||'Login failed.') + '</div>';
});
</script>
