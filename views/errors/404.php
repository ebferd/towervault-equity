<?php /* views/errors/404.php */ ?>
<div style="text-align:center;max-width:420px">
  <div style="font-family:'DM Serif Display',serif;font-size:5rem;color:var(--border2);line-height:1;margin-bottom:.5rem">404</div>
  <h2 style="color:var(--text);margin-bottom:.5rem">Page Not Found</h2>
  <p style="color:var(--text2);margin-bottom:1.75rem;font-size:14px">The page you're looking for doesn't exist or has been moved.</p>
  <div style="display:flex;gap:.65rem;justify-content:center;flex-wrap:wrap">
    <a href="javascript:history.back()" class="btn btn-outline">Go Back</a>
    <?php if (is_logged_in()): ?>
      <a href="/investor/dashboard" class="btn btn-primary">Dashboard</a>
    <?php elseif (is_admin()): ?>
      <a href="/admin/dashboard" class="btn btn-primary">Admin Panel</a>
    <?php else: ?>
      <a href="/login" class="btn btn-primary">Sign In</a>
    <?php endif; ?>
  </div>
</div>
