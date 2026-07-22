<?php require_once ROOT . '/views/components/helpers.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<meta name="csrf-token" content="<?= csrf_token() ?>"/>
<title><?= htmlspecialchars($title ?? 'Admin Login') ?> — <?= htmlspecialchars(platform_setting('platform_name','NexVest')) ?></title>
<?= favicon_tag() ?><link rel="stylesheet" href="/assets/css/app.css?v=<?= filemtime(ROOT.'/assets/css/app.css') ?>"/>
</head>
<body style="background:var(--navy);display:flex;align-items:center;justify-content:center;min-height:100vh;padding:2rem">
<div style="width:100%;max-width:380px">
  <div style="text-align:center;margin-bottom:2rem">
    <?php $adminLogo = platform_setting('platform_logo', ''); ?>
    <?php if ($adminLogo): ?>
      <img src="<?= file_url($adminLogo) ?>" alt="<?= htmlspecialchars(platform_setting('platform_name','')) ?>" style="width:44px;height:44px;object-fit:contain;border-radius:6px;margin:0 auto .75rem;display:block"/>
    <?php else: ?>
      <div style="width:44px;height:44px;background:#C0392B;border-radius:3px;display:flex;align-items:center;justify-content:center;margin:0 auto .75rem;font-family:'DM Serif Display',serif;font-size:18px;color:#fff"><?= htmlspecialchars(platform_setting('platform_initials','NV')) ?></div>
    <?php endif; ?>
    <div style="font-size:16px;font-weight:700;color:#fff"><?= htmlspecialchars(platform_setting('platform_name','NexVest')) ?></div>
    <div style="font-size:9px;letter-spacing:2.5px;text-transform:uppercase;color:rgba(255,255,255,.3);margin-top:2px">Admin Panel</div>
  </div>
  <div style="background:#fff;border-radius:3px;padding:2rem">
    <?php renderFlash(); ?>
    <?= $content ?>
  </div>
  <div style="text-align:center;margin-top:1.5rem;font-size:11px;color:rgba(255,255,255,.25)">Restricted Access — Authorised Personnel Only</div>
</div>
<script src="/assets/js/app.js"></script>
</body>
</html>
