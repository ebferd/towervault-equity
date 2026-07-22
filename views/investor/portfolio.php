<?php
/* Portfolio — $holdings, $total_invested, $total_earned */

$activeHoldings  = array_filter($holdings, fn($h) => $h['status'] === 'active');
$maturedHoldings = array_filter($holdings, fn($h) => $h['status'] === 'matured');

if (!function_exists('holdingDuration')):
function holdingDuration(array $h): string {
    $v = (int)($h['duration_value'] ?? 0);
    $u = $h['duration_unit'] ?? 'months';
    return $v . ' ' . ($v === 1 ? rtrim($u,'s') : $u);
}
function holdingExpectedReturn(array $h): float {
    $roi  = (float)($h['roi'] ?? $h['inv_roi'] ?? 0);
    $v    = (int)($h['duration_value'] ?? 0);
    $u    = $h['duration_unit'] ?? 'months';
    $yrs  = match($u) { 'years' => $v, 'days' => $v / 365, default => $v / 12 };
    return (float)$h['amount'] * $roi / 100 * $yrs;
}
function holdingProgress(array $h): int {
    if (empty($h['start_date']) || empty($h['end_date'])) return 0;
    $s = strtotime($h['start_date']);
    $e = strtotime($h['end_date']);
    $n = time();
    if ($e <= $s) return 100;
    return (int)min(100, max(0, ($n - $s) / ($e - $s) * 100));
}
endif;
?>

<style>
.port-summary{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.5rem}
@media(max-width:600px){.port-summary{grid-template-columns:1fr 1fr}}
.port-sum-card{background:#fff;border:1px solid var(--mist-100);border-radius:14px;padding:1.25rem 1.4rem}
.port-sum-eyebrow{font-size:10.5px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--mist-400);margin-bottom:.5rem}
.port-sum-val{font-size:1.5rem;font-weight:800;color:var(--mist-900);line-height:1}
.port-sum-sub{font-size:11.5px;color:var(--mist-400);margin-top:.35rem}

