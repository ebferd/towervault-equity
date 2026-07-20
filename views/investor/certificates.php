<?php
/* Certificates — $holdings (active + matured, with inv_roi, certificate_ref) */

if (!function_exists('certDuration')):
function certDuration(array $h): string {
    $v = (int)($h['duration_value'] ?? 0);
    $u = $h['duration_unit'] ?? 'months';
    return $v . ' ' . ($v === 1 ? rtrim($u,'s') : $u);
}
function certExpectedReturn(array $h): float {
    // ROI is the total return over the full duration
    $roi = (float)($h['roi'] ?? $h['inv_roi'] ?? 0);
    return (float)$h['amount'] * $roi / 100;
}
endif;

$pName = platform_setting('platform_name', 'NexVest');
?>

<style>
.cert-summary{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.75rem}
@media(max-width:560px){.cert-summary{grid-template-columns:1fr 1fr}
  .cert-summary > :last-child{grid-column:1/-1}}
.cert-sum-card{background:#fff;border:1px solid var(--mist-100);border-radius:14px;padding:1.25rem 1.4rem}
.cert-sum-ey{font-size:10px;font-weight:700;letter-spacing:.09em;text-transform:uppercase;color:var(--mist-400);margin-bottom:.5rem}
.cert-sum-val{font-size:1.6rem;font-weight:900;color:var(--mist-900);line-height:1;letter-spacing:-.5px}
.cert-sum-sub{font-size:11.5px;color:var(--mist-400);margin-top:.4rem}

/* Certificate card */
.cert-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:1rem;margin-bottom:2rem}
@media(max-width:480px){.cert-grid{grid-template-columns:1fr}}

