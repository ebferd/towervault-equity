<?php require_once ROOT . '/views/components/helpers.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<meta name="csrf-token" content="<?= csrf_token() ?>"/>
<title><?= htmlspecialchars($title ?? platform_setting('platform_name','NexVest')) ?></title>
<link rel="stylesheet" href="/assets/css/app.css"/>
</head>
<body style="background:var(--bg);display:flex;align-items:center;justify-content:center;min-height:100vh;padding:2rem">
<?php renderFlash(); ?>
<?= $content ?>
<script src="/assets/js/app.js"></script>
<?php render_live_chat(); ?>
</body>
</html>
