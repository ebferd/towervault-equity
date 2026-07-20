<?php
$pName    = platform_setting('platform_name', 'NexVest');
$company  = platform_setting('legal_company_name', $pName . ' Ltd.');
$regNum   = platform_setting('legal_registration_number', '');
$regulator= platform_setting('legal_regulator', '');
$juris    = platform_setting('legal_jurisdiction', '');
$terms    = platform_setting('legal_terms', '');
$privacy  = platform_setting('legal_privacy', '');
?>
<style>
.legal-hero{background:linear-gradient(135deg,#0f766e,#059669);border-radius:18px;padding:2.5rem;margin-bottom:2rem;color:#fff}
.legal-hero h1{font-size:1.6rem;font-weight:800;letter-spacing:-.4px;margin-bottom:.5rem}
.legal-hero p{font-size:14px;opacity:.85;max-width:520px;line-height:1.6}
.legal-card{background:#fff;border:1px solid var(--mist-100);border-radius:16px;overflow:hidden;margin-bottom:1.5rem}
.legal-card-head{padding:1.1rem 1.5rem;border-bottom:1px solid var(--mist-100);display:flex;align-items:center;gap:.75rem}
.legal-card-icon{width:32px;height:32px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.legal-card-title{font-size:14px;font-weight:700;color:var(--mist-900)}
.legal-card-body{padding:1.25rem 1.5rem}
.legal-row{display:flex;justify-content:space-between;align-items:flex-start;padding:.6rem 0;border-bottom:1px solid var(--mist-50);gap:1rem;font-size:13.5px}
.legal-row:last-child{border-bottom:none}
.legal-row-lbl{color:var(--mist-500);font-weight:500;flex-shrink:0;min-width:160px}
.legal-row-val{font-weight:600;color:var(--mist-900);text-align:right}
.legal-prose{font-size:13.5px;line-height:1.75;color:var(--mist-700);white-space:pre-wrap}
.legal-empty{font-size:13px;color:var(--mist-400);font-style:italic;text-align:center;padding:1.5rem 0}
.legal-tabs{display:flex;gap:0;border-bottom:1px solid var(--mist-100);padding:0 1.5rem}
.legal-tab{font-size:13px;font-weight:600;color:var(--mist-400);padding:.85rem .25rem;margin-right:1.5rem;border-bottom:2px solid transparent;cursor:pointer;transition:color .15s,border-color .15s}
.legal-tab.active{color:var(--em-600);border-bottom-color:var(--em-600)}
.legal-tab-panel{display:none;padding:1.25rem 1.5rem}
.legal-tab-panel.active{display:block}
</style>

<div class="page-header">
  <div>
    <h1 class="greet">Legal &amp; Compliance</h1>
    <p class="greet-sub">Company information, regulatory status, terms of service, and privacy policy.</p>
  </div>
</div>

<div class="legal-hero">
  <h1><?= htmlspecialchars($company) ?></h1>
  <p>Operating as <strong><?= htmlspecialchars($pName) ?></strong> — a regulated investment platform committed to transparency, security, and compliance.</p>
</div>

<!-- Company Info -->
<div class="legal-card">
  <div class="legal-card-head">
    <div class="legal-card-icon" style="background:#eff6ff">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1e40af" stroke-width="1.8"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
    </div>
    <div class="legal-card-title">Company Information</div>
  </div>
  <div class="legal-card-body">
    <?php $rows = array_filter([
      'Legal Company Name'      => $company,
      'Registration Number'     => $regNum,
      'Regulatory Body'         => $regulator,
      'Jurisdiction'            => $juris,
      'Platform Name'           => $pName,
      'Contact Email'           => platform_setting('platform_support_email', platform_setting('platform_email', '')),
    ]); ?>
    <?php foreach ($rows as $l => $v): ?>
      <div class="legal-row">
        <span class="legal-row-lbl"><?= htmlspecialchars($l) ?></span>
        <span class="legal-row-val"><?= htmlspecialchars($v) ?></span>
      </div>
    <?php endforeach; ?>
    <?php if (empty($rows)): ?>
      <div class="legal-empty">Company details have not been configured yet. Please contact support.</div>
    <?php endif; ?>
  </div>
</div>

<!-- Terms / Privacy tabs -->
<div class="legal-card">
  <div class="legal-tabs">
    <div class="legal-tab active" onclick="switchTab(this,'tab-terms')">Terms of Service</div>
    <div class="legal-tab" onclick="switchTab(this,'tab-privacy')">Privacy Policy</div>
  </div>
  <div id="tab-terms" class="legal-tab-panel active">
    <?php if ($terms): ?>
      <div class="legal-prose"><?= htmlspecialchars($terms) ?></div>
    <?php else: ?>
      <div class="legal-empty">Terms of service have not been published yet.</div>
    <?php endif; ?>
  </div>
  <div id="tab-privacy" class="legal-tab-panel">
    <?php if ($privacy): ?>
      <div class="legal-prose"><?= htmlspecialchars($privacy) ?></div>
    <?php else: ?>
      <div class="legal-empty">Privacy policy has not been published yet.</div>
    <?php endif; ?>
  </div>
</div>

<!-- GDPR Rights Notice -->
<div class="legal-card">
  <div class="legal-card-head">
    <div class="legal-card-icon" style="background:#f0fdf4">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="1.8"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
    </div>
    <div class="legal-card-title">Your Data Rights (GDPR)</div>
  </div>
  <div class="legal-card-body">
    <p style="font-size:13.5px;color:var(--mist-600);margin-bottom:1.25rem;line-height:1.7">
      Under the General Data Protection Regulation (GDPR), you have the right to access, correct, and delete your personal data.
      You may download a complete copy of your data or request account deletion at any time from your <a href="/investor/profile" style="color:var(--em-600);font-weight:600">Profile page</a>.
    </p>
    <div style="display:flex;gap:.75rem;flex-wrap:wrap">
      <a href="/investor/data-export" class="qbtn outline" style="height:38px;font-size:13px">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:5px"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        Download My Data
      </a>
    </div>
  </div>
</div>

<script>
function switchTab(el, panelId) {
  document.querySelectorAll('.legal-tab').forEach(t => t.classList.remove('active'));
  document.querySelectorAll('.legal-tab-panel').forEach(p => p.classList.remove('active'));
  el.classList.add('active');
  document.getElementById(panelId).classList.add('active');
}
</script>
