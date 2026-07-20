<?php
$pName    = platform_setting('platform_name', 'NexVest');
$pInit    = platform_setting('platform_initials', 'NV');
$msg      = platform_setting('maintenance_message', 'We are performing scheduled maintenance. We will be back shortly.');
$logoPath = platform_setting('platform_logo', '');
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Under Maintenance — <?= htmlspecialchars($pName) ?></title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#0f1623;color:#e2e8f0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem}
.wrap{max-width:520px;width:100%;text-align:center}
.logo{width:64px;height:64px;background:linear-gradient(135deg,#10b981,#059669);border-radius:18px;display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:900;color:#fff;margin:0 auto 2rem;letter-spacing:-.5px}
.gear{display:flex;gap:14px;justify-content:center;margin-bottom:2.5rem}
.gear svg{animation:spin 3s linear infinite}
.gear svg:nth-child(2){animation-duration:2s;animation-direction:reverse;opacity:.6}
@keyframes spin{to{transform:rotate(360deg)}}
h1{font-size:1.75rem;font-weight:800;letter-spacing:-.4px;margin-bottom:.75rem;color:#f8fafc}
p{font-size:15px;color:#94a3b8;line-height:1.65;margin-bottom:2rem;max-width:400px;margin-left:auto;margin-right:auto}
.badge{display:inline-flex;align-items:center;gap:.5rem;background:#1e293b;border:1px solid #334155;border-radius:100px;padding:.45rem 1rem;font-size:12px;font-weight:600;color:#94a3b8;margin-bottom:.85rem}
.badge-dot{width:7px;height:7px;background:#f59e0b;border-radius:50%;animation:pulse 1.5s ease-in-out infinite}
@keyframes pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.5;transform:scale(.8)}}
.footer{margin-top:3rem;font-size:11.5px;color:#475569}
</style>
</head>
<body>
<div class="wrap">
  <?php if ($logoPath): ?>
    <img src="<?= htmlspecialchars($logoPath) ?>" style="height:56px;margin:0 auto 2rem;display:block" alt="<?= htmlspecialchars($pName) ?>"/>
  <?php else: ?>
    <div class="logo"><?= htmlspecialchars($pInit) ?></div>
  <?php endif; ?>

  <div class="badge">
    <span class="badge-dot"></span>
    Maintenance in progress
  </div>

  <div class="gear">
    <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="1.6">
      <circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
    </svg>
    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="1.6">
      <circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
    </svg>
  </div>

  <h1><?= htmlspecialchars($pName) ?> is getting an upgrade</h1>
  <p><?= htmlspecialchars($msg) ?></p>

  <div class="footer">
    &copy; <?= date('Y') ?> <?= htmlspecialchars($pName) ?> &mdash; Your investments are safe.
  </div>
</div>
</body>
</html>