.hold-grid{display:grid;gap:1rem;margin-bottom:2rem}
.hold-card{background:#fff;border:1px solid var(--mist-100);border-radius:16px;overflow:visible;transition:box-shadow .18s,transform .18s}
.hold-card:hover{box-shadow:0 6px 24px rgba(11,17,32,.08);transform:translateY(-1px)}
.hold-card-top{display:grid;grid-template-columns:1fr auto;gap:1rem;padding:1.25rem 1.4rem 1rem;align-items:start;border-radius:16px 16px 0 0;overflow:hidden}
.hold-name{font-size:15px;font-weight:700;color:var(--mist-900);margin-bottom:.3rem}
.hold-meta{display:flex;gap:.5rem;flex-wrap:wrap;align-items:center}
.hold-type-pill{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;padding:.2rem .55rem;border-radius:20px;background:var(--mist-100);color:var(--mist-500)}
.hold-type-pill.real_estate{background:#ecfdf5;color:#065f46}
.hold-type-pill.index_fund{background:#eff6ff;color:#1e40af}
.hold-amount{text-align:right}
.hold-amount-val{font-size:1.25rem;font-weight:800;color:var(--mist-900);line-height:1}
.hold-amount-lbl{font-size:10.5px;color:var(--mist-400);margin-top:.2rem}
.hold-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:0;border-top:1px solid var(--mist-100)}
@media(max-width:680px){.hold-stats{grid-template-columns:repeat(2,1fr)}}
.hold-stat{padding:.9rem 1.1rem;border-right:1px solid var(--mist-100)}
.hold-stat:last-child{border-right:none}
.hold-stat-lbl{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--mist-400);margin-bottom:.3rem}
.hold-stat-val{font-size:13.5px;font-weight:700;color:var(--mist-800)}
.hold-stat-val.green{color:var(--em-600)}
.hold-prog-bar{margin:0 1.4rem;padding-bottom:.9rem}
.hold-prog-track{height:5px;background:var(--mist-100);border-radius:3px;overflow:hidden}
.hold-prog-fill{height:100%;background:linear-gradient(90deg,var(--em-500),var(--em-400));border-radius:3px;transition:width .4s}
.hold-prog-labels{display:flex;justify-content:space-between;margin-top:.35rem;font-size:10.5px;color:var(--mist-400)}
.hold-card-footer{display:flex;align-items:center;justify-content:space-between;padding:.75rem 1.4rem;background:var(--mist-50);border-top:1px solid var(--mist-100);gap:.5rem}
.hf-left{min-width:0}
.hf-badge-row{display:flex;align-items:center;gap:.4rem;flex-wrap:wrap}
.hf-reinvest-status{font-size:11px;color:var(--mist-400);display:flex;align-items:center;gap:4px;margin-top:3px}
.hf-reinvest-dot{width:5px;height:5px;border-radius:50%;background:var(--mist-300);flex-shrink:0;transition:background .2s}
.hf-reinvest-dot.on{background:var(--em-500)}
.hf-actions{display:flex;align-items:center;gap:5px;flex-shrink:0}
.hf-btn-primary{display:inline-flex;align-items:center;gap:5px;height:30px;padding:0 12px;border-radius:7px;border:none;background:var(--em-600);color:#fff;font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;text-decoration:none;white-space:nowrap;transition:background .15s}
.hf-btn-primary:hover{background:var(--em-700)}
.hf-btn-icon{width:30px;height:30px;border-radius:7px;border:1px solid var(--mist-200);background:#fff;color:var(--mist-500);display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:13px;transition:background .12s,color .12s;flex-shrink:0}
.hf-btn-icon:hover{background:var(--mist-100);color:var(--mist-800)}
.hf-btn-icon.reinvest-on{background:var(--em-50);color:var(--em-600);border-color:var(--em-400)}
.hf-more-wrap{position:relative}
.hf-overflow{position:absolute;bottom:calc(100% + 6px);right:0;background:#fff;border:1px solid var(--mist-200);border-radius:10px;box-shadow:0 4px 18px rgba(11,17,32,.12);min-width:175px;z-index:50;display:none;overflow:hidden}
.hf-overflow.open{display:block}
.hf-om-item{display:flex;align-items:center;gap:8px;padding:9px 13px;font-size:12.5px;color:var(--mist-800);cursor:pointer;transition:background .1s;text-decoration:none;white-space:nowrap}
.hf-om-item:hover{background:var(--mist-50)}
.hf-om-item svg{flex-shrink:0;color:var(--mist-400)}
.hf-om-item.danger{color:var(--red)}
.hf-om-item.danger svg{color:var(--red)}
.hf-om-divider{height:1px;background:var(--mist-100)}

.section-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:.85rem}
.section-head-title{font-size:13px;font-weight:700;color:var(--mist-700);text-transform:uppercase;letter-spacing:.07em}
.section-count{font-size:12px;color:var(--mist-400)}

.matured-card{background:#fff;border:1px solid var(--mist-100);border-radius:14px;padding:1.1rem 1.4rem;display:grid;grid-template-columns:1fr auto;gap:1rem;align-items:center;margin-bottom:.65rem}
.matured-card:last-child{margin-bottom:0}
.matured-name{font-size:14px;font-weight:600;color:var(--mist-900);margin-bottom:.25rem}
.matured-info{font-size:11.5px;color:var(--mist-400)}
.matured-right{text-align:right}
.matured-earned{font-size:1.1rem;font-weight:800;color:var(--em-600)}
.matured-earned-lbl{font-size:10.5px;color:var(--mist-400);margin-top:.15rem}
</style>

<div class="page-header">
  <div>
    <h1 class="greet">My Portfolio</h1>
    <p class="greet-sub">Track your active investments, earnings, and matured positions.</p>
  </div>
</div>

<!-- Summary Strip -->
<div class="port-summary">
  <div class="port-sum-card">
    <div class="port-sum-eyebrow">Total Invested</div>
    <div class="port-sum-val"><?= fmt_currency($total_invested) ?></div>
    <div class="port-sum-sub"><?= count($activeHoldings) ?> active position<?= count($activeHoldings) !== 1 ? 's' : '' ?></div>
  </div>
  <div class="port-sum-card">
    <div class="port-sum-eyebrow">Total Earned</div>
    <div class="port-sum-val" style="color:var(--em-600)"><?= fmt_currency($total_earned) ?></div>
    <div class="port-sum-sub">Cumulative returns paid</div>
  </div>
  <div class="port-sum-card">
    <div class="port-sum-eyebrow">Positions</div>
    <div class="port-sum-val"><?= count($holdings) ?></div>
    <div class="port-sum-sub"><?= count($maturedHoldings) ?> matured &middot; <?= count($activeHoldings) ?> active</div>
  </div>
</div>

<?php if (empty($holdings)): ?>
<!-- Empty state -->
<div style="text-align:center;padding:5rem 2rem">
  <div style="width:72px;height:72px;background:var(--mist-100);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem">
    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="var(--mist-400)" stroke-width="1.6"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
  </div>
  <h3 style="font-size:17px;font-weight:700;margin-bottom:.5rem">Your portfolio is empty</h3>
  <p style="font-size:13.5px;color:var(--mist-500);margin-bottom:1.75rem;max-width:380px;margin-left:auto;margin-right:auto">Start investing to build your portfolio and track your returns here.</p>
  <a href="/investor/investments" class="qbtn primary" style="height:42px;width:auto;padding:0 2rem;display:inline-flex">Browse investments</a>
</div>

<?php else: ?>

<!-- Active Holdings -->
<div class="section-head">
  <span class="section-head-title">Active investments</span>
  <span class="section-count"><?= count($activeHoldings) ?> position<?= count($activeHoldings) !== 1 ? 's' : '' ?></span>
</div>

<?php if (empty($activeHoldings)): ?>
<div class="card" style="margin-bottom:2rem">
  <div style="text-align:center;padding:2.5rem 2rem">
    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="var(--mist-300)" stroke-width="1.4" style="margin-bottom:.85rem"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
    <p style="font-size:13.5px;color:var(--mist-500);margin-bottom:1rem">No active investments right now.</p>
    <a href="/investor/investments" class="qbtn primary" style="height:38px;width:auto;padding:0 1.25rem;display:inline-flex">Browse opportunities</a>
  </div>
</div>
<?php else: ?>
<div class="hold-grid" style="margin-bottom:2rem">
  <?php foreach ($activeHoldings as $h):
    $prog   = holdingProgress($h);
    $expRet = holdingExpectedReturn($h);
    $roi    = (float)($h['roi'] ?? $h['inv_roi'] ?? 0);
  ?>
  <div class="hold-card">
    <div class="hold-card-top">
      <div>
        <div class="hold-name"><?= htmlspecialchars($h['name']) ?></div>
        <div class="hold-meta">
          <span class="hold-type-pill <?= htmlspecialchars($h['type']) ?>"><?= htmlspecialchars(str_replace('_',' ', ucfirst($h['type']))) ?></span>
          <?php if (!empty($h['certificate_ref'])): ?>
          <span style="font-size:10.5px;color:var(--mist-400);font-family:monospace"><?= htmlspecialchars($h['certificate_ref']) ?></span>
          <?php endif; ?>
        </div>
      </div>
      <div class="hold-amount">
        <div class="hold-amount-val"><?= fmt_currency((float)$h['amount']) ?></div>
        <div class="hold-amount-lbl">Invested</div>
      </div>
    </div>

    <div class="hold-stats">
      <div class="hold-stat">
        <div class="hold-stat-lbl">ROI</div>
        <div class="hold-stat-val green"><?= number_format($roi,1) ?>%</div>
      </div>
      <div class="hold-stat">
        <div class="hold-stat-lbl">Duration</div>
        <div class="hold-stat-val"><?= holdingDuration($h) ?></div>
      </div>
      <div class="hold-stat">
        <div class="hold-stat-lbl">Earned so far</div>
        <div class="hold-stat-val green"><?= fmt_currency((float)($h['total_earned'] ?? 0)) ?></div>
      </div>
      <div class="hold-stat">
        <div class="hold-stat-lbl">Expected return</div>
        <div class="hold-stat-val"><?= fmt_currency($expRet) ?></div>
      </div>
    </div>

    <!-- Progress bar -->
    <div class="hold-prog-bar">
      <div class="hold-prog-track"><div class="hold-prog-fill" style="width:<?= $prog ?>%"></div></div>
      <div class="hold-prog-labels">
        <span><?= !empty($h['start_date']) ? date('M j, Y', strtotime($h['start_date'])) : '—' ?></span>
        <span><?= $prog ?>% elapsed</span>
        <span><?= !empty($h['end_date']) ? date('M j, Y', strtotime($h['end_date'])) : '—' ?></span>
      </div>
    </div>

    <div class="hold-card-footer">
      <div class="hf-left">
        <div class="hf-badge-row">
          <span class="badge active">Active</span>
          <?php if (!empty($h['end_date'])): ?>
          <span style="font-size:11px;color:var(--mist-400)">Matures <?= date('M j, Y', strtotime($h['end_date'])) ?></span>
          <?php endif; ?>
        </div>
        <div class="hf-reinvest-status">
          <div class="hf-reinvest-dot <?= ($h['auto_reinvest']??0) ? 'on' : '' ?>" id="rdot-<?= (int)$h['id'] ?>"></div>
          <span id="rlbl-<?= (int)$h['id'] ?>"><?= ($h['auto_reinvest']??0) ? 'Auto-reinvest on' : 'Auto-reinvest off' ?></span>
        </div>
      </div>
      <div class="hf-actions">
        <?php if (!empty($h['certificate_ref'])): ?>
        <a href="/investor/certificate/<?= htmlspecialchars($h['certificate_ref']) ?>" class="hf-btn-primary">
          <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
          Certificate
        </a>
        <?php endif; ?>
        <button class="hf-btn-icon reinvest-toggle <?= ($h['auto_reinvest']??0) ? 'reinvest-on' : '' ?>"
          data-id="<?= (int)$h['id'] ?>"
          title="<?= ($h['auto_reinvest']??0) ? 'Auto-reinvest ON — click to turn off' : 'Auto-reinvest OFF — click to turn on' ?>"
          aria-label="Toggle auto-reinvest">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
        </button>
        <div class="hf-more-wrap">
          <button class="hf-btn-icon hf-more-btn" aria-label="More actions" aria-haspopup="true"
            data-id="<?= (int)$h['id'] ?>"
            data-deal="/investor/investments/<?= (int)$h['investment_id'] ?>"
            data-topup-name="<?= htmlspecialchars($h['name'], ENT_QUOTES) ?>"
            data-topup-min="<?= (float)($h['min_investment']??0) ?>"
            data-trm-name="<?= htmlspecialchars($h['name'] ?? 'this investment', ENT_QUOTES) ?>"
            data-trm-payout="<?= htmlspecialchars(fmt_currency((float)$h['amount']+(float)$h['total_earned']), ENT_QUOTES) ?>">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>
          </button>
          <div class="hf-overflow" id="hf-menu-<?= (int)$h['id'] ?>">
            <a class="hf-om-item" href="/investor/investments/<?= (int)$h['investment_id'] ?>">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
              View deal
            </a>
            <div class="hf-om-item btn-topup"
              data-id="<?= (int)$h['id'] ?>"
              data-name="<?= htmlspecialchars($h['name'], ENT_QUOTES) ?>"
              data-min="<?= (float)($h['min_investment']??0) ?>">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
              Add funds
            </div>
            <div class="hf-om-divider"></div>
            <div class="hf-om-item danger btn-terminate"
              data-id="<?= (int)$h['id'] ?>"
              data-name="<?= htmlspecialchars($h['name'] ?? 'this investment', ENT_QUOTES) ?>"
              data-payout="<?= htmlspecialchars(fmt_currency((float)$h['amount']+(float)$h['total_earned']), ENT_QUOTES) ?>">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              Terminate early
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Matured Holdings -->
<?php if (!empty($maturedHoldings)): ?>
<div class="section-head">
  <span class="section-head-title">Matured positions</span>
  <span class="section-count"><?= count($maturedHoldings) ?></span>
</div>
<div style="margin-bottom:2rem">
  <?php foreach ($maturedHoldings as $h):
    $roi = (float)($h['roi'] ?? $h['inv_roi'] ?? 0);
  ?>
  <div class="matured-card">
    <div>
      <div class="matured-name"><?= htmlspecialchars($h['name']) ?></div>
      <div class="matured-info">
        <?= fmt_currency((float)$h['amount']) ?> invested &middot;
        <?= number_format($roi,1) ?>% ROI &middot;
        <?= holdingDuration($h) ?>
        <?php if (!empty($h['end_date'])): ?>
         &middot; Matured <?= date('M j, Y', strtotime($h['end_date'])) ?>
        <?php endif; ?>
      </div>
    </div>
    <div class="matured-right">
      <div class="matured-earned">+<?= fmt_currency((float)($h['total_earned'] ?? 0)) ?></div>
      <div class="matured-earned-lbl">Total earned</div>
      <?php if (!empty($h['certificate_ref'])): ?>
      <a href="/investor/certificate/<?= htmlspecialchars($h['certificate_ref']) ?>" style="font-size:11.5px;color:var(--em-600);text-decoration:none;display:block;margin-top:.4rem;font-weight:600">↓ Certificate</a>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php endif; ?>

<!-- Top-up Modal -->
<div id="topup-modal" class="modal-overlay" style="display:none">
  <div class="modal" style="max-width:420px">
    <div class="modal-head">
      <h3 class="modal-title">Add Funds — <span id="topup-name"></span></h3>
      <button class="modal-close" onclick="document.getElementById('topup-modal').style.display='none'">&times;</button>
    </div>
    <div class="modal-body">
      <p style="font-size:13px;color:var(--mist-500);margin-bottom:1rem">Funds are deducted from your wallet immediately and added to your holding principal. Your maturity date stays the same.</p>
      <div id="topup-result" style="margin-bottom:.65rem"></div>
      <div class="fg">
        <label class="fl">Amount to add</label>
        <div class="fi-prefix">
          <span class="fi-sym"><?= platform_setting('platform_symbol','$') ?></span>
          <input class="fi" type="number" id="topup-amount" min="1" step="1" placeholder="0.00"/>
        </div>
        <div class="fhelp" id="topup-min-note"></div>
      </div>
      <input type="hidden" id="topup-holding-id"/>
      <div style="display:flex;gap:.65rem;margin-top:.25rem">
        <button class="qbtn primary" style="flex:1;height:44px" id="topup-btn" onclick="submitTopup()"><span>Confirm Top-up</span></button>
        <button class="qbtn outline" onclick="document.getElementById('topup-modal').style.display='none'" style="padding:0 18px">Cancel</button>
      </div>
    </div>
  </div>
</div>

<!-- Terminate Investment Modal -->
<div id="trm-modal" class="modal-overlay" style="display:none">
  <div class="modal" style="max-width:420px">
    <div class="modal-head">
      <h3 class="modal-title">Terminate Investment</h3>
      <button class="modal-close" onclick="document.getElementById('trm-modal').style.display='none'">&times;</button>
    </div>
    <div class="modal-body" style="padding-bottom:.5rem">
      <div class="alert-banner" style="background:var(--red-bg);border:1px solid var(--red-b);color:var(--red);border-radius:var(--r);padding:.7rem .9rem;margin-bottom:.85rem;font-size:12.5px">
        <strong>Warning:</strong> This closes your position immediately. You receive your capital plus interest earned to date.
      </div>
      <div id="trm-info" style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:.75rem .9rem;font-size:13.5px;margin-bottom:.85rem"></div>
      <div id="trm-alert"></div>
    </div>
    <div style="display:flex;gap:.75rem;padding:.75rem 1.6rem 1.25rem;position:sticky;bottom:0;background:var(--surface);border-top:1px solid var(--border)">
      <button class="qbtn outline" style="flex:1" onclick="document.getElementById('trm-modal').style.display='none'">Cancel</button>
      <button id="trm-btn" class="qbtn" style="flex:1;background:#C0392B;color:#fff" onclick="submitTerminate()">
        <span>Confirm Termination</span>
      </button>
    </div>
  </div>
</div>

<!-- ── Auto-reinvest confirmation ──────────────────────────────── -->
<div id="rein-modal" class="modal-overlay" style="display:none">
  <div class="modal" style="max-width:450px">
    <div class="modal-head">
      <h3 class="modal-title" id="rein-title">Turn on auto-reinvest?</h3>
      <button class="modal-close" onclick="closeReinModal()">&times;</button>
    </div>
    <div class="modal-body">
      <div id="rein-explain" style="font-size:13.5px;line-height:1.75;color:var(--mist-600)"></div>
      <div id="rein-alert" style="margin-top:.85rem"></div>
      <div style="display:flex;gap:.6rem;margin-top:1.35rem">
        <button class="qbtn outline" style="flex:1" onclick="closeReinModal()">Cancel</button>
        <button class="qbtn primary" style="flex:1" id="rein-confirm">Yes, turn it on</button>
      </div>
    </div>
  </div>
</div>

<script>
// ── Auto-reinvest toggle (asks for confirmation first) ─────────
var REIN_ON_TEXT =
  '<p style="margin:0 0 .7rem"><strong>Auto-reinvest</strong> puts each payout straight back into this investment instead of paying it into your wallet.</p>' +
  '<ul style="margin:0 0 .7rem;padding-left:1.1rem">' +
  '<li style="margin-bottom:.35rem">Your invested amount grows with every payout, so later returns are worked out on a larger balance.</li>' +
  '<li style="margin-bottom:.35rem">While it is on, these payouts will <strong>not</strong> arrive as cash in your wallet.</li>' +
  '<li>You can switch it off at any time — payouts then go to your wallet as normal.</li>' +
  '</ul>' +
  '<p style="margin:0;color:var(--mist-400);font-size:12.5px">Your original amount is still returned at maturity.</p>';

var REIN_OFF_TEXT =
  '<p style="margin:0 0 .7rem">Turning <strong>auto-reinvest</strong> off means future payouts for this investment will be paid into your <strong>wallet as cash</strong>, instead of being added back into the investment.</p>' +
  '<p style="margin:0;color:var(--mist-400);font-size:12.5px">Anything already reinvested stays invested. You can turn it back on at any time.</p>';

var reinPending = null;

document.addEventListener('click', function(e) {
  const btn = e.target.closest('.reinvest-toggle');
  if (!btn) return;
  const turningOn = !btn.classList.contains('reinvest-on');
  reinPending = btn;
  document.getElementById('rein-title').textContent   = turningOn ? 'Turn on auto-reinvest?' : 'Turn off auto-reinvest?';
  document.getElementById('rein-explain').innerHTML   = turningOn ? REIN_ON_TEXT : REIN_OFF_TEXT;
  document.getElementById('rein-alert').innerHTML     = '';
  const c = document.getElementById('rein-confirm');
  c.textContent = turningOn ? 'Yes, turn it on' : 'Yes, turn it off';
  c.disabled = false;
  document.getElementById('rein-modal').style.display = 'flex';
});

function closeReinModal() {
  document.getElementById('rein-modal').style.display = 'none';
  reinPending = null;
}

document.getElementById('rein-confirm').addEventListener('click', async function() {
  if (!reinPending) return;
  const btn = reinPending, c = this;
  const original = c.textContent;
  c.disabled = true; c.textContent = 'Saving…';

  const fd = new FormData();
  fd.append('holding_id', btn.dataset.id);
  const data = await post('/investor/reinvest', fd, true);

  c.disabled = false;
  if (data.success) {
    const on = data.auto_reinvest === 1;
    btn.classList.toggle('reinvest-on', on);
    btn.title = on ? 'Auto-reinvest ON — click to turn off' : 'Auto-reinvest OFF — click to turn on';
    const dot = document.getElementById('rdot-' + btn.dataset.id);
    const lbl = document.getElementById('rlbl-' + btn.dataset.id);
    if (dot) dot.classList.toggle('on', on);
    if (lbl) lbl.textContent = on ? 'Auto-reinvest on' : 'Auto-reinvest off';
    closeReinModal();
  } else {
    c.textContent = original;
    document.getElementById('rein-alert').innerHTML =
      '<div class="alert alert-err">' + (data.error || 'Could not update auto-reinvest.') + '</div>';
  }
});

// Close when clicking the dark backdrop
document.getElementById('rein-modal').addEventListener('click', function(e) {
  if (e.target === this) closeReinModal();
});

// ── Overflow menus ─────────────────────────────────────────────
document.addEventListener('click', function(e) {
  const moreBtn = e.target.closest('.hf-more-btn');
  if (moreBtn) {
    const id = moreBtn.dataset.id;
    const menu = document.getElementById('hf-menu-' + id);
    const isOpen = menu && menu.classList.contains('open');
    document.querySelectorAll('.hf-overflow.open').forEach(m => m.classList.remove('open'));
    if (menu && !isOpen) menu.classList.add('open');
    e.stopPropagation();
    return;
  }
  if (!e.target.closest('.hf-overflow')) {
    document.querySelectorAll('.hf-overflow.open').forEach(m => m.classList.remove('open'));
  }
});

// ── Top-up ─────────────────────────────────────────────────────
document.addEventListener('click', function(e) {
  const btn = e.target.closest('.btn-topup');
  if (!btn) return;
  document.querySelectorAll('.hf-overflow.open').forEach(m => m.classList.remove('open'));
  document.getElementById('topup-holding-id').value = btn.dataset.id;
  document.getElementById('topup-name').textContent  = btn.dataset.name;
  document.getElementById('topup-result').innerHTML  = '';
  document.getElementById('topup-amount').value      = '';
  const min = parseFloat(btn.dataset.min || '0');
  document.getElementById('topup-min-note').textContent = min > 0 ? 'Minimum: <?= platform_setting('platform_symbol','$') ?>' + min.toLocaleString() : '';
  document.getElementById('topup-modal').style.display = 'flex';
});

async function submitTopup() {
  const btn = document.getElementById('topup-btn');
  const amt = parseFloat(document.getElementById('topup-amount').value);
  if (!amt || amt <= 0) {
    document.getElementById('topup-result').innerHTML = '<div class="alert-banner err">Please enter a valid amount.</div>';
    return;
  }
  btn.disabled = true;
  btn.querySelector('span').innerHTML = '<span class="spinner" style="border-color:rgba(255,255,255,.4);border-top-color:#fff"></span> Processing…';
  const fd = new FormData();
  fd.append('_token', document.querySelector('meta[name="csrf"]')?.content || '');
  fd.append('holding_id', document.getElementById('topup-holding-id').value);
  fd.append('amount', amt);
  const data = await post('/investor/topup', fd, true);
  btn.disabled = false;
  btn.querySelector('span').textContent = 'Confirm Top-up';
  if (data.success) {
    document.getElementById('topup-result').innerHTML = '<div class="alert-banner ok"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg><span>' + data.message + '</span></div>';
    setTimeout(() => { document.getElementById('topup-modal').style.display='none'; location.reload(); }, 1800);
  } else {
    document.getElementById('topup-result').innerHTML = '<div class="alert-banner err">' + (data.error || 'Failed.') + '</div>';
  }
}

// ── Terminate ──────────────────────────────────────────────────
let _trmHoldingId = null;
document.addEventListener('click', function(e) {
  const btn = e.target.closest('.btn-terminate');
  if (!btn) return;
  document.querySelectorAll('.hf-overflow.open').forEach(m => m.classList.remove('open'));
  _trmHoldingId = btn.dataset.id;
  const invName = btn.dataset.name;
  const payout  = btn.dataset.payout;
  document.getElementById('trm-info').textContent = '';
  const nameEl = document.createElement('div');
  nameEl.style.marginBottom = '.4rem';
  nameEl.innerHTML = '<strong>' + invName + '</strong>';
  const payEl = document.createElement('div');
  payEl.style.cssText = 'color:var(--text3);font-size:12.5px';
  payEl.innerHTML = 'Payout to wallet: <strong style="color:var(--green)">' + payout + '<\/strong> (capital + interest earned)';
  const info = document.getElementById('trm-info');
  info.innerHTML = '';
  info.appendChild(nameEl);
  info.appendChild(payEl);
  document.getElementById('trm-alert').innerHTML = '';
  document.getElementById('trm-btn').querySelector('span').textContent = 'Confirm Termination';
  document.getElementById('trm-btn').disabled = false;
  document.getElementById('trm-modal').style.display = 'flex';
});
async function submitTerminate() {
  const btn = document.getElementById('trm-btn');
  btn.disabled = true;
  btn.querySelector('span').innerHTML = '<span class="spinner"></span> Processing…';
  const data = await post('/investor/terminate', { holding_id: _trmHoldingId });
  if (data.success) {
    document.getElementById('trm-alert').innerHTML = '<div class="alert-banner ok">' + (data.message || 'Investment terminated.') + ' Reloading…</div>';
    setTimeout(() => location.reload(), 2000);
  } else {
    btn.disabled = false;
    btn.querySelector('span').textContent = 'Confirm Termination';
    document.getElementById('trm-alert').innerHTML = '<div class="alert-banner err">' + (data.error || 'Failed.') + '</div>';
  }
}
</script>
