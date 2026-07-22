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
        <div id="google_translate_element"></div>

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
<script type="text/javascript">
function googleTranslateElementInit(){
  new google.translate.TranslateElement({pageLanguage:'en',layout:google.translate.TranslateElement.InlineLayout.SIMPLE},'google_translate_element');
}
</script>
<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
<?php render_live_chat(); ?>
</body>
</html>
