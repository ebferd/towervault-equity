<?php require_once ROOT . '/views/components/helpers.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<title><?= htmlspecialchars($title ?? 'Fund Protection') ?> — <?= htmlspecialchars(platform_setting('platform_name','NexVest')) ?></title>
<meta name="description" content="<?= htmlspecialchars($meta_desc ?? 'How investor funds are protected, ring-fenced and insured.') ?>"/>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<?= favicon_tag() ?>
<style>
  *,*::before,*::after{box-sizing:border-box}
  body{margin:0;font-family:Inter,system-ui,sans-serif;background:#F6F9F7;color:#0E1B15}
  .pp-nav{position:sticky;top:0;z-index:50;display:flex;align-items:center;justify-content:space-between;gap:14px;
    padding:14px 24px;background:rgba(255,255,255,.86);backdrop-filter:blur(12px);border-bottom:1px solid #E3EAE6}
  .pp-brand{display:flex;align-items:center;gap:10px;text-decoration:none;color:inherit}
  .pp-mark{width:34px;height:34px;border-radius:9px;background:#059669;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;box-shadow:0 6px 16px -6px rgba(5,150,105,.6)}
  .pp-logo{height:36px;width:auto;max-width:190px;object-fit:contain;display:block}
  .pp-name{font-weight:700;font-size:15px;letter-spacing:-.2px}
  .pp-actions{display:flex;align-items:center;gap:9px}
  .pp-btn{display:inline-flex;align-items:center;height:38px;padding:0 16px;border-radius:9px;font-size:13px;font-weight:600;text-decoration:none;border:1px solid transparent;white-space:nowrap}
  .pp-btn.ghost{color:#0E1B15;border-color:#E3EAE6;background:#fff}
  .pp-btn.ghost:hover{border-color:#cdd8d1}
  .pp-btn.solid{background:#059669;color:#fff;box-shadow:0 10px 22px -12px rgba(5,150,105,.7)}
  .pp-btn.solid:hover{background:#047857}
  .pp-wrap{max-width:1000px;margin:0 auto;padding:52px 20px 20px}
  .pp-foot{max-width:1000px;margin:0 auto;padding:26px 20px 44px;border-top:1px solid #E3EAE6;margin-top:26px;
    display:flex;flex-wrap:wrap;gap:12px 20px;align-items:center;justify-content:space-between;font-size:12px;color:#8B988F}
  .pp-foot a{color:#55635B;text-decoration:none;font-weight:500}
  .pp-foot a:hover{color:#047857}
  .pp-foot .lx{display:flex;gap:16px;flex-wrap:wrap}
  @media(max-width:560px){.pp-name{display:none}.pp-wrap{padding:34px 12px 18px}.pp-btn.pp-hide-sm{display:none}.pp-btn{height:36px;padding:0 13px;font-size:12.5px}}
</style>
</head>
<body>
<?php
  $pName = platform_setting('platform_name', 'NexVest');
  $pInit = platform_setting('platform_initials', 'N');
  $pLogo = platform_setting('platform_logo', '');
  // Marketing homepage: strip protocol and any app subdomain (equity./app./…)
  // from the configured website so we link to the main site, not the portal.
  $home = preg_replace('#^https?://#i', '', rtrim((string) platform_setting('platform_website', ''), '/'));
  $home = preg_replace('#^(equity|app|portal|dashboard|my|invest)\.#i', '', $home);
  $home = $home !== '' ? 'https://' . $home : 'https://towervaultequity.com';
?>
<nav class="pp-nav">
  <a class="pp-brand" href="<?= htmlspecialchars($home) ?>">
    <?php if ($pLogo): ?><img class="pp-logo" src="<?= file_url($pLogo) ?>" alt="<?= htmlspecialchars($pName) ?>"/>
    <?php else: ?><span class="pp-mark"><?= htmlspecialchars($pInit) ?></span><?php endif; ?>
    <span class="pp-name"><?= htmlspecialchars($pName) ?></span>
  </a>
  <div class="pp-actions">
    <a class="pp-btn ghost" href="<?= htmlspecialchars($home) ?>">Homepage</a>
    <a class="pp-btn ghost pp-hide-sm" href="/login">Sign in</a>
    <a class="pp-btn solid" href="/register">Create account</a>
  </div>
</nav>

<div class="pp-wrap">
  <?= $content ?>
</div>

<footer class="pp-foot">
  <div>&copy; <?= date('Y') ?> <?= htmlspecialchars($pName) ?>. All rights reserved.</div>
  <div class="lx">
    <a href="/investor/fund-protection">Fund Protection</a>
    <a href="/terms">Terms</a>
    <a href="/privacy">Privacy</a>
    <a href="/register">Get started</a>
  </div>
</footer>
</body>
</html>
