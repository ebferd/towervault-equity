<?php require_once ROOT . '/views/components/helpers.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<meta name="csrf-token" content="<?= csrf_token() ?>"/>
<title><?= htmlspecialchars($title ?? 'Dashboard') ?> — <?= htmlspecialchars(platform_setting('platform_name','NexVest')) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<?= favicon_tag() ?><link rel="stylesheet" href="/assets/css/app-v2.css?v=<?= filemtime(ROOT.'/assets/css/app-v2.css') ?>"/>
<!-- PWA / installable app -->
<link rel="manifest" href="/manifest.webmanifest"/>
<meta name="theme-color" content="#059669"/>
<meta name="mobile-web-app-capable" content="yes"/>
<meta name="apple-mobile-web-app-capable" content="yes"/>
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent"/>
<meta name="apple-mobile-web-app-title" content="<?= htmlspecialchars(platform_setting('platform_name','NexVest')) ?>"/>
<?php $pwaIcon = platform_setting('platform_logo','') ?: platform_setting('platform_favicon',''); if ($pwaIcon): ?>
<link rel="apple-touch-icon" href="<?= htmlspecialchars(file_url_abs($pwaIcon)) ?>"/>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body class="app">
<?php
$pName     = platform_setting('platform_name', 'NexVest');
$pInit     = platform_setting('platform_initials', 'N');
$pLogo     = platform_setting('platform_logo', '');
$uid       = current_user_id();
$userName  = $_SESSION['user_name']  ?? 'Investor';
$userEmail = $_SESSION['user_email'] ?? '';
$nameParts = preg_split('/\s+/', trim($userName));
$userInit  = strtoupper(substr($nameParts[0] ?? 'I', 0, 1) . substr($nameParts[1] ?? '', 0, 1));
$balance   = (float)((DB::fetch("SELECT wallet_balance FROM users WHERE id=?", [$uid]) ?? [])['wallet_balance'] ?? 0);
$unread    = (int)((DB::fetch("SELECT COUNT(*) AS c FROM notifications WHERE user_id=? AND is_read=0", [$uid]) ?? [])['c'] ?? 0);
$notifPreview = DB::fetchAll("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 4", [$uid]);
$isGhost   = $_SESSION['is_ghost'] ?? false;
$kycStatus = $_SESSION['kyc_status'] ?? 'not_submitted';
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$nav = [
  ['section' => 'Overview'],
  ['path' => '/investor/dashboard', 'label' => 'Dashboard', 'icon' => 'grid'],
  ['path' => '/investor/how-it-works', 'label' => 'How it works', 'icon' => 'info'],
  ['section' => 'Invest'],
  ['path' => '/investor/investments', 'label' => 'Investments', 'icon' => 'building'],
  ['path' => '/investor/portfolio', 'label' => 'Portfolio', 'icon' => 'briefcase'],
  ['path' => '/investor/calculator', 'label' => 'Calculator', 'icon' => 'calc'],
  ['section' => 'Finance'],
  ['path' => '/investor/wallet', 'label' => 'Wallet', 'icon' => 'wallet'],
  ['path' => '/investor/transactions', 'label' => 'Transactions', 'icon' => 'swap'],
  ['path' => '/investor/certificates', 'label' => 'Certificates', 'icon' => 'doc'],
  ['section' => 'Account'],
  ...(platform_setting('kyc_enabled','1') === '1' ? [['path' => '/investor/kyc', 'label' => 'Identity (KYC)', 'icon' => 'shield', 'badge' => in_array($kycStatus, ['not_submitted','rejected'], true) ? '!' : null]] : []),
  ['path' => '/investor/profile', 'label' => 'Profile', 'icon' => 'user'],
  ['path' => '/investor/referrals', 'label' => 'Referrals', 'icon' => 'gift'],
  ['path' => '/investor/support', 'label' => 'Support', 'icon' => 'headset'],
];