.cert-card{background:#fff;border:1px solid var(--mist-100);border-radius:18px;overflow:hidden;transition:box-shadow .18s,transform .18s}
.cert-card:hover{box-shadow:0 8px 28px rgba(11,17,32,.09);transform:translateY(-2px)}

/* Certificate document preview area */
.cert-doc{background:var(--navy);padding:1.5rem 1.5rem 1.25rem;position:relative;overflow:hidden}
.cert-doc-glow{position:absolute;bottom:-40px;right:-40px;width:180px;height:180px;background:radial-gradient(circle,rgba(16,185,129,.2) 0%,transparent 65%);pointer-events:none}
.cert-doc-header{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1rem}
.cert-doc-brand{font-size:11px;font-weight:700;color:rgba(255,255,255,.5);letter-spacing:.05em}
.cert-doc-badge{background:rgba(16,185,129,.15);border:1px solid rgba(16,185,129,.3);border-radius:20px;padding:.2rem .7rem;font-size:10px;font-weight:700;color:var(--em-400)}
.cert-doc-title{font-family:'Fraunces',serif;font-size:1.1rem;font-weight:900;color:#fff;line-height:1.25;margin-bottom:.3rem;max-width:85%}
.cert-doc-type{font-size:10.5px;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.08em;font-weight:600}
.cert-doc-ref{font-size:11px;font-family:monospace;color:rgba(255,255,255,.35);margin-top:.75rem;letter-spacing:.05em}

/* Certificate body */
.cert-body{padding:1.1rem 1.4rem}
.cert-kv-row{display:grid;grid-template-columns:1fr 1fr;gap:.6rem .75rem;margin-bottom:1rem}
.cert-kv{background:var(--mist-50);border-radius:9px;padding:.6rem .8rem}
.cert-kv-lbl{font-size:9.5px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--mist-400);margin-bottom:.25rem}
.cert-kv-val{font-size:13px;font-weight:700;color:var(--mist-900)}
.cert-kv-val.em{color:var(--em-600)}

.cert-footer{display:flex;align-items:center;justify-content:space-between;padding:.85rem 1.4rem;border-top:1px solid var(--mist-100);background:var(--mist-50)}
.cert-status{display:flex;align-items:center;gap:.4rem}

.cert-empty{text-align:center;padding:5rem 2rem}
.cert-empty-icon{width:72px;height:72px;background:var(--mist-100);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem}
</style>

<div class="page-header">
  <div>
    <h1 class="greet">Certificates</h1>
    <p class="greet-sub">Download and manage your official investment certificates.</p>
  </div>
</div>

<!-- Summary -->
<?php
$total    = count($holdings);
$active   = count(array_filter($holdings, fn($h) => $h['status']==='active'));
$matured  = count(array_filter($holdings, fn($h) => $h['status']==='matured'));
$withCert = count(array_filter($holdings, fn($h) => !empty($h['certificate_ref'])));
?>
<div class="cert-summary">
  <div class="cert-sum-card">
    <div class="cert-sum-ey">Total certificates</div>
    <div class="cert-sum-val"><?= $withCert ?></div>
    <div class="cert-sum-sub"><?= $total ?> total positions</div>
  </div>
  <div class="cert-sum-card">
    <div class="cert-sum-ey">Active</div>
    <div class="cert-sum-val" style="color:var(--em-600)"><?= $active ?></div>
    <div class="cert-sum-sub">Ongoing investments</div>
  </div>
  <div class="cert-sum-card">
    <div class="cert-sum-ey">Matured</div>
    <div class="cert-sum-val"><?= $matured ?></div>
    <div class="cert-sum-sub">Completed positions</div>
  </div>
</div>

<?php if (empty($holdings)): ?>
<!-- Empty state -->
<div class="cert-empty">
  <div class="cert-empty-icon">
    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="var(--mist-400)" stroke-width="1.6"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="15" x2="15" y2="15"/><line x1="9" y1="11" x2="15" y2="11"/></svg>
  </div>
  <h3 style="font-size:16px;font-weight:700;margin-bottom:.5rem">No certificates yet</h3>
  <p style="font-size:13.5px;color:var(--mist-500);margin-bottom:1.75rem;max-width:360px;margin-left:auto;margin-right:auto">
    Certificates are issued once your investment is activated. Start investing to earn your first certificate.
  </p>
  <a href="/investor/investments" class="qbtn primary" style="height:42px;width:auto;padding:0 2rem;display:inline-flex">Browse investments</a>
</div>

<?php else: ?>
<div class="cert-grid">
  <?php foreach ($holdings as $h):
    $roi     = (float)($h['roi'] ?? $h['inv_roi'] ?? 0);
    $expRet  = certExpectedReturn($h);
    $hasCert = !empty($h['certificate_ref']);
    $isActive = $h['status'] === 'active';
  ?>
  <div class="cert-card">
    <!-- Document preview -->
    <div class="cert-doc">
      <div class="cert-doc-glow"></div>
      <div class="cert-doc-header">
        <div class="cert-doc-brand"><?= htmlspecialchars($pName) ?> &nbsp;·&nbsp; Investment Certificate</div>
        <span class="cert-doc-badge"><?= $isActive ? 'Active' : 'Matured' ?></span>
      </div>
      <div class="cert-doc-title"><?= htmlspecialchars($h['name']) ?></div>
      <div class="cert-doc-type"><?= htmlspecialchars(str_replace('_', ' ', ucfirst($h['type']))) ?></div>
      <div class="cert-doc-ref"><?= $hasCert ? htmlspecialchars($h['certificate_ref']) : 'Certificate pending' ?></div>
    </div>

    <!-- Key values -->
    <div class="cert-body">
      <div class="cert-kv-row">
        <div class="cert-kv">
          <div class="cert-kv-lbl">Amount invested</div>
          <div class="cert-kv-val"><?= fmt_currency((float)$h['amount']) ?></div>
        </div>
        <div class="cert-kv">
          <div class="cert-kv-lbl">ROI</div>
          <div class="cert-kv-val em"><?= number_format($roi,1) ?>% total</div>
        </div>
        <div class="cert-kv">
          <div class="cert-kv-lbl">Duration</div>
          <div class="cert-kv-val"><?= certDuration($h) ?></div>
        </div>
        <div class="cert-kv">
          <div class="cert-kv-lbl">Expected return</div>
          <div class="cert-kv-val em"><?= fmt_currency($expRet) ?></div>
        </div>
      </div>

      <?php if (!empty($h['start_date']) && !empty($h['end_date'])): ?>
      <div style="display:flex;justify-content:space-between;font-size:11.5px;color:var(--mist-400);margin-bottom:.85rem">
        <span>Started <?= date('M j, Y', strtotime($h['start_date'])) ?></span>
        <span><?= $isActive ? 'Matures' : 'Matured' ?> <?= date('M j, Y', strtotime($h['end_date'])) ?></span>
      </div>
      <?php endif; ?>
    </div>

    <!-- Footer actions -->
    <div class="cert-footer">
      <div class="cert-status">
        <span class="badge <?= $isActive ? 'active' : 'matured' ?>"><?= $isActive ? 'Active' : 'Matured' ?></span>
        <?php if (!$hasCert): ?>
          <span style="font-size:11px;color:var(--mist-400)">Cert. pending</span>
        <?php endif; ?>
      </div>
      <?php if ($hasCert): ?>
      <a href="/investor/certificate/<?= htmlspecialchars($h['certificate_ref'] ?? '') ?>" class="qbtn primary" style="height:34px;font-size:12.5px;padding:0 1rem">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        Download PDF
      </a>
      <?php else: ?>
      <span style="font-size:12px;color:var(--mist-400)">Awaiting activation</span>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
