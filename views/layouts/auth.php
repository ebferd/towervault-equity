<?php require_once ROOT . '/views/components/helpers.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<meta name="csrf-token" content="<?= csrf_token() ?>"/>
<title><?= htmlspecialchars($title ?? 'NexVest') ?> — <?= htmlspecialchars(platform_setting('platform_name','NexVest')) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/auth.css?v=<?= filemtime(ROOT.'/assets/css/auth.css') ?>"/>
</head>
<body class="auth-body">
<?php
$pName    = platform_setting('platform_name', 'NexVest');
$pInit    = platform_setting('platform_initials', 'N');
$pTagline = platform_setting('platform_tagline', 'Capital Group');

$authHeadline = platform_setting('auth_headline', 'Capital that compounds quietly.');
$authSubtext  = platform_setting('auth_subtext', 'Real estate and index-fund investments, managed transparently — with the reporting and controls institutional investors expect.');
$authStats = [
  [platform_setting('auth_stat1_value', '$420M+'),  platform_setting('auth_stat1_label', 'Capital deployed')],
  [platform_setting('auth_stat2_value', '12,400+'), platform_setting('auth_stat2_label', 'Active investors')],
  [platform_setting('auth_stat3_value', '4.9/5'),   platform_setting('auth_stat3_label', 'Investor rating')],
];
?>
<div class="shell auth-shell">

  <!-- ============ BRAND PANEL (desktop only) ============ -->
  <aside class="brand">
    <div class="brand-logo">
      <div class="logo-mark"><?= htmlspecialchars($pInit) ?></div>
      <span class="logo-word"><?= htmlspecialchars($pName) ?></span>
    </div>

    <div class="brand-mid">
      <p class="eyebrow">Investor Portal</p>
      <h1 class="headline serif"><?= nl2br(htmlspecialchars($authHeadline)) ?></h1>
      <p class="sub"><?= htmlspecialchars($authSubtext) ?></p>

      <svg viewBox="0 0 320 90" class="spark" fill="none" aria-hidden="true">
        <path d="M0 70 L40 62 L80 68 L120 40 L160 48 L200 22 L240 30 L280 8 L320 14"
              stroke="#34D399" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="spark-line"/>
        <path d="M0 70 L40 62 L80 68 L120 40 L160 48 L200 22 L240 30 L280 8 L320 14 L320 90 L0 90 Z"
              fill="url(#g)" opacity="0.5"/>
        <defs>
          <linearGradient id="g" x1="0" y1="0" x2="0" y2="90">
            <stop offset="0%" stop-color="#10B981" stop-opacity="0.25"/>
            <stop offset="100%" stop-color="#10B981" stop-opacity="0"/>
          </linearGradient>
        </defs>
      </svg>

      <div class="stats">
        <?php foreach ($authStats as [$statVal, $statLbl]): ?>
          <?php if ($statVal !== '' || $statLbl !== ''): ?>
          <div><div class="stat-num"><?= htmlspecialchars($statVal) ?></div><div class="stat-lbl"><?= htmlspecialchars($statLbl) ?></div></div>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>

    <p class="brand-foot">&copy; <?= date('Y') ?> <?= htmlspecialchars($pName.' '.$pTagline) ?>. All rights reserved.</p>
  </aside>

  <!-- ============ FORM PANEL ============ -->
  <main class="panel">

    <div class="mobile-bar">
      <div class="mobile-logo"><?= htmlspecialchars($pInit) ?></div>
      <span class="mobile-word"><?= htmlspecialchars($pName) ?></span>
    </div>

    <div class="utility-row">
      <div id="google_translate_element"></div>
      <a href="mailto:<?= htmlspecialchars(platform_setting('platform_support_email', platform_setting('platform_email','support@nexvest.com'))) ?>" class="help-link">Need help?</a>
    </div>

    <div class="form-wrap">
      <div class="form-card">
        <?= $content ?>
      </div>
    </div>
  </main>
</div>

<script src="/assets/js/app.js"></script>
<script type="text/javascript">
function googleTranslateElementInit(){
  new google.translate.TranslateElement({pageLanguage:'en',layout:google.translate.TranslateElement.InlineLayout.SIMPLE},'google_translate_element');
}
</script>
<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
<?php render_live_chat(); ?>
</body>
</html>