?>
<div class="shell">
  <div class="sb-overlay" id="sb-overlay"></div>

  <nav class="sidebar" id="sidebar">
    <div class="sb-logo">
      <?php if ($pLogo): ?>
        <img src="<?= file_url($pLogo) ?>" alt="" style="width:30px;height:30px;object-fit:contain;border-radius:8px"/>
      <?php else: ?>
        <div class="logo-mark"><?= htmlspecialchars($pInit) ?></div>
      <?php endif; ?>
      <span class="sb-name"><?= htmlspecialchars($pName) ?></span>
    </div>

    <div class="sb-nav">
      <?php foreach ($nav as $item): ?>
        <?php if (isset($item['section'])): ?>
          <div class="sb-section"><?= htmlspecialchars($item['section']) ?></div>
        <?php else:
          $isActive = str_starts_with($currentPath, $item['path']);
        ?>
          <a href="<?= htmlspecialchars($item['path']) ?>" class="sb-item<?= $isActive ? ' active' : '' ?>">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><?= nv_icon($item['icon']) ?></svg>
            <?= htmlspecialchars($item['label']) ?>
            <?php if (!empty($item['badge'])): ?><span class="sb-badge warn"><?= htmlspecialchars((string)$item['badge']) ?></span><?php endif; ?>
          </a>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>

    <div class="sb-bottom">
      <a href="/investor/profile" class="sb-user">
        <div class="sb-avatar"><?= htmlspecialchars($userInit) ?></div>
        <div style="min-width:0">
          <div class="sb-uname"><?= htmlspecialchars($userName) ?></div>
          <div class="sb-uemail"><?= htmlspecialchars($userEmail) ?></div>
        </div>
      </a>
      <form method="POST" action="/logout">
        <input type="hidden" name="_token" value="<?= csrf_token() ?>"/>
        <button type="submit" class="sb-logout">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><?= nv_icon('logout') ?></svg>
          Sign out
        </button>
      </form>
    </div>
  </nav>

  <div class="main">
    <header class="topbar">
      <div class="tb-left">
        <button class="hamburger" id="hamburger" aria-label="Open menu">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><?= nv_icon('menu') ?></svg>
        </button>
        <div>
          <div class="tb-page"><?= htmlspecialchars($title ?? 'Dashboard') ?></div>
          <div class="tb-bc"><?= htmlspecialchars($pName) ?> &middot; Investor Portal</div>
        </div>
      </div>
      <div class="tb-right">
        <?php $langVariant='light'; include ROOT.'/views/components/lang_switcher.php'; ?>

        <div class="dropdown">
          <button class="tb-icon-btn" id="notif-btn" aria-label="Notifications">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><?= nv_icon('bell') ?></svg>
            <?php if ($unread > 0): ?><span class="tb-dot"></span><?php endif; ?>
          </button>
          <div class="dropdown-menu" id="notif-menu">
            <div class="dd-head">
              <span>Notifications</span>
              <?php if ($unread > 0): ?><button type="button" id="mark-read-btn">Mark all read</button><?php endif; ?>
            </div>
            <?php if (empty($notifPreview)): ?>
              <div class="dd-empty">You're all caught up.</div>
            <?php else: foreach ($notifPreview as $n): [$icoName, $icoColor, $icoBg] = nv_notif_icon($n['type']); ?>
              <div class="dd-item">
                <div class="dd-icon" style="background:<?= $icoBg ?>"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="<?= $icoColor ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><?= nv_icon($icoName) ?></svg></div>
                <div><div class="dd-title"><?= htmlspecialchars($n['title']) ?></div><div class="dd-time"><?= time_ago($n['created_at']) ?></div></div>
              </div>
            <?php endforeach; endif; ?>
            <a href="/investor/notifications" class="see-all">View all notifications</a>
          </div>
        </div>

        <a href="/investor/wallet" class="tb-balance">
          <span class="tb-balance-lbl">Balance</span>
          <span class="tb-balance-val"><?= fmt_currency($balance) ?></span>
        </a>
        <a href="/investor/profile" class="tb-avatar"><?= htmlspecialchars($userInit) ?></a>
      </div>
    </header>

    <?php if ($isGhost): ?>
    <div style="background:var(--ink-900);color:#fff;padding:.6rem 1.5rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;font-size:12.5px">
      <span style="display:flex;align-items:center;gap:8px"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><?= nv_icon('eye') ?></svg>Viewing as <strong><?= htmlspecialchars($userName) ?></strong> — ghost login active. All actions are logged.</span>
      <a href="/admin/ghost/exit" style="color:var(--em-400);font-weight:600">Exit ghost mode</a>
    </div>
    <?php endif; ?>

    <main class="content">
      <?php renderFlash(); ?>
      <?= $content ?>
    </main>
  </div>
</div>

<script src="/assets/js/app.js?v=<?= filemtime(ROOT.'/assets/js/app.js') ?>"></script>
<script>
  var notifBtn = document.getElementById('notif-btn');
  var notifMenu = document.getElementById('notif-menu');
  notifBtn.addEventListener('click', function (e) {
    e.stopPropagation();
    notifMenu.classList.toggle('show');
  });
  document.addEventListener('click', function () { notifMenu.classList.remove('show'); });

  var markReadBtn = document.getElementById('mark-read-btn');
  if (markReadBtn) {
    markReadBtn.addEventListener('click', async function () {
      await post('/investor/notifications/read', {});
      window.location.reload();
    });
  }
