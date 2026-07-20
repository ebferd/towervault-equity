<?php /* Public legal page — Terms / Privacy. No auth required. */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title><?= htmlspecialchars($title ?? 'Legal') ?> — <?= htmlspecialchars($platform_name ?? 'NexVest') ?></title>
<link rel="stylesheet" href="/assets/css/app.css"/>
</head>
<body>
<?php
$isTerms    = ($heading === 'Terms of Service');
$iconPath   = $isTerms
    ? 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'
    : 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z';
$cleanContent = trim($content ?? '');
$hasContent   = $cleanContent !== '';
?>
<style>
:root{--acc:#4F8EF7}
*,*::before,*::after{box-sizing:border-box}
.lg-page{min-height:100vh;background:linear-gradient(135deg,#F0F4FF 0%,#F8FAFF 60%,#EEF2FF 100%);display:flex;flex-direction:column}
.lg-nav{display:flex;align-items:center;justify-content:space-between;padding:1rem 2rem;border-bottom:1px solid rgba(255,255,255,.7);background:rgba(255,255,255,.85);backdrop-filter:blur(12px);position:sticky;top:0;z-index:50}
.lg-brand{display:flex;align-items:center;gap:.6rem;text-decoration:none}
.lg-mark{width:32px;height:32px;border-radius:9px;background:var(--acc);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:13px;color:#fff;flex-shrink:0;box-shadow:0 4px 12px rgba(79,142,247,.35)}
.lg-brand-name{font-size:14.5px;font-weight:800;color:#111827;letter-spacing:-.2px}
.lg-back-link{display:inline-flex;align-items:center;gap:5px;font-size:12.5px;font-weight:600;color:#6B7280;text-decoration:none;border:1px solid #E5E7EB;border-radius:7px;padding:5px 11px;background:#fff;transition:all .15s}
.lg-back-link:hover{border-color:#D1D5DB;color:#374151}

.lg-body{flex:1;padding:3rem 1.5rem 5rem;display:flex;flex-direction:column;align-items:center}
.lg-hero{text-align:center;margin-bottom:2rem;max-width:520px}
.lg-hero-badge{display:inline-flex;align-items:center;gap:6px;background:rgba(79,142,247,.1);border:1px solid rgba(79,142,247,.2);color:var(--acc);border-radius:20px;padding:5px 14px;font-size:11.5px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;margin-bottom:1.1rem}
.lg-hero-title{font-size:2rem;font-weight:900;color:#111827;letter-spacing:-.5px;margin-bottom:.5rem;line-height:1.15}
.lg-hero-sub{font-size:13.5px;color:#6B7280;line-height:1.65}

.lg-card{background:#fff;border:1px solid #E5E7EB;border-radius:18px;width:100%;max-width:780px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.06),0 1px 4px rgba(0,0,0,.04)}
.lg-card-head{padding:1.4rem 2rem 1.25rem;border-bottom:1px solid #F3F4F6;display:flex;align-items:center;gap:.75rem}
.lg-head-icon{width:36px;height:36px;border-radius:10px;background:rgba(79,142,247,.1);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.lg-head-title{font-size:16px;font-weight:800;color:#111827}
.lg-head-updated{font-size:11.5px;color:#9CA3AF;margin-top:2px}

.lg-content-wrap{padding:1.75rem 2rem 2.5rem}
.lg-content{font-size:14.5px;color:#374151;line-height:1.85}
.lg-content h1,.lg-content h2,.lg-content h3{color:#111827;font-weight:700;margin:1.65em 0 .6em;line-height:1.3}
.lg-content h1{font-size:1.2rem}
.lg-content h2{font-size:1.05rem;padding-bottom:.4rem;border-bottom:1px solid #F3F4F6}
.lg-content h3{font-size:.95rem}
.lg-content p{margin:0 0 1em}
.lg-content ul,.lg-content ol{margin:0 0 1em 1.5em}
.lg-content li{margin-bottom:.4em}
.lg-content a{color:var(--acc);text-decoration:none}
.lg-content a:hover{text-decoration:underline}
.lg-content strong{color:#1F2937;font-weight:600}
.lg-content blockquote{border-left:3px solid #E5E7EB;padding:.5rem 1rem;margin:1em 0;color:#6B7280;font-style:italic}
.lg-content hr{border:none;border-top:1px solid #F3F4F6;margin:1.5em 0}

.lg-empty{padding:3.5rem 2rem;text-align:center}
.lg-empty-ic{width:60px;height:60px;border-radius:16px;background:#F9FAFB;border:1px solid #F3F4F6;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;color:#D1D5DB}
.lg-empty h3{font-size:15px;font-weight:700;color:#111827;margin-bottom:.35rem}
.lg-empty p{font-size:13px;color:#9CA3AF;margin:0;line-height:1.65}

.lg-footer{text-align:center;font-size:11.5px;color:#9CA3AF;padding-bottom:2rem;margin-top:1.5rem}
.lg-footer a{color:#6B7280;text-decoration:none}

@media(max-width:640px){
  .lg-body{padding:2rem 1rem 4rem}
  .lg-hero-title{font-size:1.5rem}
  .lg-card-head{padding:1rem 1.25rem}
  .lg-content-wrap{padding:1.25rem 1.25rem 2rem}
  .lg-nav{padding:.75rem 1rem}
}
</style>

<div class="lg-page">
  <!-- Nav -->
  <nav class="lg-nav">
    <a href="/" class="lg-brand">
      <div class="lg-mark"><?= htmlspecialchars(platform_setting('platform_initials','N')) ?></div>
      <span class="lg-brand-name"><?= htmlspecialchars($platform_name) ?></span>
    </a>
    <a href="/register" class="lg-back-link">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
      Back to registration
    </a>
  </nav>

  <div class="lg-body">
    <!-- Hero -->
    <div class="lg-hero">
      <div class="lg-hero-badge">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="<?= $iconPath ?>"/></svg>
        <?= $isTerms ? 'Legal document' : 'Privacy document' ?>
      </div>
      <h1 class="lg-hero-title"><?= htmlspecialchars($heading) ?></h1>
      <p class="lg-hero-sub">
        <?php if ($isTerms): ?>
          Please read these terms carefully before creating an account. By registering, you agree to be bound by these terms.
        <?php else: ?>
          This document explains how <?= htmlspecialchars($platform_name) ?> collects, uses, and protects your personal information.
        <?php endif; ?>
      </p>
    </div>

    <!-- Card -->
    <div class="lg-card">
      <div class="lg-card-head">
        <div class="lg-head-icon">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--acc)" stroke-width="1.75"><path d="<?= $iconPath ?>"/></svg>
        </div>
        <div>
          <div class="lg-head-title"><?= htmlspecialchars($heading) ?></div>
          <div class="lg-head-updated"><?= htmlspecialchars($platform_name) ?> · Effective upon registration</div>
        </div>
      </div>

      <?php if ($hasContent): ?>
        <div class="lg-content-wrap">
          <div class="lg-content">
            <?php
            // Render HTML content if it contains tags, otherwise treat as plain text
            if (preg_match('/<[a-zA-Z][\s\S]*?>/', $cleanContent)) {
                echo $cleanContent;
            } else {
                echo nl2br(htmlspecialchars($cleanContent));
            }
            ?>
          </div>
        </div>
      <?php else: ?>
        <div class="lg-empty">
          <div class="lg-empty-ic">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
          </div>
          <h3>Document not yet available</h3>
          <p>This document hasn't been published yet.<br>Please contact <a href="mailto:<?= htmlspecialchars(platform_setting('platform_support_email', platform_setting('platform_email',''))) ?>"><?= htmlspecialchars(platform_setting('platform_name','our support team')) ?></a> for more information.</p>
        </div>
      <?php endif; ?>
    </div>

    <div class="lg-footer">
      &copy; <?= date('Y') ?> <?= htmlspecialchars($platform_name) ?> · All rights reserved ·
      <a href="/terms">Terms</a> · <a href="/privacy">Privacy</a>
    </div>
  </div>
</div>
<script src="/assets/js/app.js"></script>
</body>
</html>
