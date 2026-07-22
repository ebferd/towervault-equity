<?php require_once ROOT . '/views/components/helpers.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<meta name="csrf-token" content="<?= csrf_token() ?>"/>
<title><?= htmlspecialchars($title ?? 'Admin') ?> — <?= htmlspecialchars(platform_setting('platform_name','NexVest')) ?> Admin</title>
<?= favicon_tag() ?><link rel="stylesheet" href="/assets/css/app.css?v=<?= filemtime(ROOT.'/assets/css/app.css') ?>"/>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
  .goog-te-banner-frame,.skiptranslate{display:none!important}
  body{top:0!important}
  #google_translate_element .goog-te-gadget{font-size:0}
  #google_translate_element select{background:rgba(255,255,255,.08);color:rgba(255,255,255,.7);border:1px solid rgba(255,255,255,.15);border-radius:6px;padding:4px 8px;font-size:11.5px;cursor:pointer;outline:none}
  #google_translate_element select:hover{background:rgba(255,255,255,.14)}
  :root { --accent:#C0392B; --accent-h:#a93226; --accent-l:#FDF2F2; --accent-m:#FCCACA; --accent-glow:rgba(192,57,43,.15); }
  .sb-item.active { background:rgba(192,57,43,.2); }
  .sb-item.active::before { background:linear-gradient(180deg,var(--red),#e05c50); }
  .sb-badge { background:linear-gradient(135deg,var(--red),#a93226); }
  .tab.active { color:#C0392B; border-bottom-color:#C0392B; }
  .btn-primary { background:linear-gradient(135deg,#C0392B,#a93226); }
  .tb-balance:hover { border-color:rgba(192,57,43,.4); box-shadow:0 0 0 3px rgba(192,57,43,.1); }
</style>
</head>
<body>
<?php
$pName    = platform_setting('platform_name','NexVest');
$pInit    = platform_setting('platform_initials','NV');
$adminName= $_SESSION['admin_name']  ?? 'Admin';
$adminRole= $_SESSION['admin_role']  ?? 'admin';
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$pendingKyc = (int)((DB::fetch("SELECT COUNT(*) AS c FROM kyc_submissions WHERE status='pending'") ?? [])['c'] ?? 0);
$pendingWr  = (int)((DB::fetch("SELECT COUNT(*) AS c FROM withdrawal_requests WHERE status='pending'") ?? [])['c'] ?? 0);
$openTix    = (int)((DB::fetch("SELECT COUNT(*) AS c FROM support_tickets WHERE status='open'") ?? [])['c'] ?? 0);
$pendingDep = (int)((DB::fetch("SELECT COUNT(*) AS c FROM deposit_invoices WHERE status='submitted'") ?? [])['c'] ?? 0);

$nav = [
  ['path'=>'/admin/dashboard',    'label'=>'Dashboard',        'icon'=>'overview'],
  ['section'=>'Management'],
  ['path'=>'/admin/users',        'label'=>'Investors',        'icon'=>'users'],
  ['path'=>'/admin/kyc',          'label'=>'KYC Queue',        'icon'=>'shield',   'badge'=>$pendingKyc],
  ['path'=>'/admin/investments',  'label'=>'Investments',      'icon'=>'building'],
  ['section'=>'Finance'],
  ['path'=>'/admin/deposits',     'label'=>'Deposits',         'icon'=>'arrowDown','badge'=>$pendingDep],
  ['path'=>'/admin/withdrawals',  'label'=>'Withdrawals',      'icon'=>'arrowUp',  'badge'=>$pendingWr],
  ['path'=>'/admin/invoices',     'label'=>'Invoices',         'icon'=>'file'],
  ['section'=>'Support'],
  ['path'=>'/admin/tickets',      'label'=>'Support Tickets',  'icon'=>'ticket',   'badge'=>$openTix],
  ['section'=>'System'],
  ['path'=>'/admin/reports',      'label'=>'Reports',          'icon'=>'doc'],
  ['path'=>'/admin/audit',        'label'=>'Audit Log',        'icon'=>'log'],
  ['path'=>'/admin/settings',     'label'=>'Settings',         'icon'=>'settings'],
];
?>
<div class="shell">
  <div class="sb-overlay" id="sb-overlay"></div>
  <nav class="sidebar" id="sidebar">
    <div class="sb-logo">
      <div class="sb-mark" style="background:#C0392B">A</div>
      <div>
        <div class="sb-name"><?= htmlspecialchars($pName) ?></div>
        <span class="sb-sub">Admin Panel</span>
        <div class="sb-admin-badge"><?= svgIcon('shield',9,'#F4A8A8') ?>Administrator</div>
      </div>
    </div>
    <div class="sb-nav">
      <?php foreach ($nav as $item): ?>
        <?php if (isset($item['section'])): ?>
          <div class="sb-section"><?= htmlspecialchars($item['section']) ?></div>
        <?php else:
          $isActive = str_starts_with($currentPath, $item['path']);
          $iconColor = $isActive ? 'rgba(255,255,255,.95)' : 'rgba(255,255,255,.44)';
        ?>
          <a href="<?= $item['path'] ?>" class="sb-item<?= $isActive ? ' active' : '' ?>">
            <?= svgIcon($item['icon'],15,$iconColor) ?>
            <?= htmlspecialchars($item['label']) ?>
            <?php if (!empty($item['badge']) && $item['badge'] > 0): ?>
              <span class="sb-badge red"><?= $item['badge'] ?></span>
            <?php endif; ?>
          </a>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
    <div class="sb-bottom">
      <div class="sb-user">
        <div class="sb-avatar" style="background:#C0392B"><?= strtoupper(substr($adminName,0,2)) ?></div>
        <div>
          <div class="sb-uname"><?= htmlspecialchars($adminName) ?></div>
          <div class="sb-uemail"><?= htmlspecialchars(str_replace('_',' ',ucwords($adminRole,'_'))) ?></div>
        </div>
      </div>
      <div id="google_translate_element" style="margin-bottom:.6rem"></div>
      <form method="POST" action="/admin/logout">
        <input type="hidden" name="_token" value="<?= csrf_token() ?>"/>
        <button type="submit" class="sb-logout"><?= svgIcon('logout',14,'rgba(255,255,255,.3)') ?>Sign Out</button>
      </form>
    </div>
  </nav>
  <div class="main">
    <header class="topbar">
      <div class="tb-left">
        <button class="hamburger" id="hamburger"><?= svgIcon('menu',20,'var(--text2)') ?></button>
        <div>
          <div class="tb-page"><?= htmlspecialchars($title ?? 'Admin') ?></div>
          <div class="tb-bc"><?= htmlspecialchars($pName) ?> <span class="tb-sep">·</span> Admin Panel</div>
        </div>
      </div>
      <div class="tb-right">
        <span class="tb-clock" id="topbar-clock"></span>
        <?php $totalPending = $pendingKyc + $pendingWr + $openTix + $pendingDep; ?>
        <div style="position:relative">
          <button id="admin-notif-btn" style="position:relative;background:none;border:none;cursor:pointer;padding:6px;border-radius:8px;display:flex;align-items:center;color:var(--text2)" title="Pending actions">
            <?= svgIcon('bell',18,'var(--text2)') ?>
            <?php if ($totalPending > 0): ?>
              <span style="position:absolute;top:2px;right:2px;background:#C0392B;color:#fff;font-size:9px;font-weight:700;border-radius:50%;width:16px;height:16px;display:flex;align-items:center;justify-content:center;line-height:1"><?= min($totalPending,99) ?></span>
            <?php endif; ?>
          </button>
          <div id="admin-notif-menu" style="display:none;position:absolute;right:0;top:calc(100% + 6px);width:240px;background:var(--surface);border:1px solid var(--border);border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.12);z-index:200;overflow:hidden">
            <div style="padding:10px 14px;font-size:11px;font-weight:600;color:var(--text3);border-bottom:1px solid var(--border);text-transform:uppercase;letter-spacing:.05em">Pending Actions</div>
            <?php if ($pendingDep > 0): ?><a href="/admin/deposits" style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;font-size:13px;color:var(--text);text-decoration:none;border-bottom:1px solid var(--border)"><?= svgIcon('arrowDown',13,'var(--text2)') ?><span style="flex:1;margin-left:8px">Deposits</span><span style="background:#C0392B;color:#fff;border-radius:50px;padding:1px 7px;font-size:11px;font-weight:700"><?= $pendingDep ?></span></a><?php endif; ?>
            <?php if ($pendingWr > 0): ?><a href="/admin/withdrawals" style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;font-size:13px;color:var(--text);text-decoration:none;border-bottom:1px solid var(--border)"><?= svgIcon('arrowUp',13,'var(--text2)') ?><span style="flex:1;margin-left:8px">Withdrawals</span><span style="background:#C0392B;color:#fff;border-radius:50px;padding:1px 7px;font-size:11px;font-weight:700"><?= $pendingWr ?></span></a><?php endif; ?>
            <?php if ($pendingKyc > 0): ?><a href="/admin/kyc" style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;font-size:13px;color:var(--text);text-decoration:none;border-bottom:1px solid var(--border)"><?= svgIcon('shield',13,'var(--text2)') ?><span style="flex:1;margin-left:8px">KYC Reviews</span><span style="background:#C0392B;color:#fff;border-radius:50px;padding:1px 7px;font-size:11px;font-weight:700"><?= $pendingKyc ?></span></a><?php endif; ?>
            <?php if ($openTix > 0): ?><a href="/admin/tickets" style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;font-size:13px;color:var(--text);text-decoration:none"><?= svgIcon('headset',13,'var(--text2)') ?><span style="flex:1;margin-left:8px">Support Tickets</span><span style="background:#C0392B;color:#fff;border-radius:50px;padding:1px 7px;font-size:11px;font-weight:700"><?= $openTix ?></span></a><?php endif; ?>
            <?php if ($totalPending === 0): ?><div style="padding:16px 14px;font-size:13px;color:var(--text3);text-align:center">All caught up!</div><?php endif; ?>
          </div>
        </div>
        <span class="tb-admin-pill">ADMIN</span>
        <button class="btn btn-outline btn-sm" onclick="document.getElementById('announce-modal').style.display='flex'">
          <?= svgIcon('bell',13) ?>Broadcast
        </button>
      </div>
    </header>
    <main class="content">
      <?php renderFlash(); ?>
      <?= $content ?>
    </main>
  </div>
</div>

<!-- Announce Modal -->
<div id="announce-modal" class="modal-overlay" style="display:none">
  <div class="modal">
    <div class="modal-head">
      <h3 class="modal-title">Broadcast Announcement</h3>
      <button class="modal-close" onclick="document.getElementById('announce-modal').style.display='none'">&times;</button>
    </div>
    <div class="modal-body">
      <div id="announce-result"></div>
      <form id="announce-form">
        <div class="fg"><label class="fl">Target Audience</label>
          <select class="fsel" name="target"><option value="all">All Investors</option><option value="active">Active Investors</option><option value="verified">KYC Verified Only</option></select>
        </div>
        <div class="fg"><label class="fl">Subject</label><input class="fi" name="subject" placeholder="e.g. Platform Maintenance Notice" required/></div>
        <div class="fg"><label class="fl">Message</label><textarea class="fta" name="message" style="min-height:120px" placeholder="Write your announcement…" required></textarea></div>
        <div style="display:flex;gap:.65rem">
          <button type="submit" class="btn btn-primary"><?= svgIcon('send',13,'#fff') ?>Send to Investors</button>
          <button type="button" class="btn btn-outline" onclick="document.getElementById('announce-modal').style.display='none'">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script src="/assets/js/app.js?v=<?= filemtime(ROOT.'/assets/js/app.js') ?>"></script>
<script>
var adminNotifBtn = document.getElementById('admin-notif-btn');
var adminNotifMenu = document.getElementById('admin-notif-menu');
if (adminNotifBtn) {
  adminNotifBtn.addEventListener('click', function(e) { e.stopPropagation(); adminNotifMenu.style.display = adminNotifMenu.style.display === 'none' ? 'block' : 'none'; });
  document.addEventListener('click', function() { adminNotifMenu.style.display = 'none'; });
}

document.getElementById('announce-form').addEventListener('submit', async e => {
  e.preventDefault();
  const btn = e.target.querySelector('[type="submit"]');
  setLoading(btn, true);
  const fd = new FormData(e.target);
  const data = await post('/admin/announce', fd, true);
  setLoading(btn, false);
  document.getElementById('announce-result').innerHTML =
    data.success
      ? '<div class="alert alert-ok">' + data.message + '</div>'
      : '<div class="alert alert-err">' + (data.error || 'Failed.') + '</div>';
  if (data.success) { e.target.reset(); setTimeout(() => { document.getElementById('announce-modal').style.display='none'; document.getElementById('announce-result').innerHTML=''; }, 3000); }
});
</script>
<script type="text/javascript">
function googleTranslateElementInit(){
  new google.translate.TranslateElement({pageLanguage:'en',layout:google.translate.TranslateElement.InlineLayout.SIMPLE},'google_translate_element');
}
</script>
<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
</body>
</html>