</script>
<?php render_live_chat(); ?>

<!-- ── Install app (PWA) ─────────────────────────────────── -->
<button id="pwa-install" hidden aria-label="Install app">
  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12"/><polyline points="7 10 12 15 17 10"/><line x1="5" y1="21" x2="19" y2="21"/></svg>
  Install app
</button>
<div id="pwa-ios" hidden>
  <div class="pwa-ios-card">
    <button id="pwa-ios-close" aria-label="Close">&times;</button>
    <div class="pwa-ios-h">Install <?= htmlspecialchars(platform_setting('platform_name','NexVest')) ?></div>
    <p class="pwa-ios-sub">Add it to your home screen for quick, full-screen access:</p>
    <ol class="pwa-ios-steps">
      <li>Tap the <b>Share</b> icon <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-3px"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><polyline points="16 6 12 2 8 6"/><line x1="12" y1="2" x2="12" y2="15"/></svg> at the bottom of Safari</li>
      <li>Scroll down and tap <b>Add to Home Screen</b></li>
      <li>Tap <b>Add</b> — done!</li>
    </ol>
    <p class="pwa-ios-note">Opened from WhatsApp/Instagram? Tap <b>&#8943;</b> and choose <b>Open in Safari</b> first.</p>
  </div>
</div>
<style>
  #pwa-install{position:fixed;left:16px;bottom:16px;z-index:9000;display:inline-flex;align-items:center;gap:7px;
    background:#059669;color:#fff;border:none;border-radius:999px;padding:11px 18px;font-size:14px;font-weight:600;
    font-family:'Inter',system-ui,sans-serif;box-shadow:0 8px 22px -6px rgba(5,150,105,.6);cursor:pointer}
  #pwa-install:active{transform:translateY(1px)}
  #pwa-ios{position:fixed;inset:0;z-index:9001;background:rgba(7,11,20,.55);display:flex;align-items:flex-end;justify-content:center}
  .pwa-ios-card{background:#fff;width:100%;max-width:460px;border-radius:18px 18px 0 0;padding:22px 22px 30px;position:relative;font-family:'Inter',system-ui,sans-serif}
  @media(min-width:520px){#pwa-ios{align-items:center}.pwa-ios-card{border-radius:18px}}
  #pwa-ios-close{position:absolute;top:12px;right:14px;background:none;border:none;font-size:26px;line-height:1;color:#9aa4b8;cursor:pointer}
  .pwa-ios-h{font-size:17px;font-weight:700;color:#0B1120;margin-bottom:4px}
  .pwa-ios-sub{font-size:13.5px;color:#5b6472;margin:0 0 14px}
  .pwa-ios-steps{margin:0;padding-left:20px;font-size:14px;color:#1f2937;line-height:1.9}
  .pwa-ios-note{font-size:12px;color:#8b939f;margin:14px 0 0;line-height:1.5}
</style>
<script>
(function(){
  if ('serviceWorker' in navigator) { navigator.serviceWorker.register('/sw.js').catch(function(){}); }
  var standalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
  if (standalone) return; // already installed — don't nag
  var btn = document.getElementById('pwa-install');
  var iosModal = document.getElementById('pwa-ios');
  var iosClose = document.getElementById('pwa-ios-close');
  var deferred = null;
  var isIOS = /iphone|ipad|ipod/i.test(navigator.userAgent);
  if (sessionStorage.getItem('pwa-dismissed') === '1') return;

  window.addEventListener('beforeinstallprompt', function(e){ e.preventDefault(); deferred = e; if (btn) btn.hidden = false; });
  if (isIOS && btn) btn.hidden = false; // iOS gives no event — always offer the guide

  if (btn) btn.addEventListener('click', async function(){
    if (deferred) { deferred.prompt(); var choice = await deferred.userChoice; deferred = null; if (choice.outcome === 'accepted') btn.hidden = true; }
    else if (iosModal) { iosModal.hidden = false; }
  });
  if (iosClose) iosClose.addEventListener('click', function(){ iosModal.hidden = true; sessionStorage.setItem('pwa-dismissed','1'); if (btn) btn.hidden = true; });
  if (iosModal) iosModal.addEventListener('click', function(e){ if (e.target === iosModal) iosModal.hidden = true; });
  window.addEventListener('appinstalled', function(){ if (btn) btn.hidden = true; });
})();
</script>
</body>
</html>
