<?php
$firstName = explode(' ', trim($user['first_name'] ?? 'Investor'))[0];
$kycStatus = $user['kyc_status'] ?? 'not_submitted';

$txLabels = [
    'deposit'             => 'Deposit',
    'withdrawal'          => 'Withdrawal',
    'investment'          => 'Investment',
    'return'              => 'Return credited',
    'referral_commission' => 'Referral commission',
    'adjustment'          => 'Balance adjustment',
];
?>
<style>
/* ── Dashboard shell ─────────────────────────────────────── */
.db-wrap { display: flex; flex-direction: column; gap: 1.5rem; }

/* ── KYC notice ──────────────────────────────────────────── */
.db-notice { display: flex; align-items: center; gap: 10px; padding: .85rem 1.1rem; border-radius: 10px; font-size: 13px; }
.db-notice.warn { background: #fffbeb; border: 1px solid #fde68a; color: #78350f; }
.db-notice.info { background: #f0f9ff; border: 1px solid #bae6fd; color: #0c4a6e; }
.db-notice strong { font-weight: 700; }
.db-notice a { margin-left: auto; font-weight: 700; font-size: 12px; text-decoration: none; padding: .3rem .8rem; border-radius: 6px; white-space: nowrap; }
.db-notice.warn a { background: #fef3c7; color: #78350f; border: 1px solid #fde68a; }
.db-notice.info a { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }

/* ── Header row ──────────────────────────────────────────── */
.db-header { display: flex; align-items: flex-end; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
.db-greeting { font-size: 1.45rem; font-weight: 800; color: var(--mist-900); letter-spacing: -.4px; line-height: 1.2; }
.db-date { font-size: 12.5px; color: var(--mist-400); margin-top: .25rem; font-weight: 500; }
.db-hdr-actions { display: flex; gap: .6rem; }

/* ── Stat row ────────────────────────────────────────────── */
.db-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1px; background: var(--mist-100); border: 1px solid var(--mist-100); border-radius: 14px; overflow: hidden; }
@media(max-width:860px) { .db-stats { grid-template-columns: 1fr 1fr; } }
@media(max-width:400px) { .db-stats { grid-template-columns: 1fr 1fr; } }

.db-stat { background: #fff; padding: 1.25rem 1.4rem; }
.db-stat-lbl { font-size: 11px; font-weight: 600; color: var(--mist-400); letter-spacing: .03em; margin-bottom: .5rem; }
.db-stat-val { font-size: 1.5rem; font-weight: 900; color: var(--mist-900); letter-spacing: -.5px; line-height: 1; }
.db-stat-val.em { color: var(--em-600); }
.db-stat-sub { font-size: 11.5px; color: var(--mist-400); margin-top: .35rem; }
.db-stat-sub.up { color: var(--em-600); }

/* ── Two-column layout ───────────────────────────────────── */
.db-cols { display: grid; grid-template-columns: 1fr 340px; gap: 1.25rem; align-items: start; }
@media(max-width:1100px) { .db-cols { grid-template-columns: 1fr 300px; } }
@media(max-width:900px)  { .db-cols { grid-template-columns: 1fr; } }

/* ── White card base ─────────────────────────────────────── */
.db-card { background: #fff; border: 1px solid var(--mist-100); border-radius: 14px; overflow: hidden; }
.db-card + .db-card { margin-top: 1.25rem; }
.dbc-head { display: flex; align-items: center; justify-content: space-between; padding: 1.1rem 1.4rem .75rem; }
.dbc-title { font-size: 13.5px; font-weight: 700; color: var(--mist-900); }
.dbc-link { font-size: 12px; font-weight: 600; color: var(--em-600); text-decoration: none; }
.dbc-link:hover { color: var(--em-700); }

/* ── Chart ───────────────────────────────────────────────── */
.db-chart-meta { display: flex; align-items: center; justify-content: space-between; padding: 0 1.4rem .5rem; flex-wrap: wrap; gap: .5rem; }
.db-chart-total { font-size: 1.35rem; font-weight: 900; color: var(--mist-900); letter-spacing: -.4px; }
.db-chart-sub { font-size: 11.5px; color: var(--mist-400); margin-top: .15rem; }
.db-period-tabs { display: flex; gap: 2px; background: var(--mist-100); border-radius: 7px; padding: 3px; }
.db-ptab { height: 26px; padding: 0 11px; border-radius: 5px; font-size: 11.5px; font-weight: 600; color: var(--mist-500); background: none; border: none; cursor: pointer; font-family: inherit; transition: background .12s, color .12s; }
.db-ptab.active { background: #fff; color: var(--mist-900); box-shadow: 0 1px 3px rgba(11,17,32,.07); }
.db-chart-wrap { height: 188px; padding: .25rem 0 0; }

/* ── Holdings list ───────────────────────────────────────── */
.db-holding { display: grid; grid-template-columns: 1fr auto; gap: .75rem 1rem; align-items: center; padding: .9rem 1.4rem; border-top: 1px solid var(--mist-50); transition: background .12s; text-decoration: none; color: inherit; }
@media(max-width:480px) { .db-holding { grid-template-columns: 1fr; } .dbh-right { display: flex; gap: .75rem; align-items: center; } .dbh-right .dbh-roi { margin-top: 0; } }
.db-holding:hover { background: var(--mist-50); }
.db-holding:first-of-type { border-top: none; }
.dbh-name { font-size: 13.5px; font-weight: 600; color: var(--mist-900); margin-bottom: .25rem; }
.dbh-row { display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; }
.dbh-pill { font-size: 9.5px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; padding: .2rem .55rem; border-radius: 20px; }
.dbh-pill.real_estate { background: #ecfdf5; color: #065f46; }
.dbh-pill.index_fund  { background: #eff6ff; color: #1e40af; }
.dbh-meta { font-size: 11.5px; color: var(--mist-400); }
.dbh-prog { height: 3px; background: var(--mist-100); border-radius: 2px; overflow: hidden; margin-top: .6rem; }
.dbh-prog-fill { height: 100%; background: var(--em-500); border-radius: 2px; }
.dbh-right { text-align: right; }
.dbh-amount { font-size: 14px; font-weight: 800; color: var(--mist-900); }
.dbh-roi { font-size: 11.5px; font-weight: 700; color: var(--em-600); margin-top: .2rem; }
.db-empty { text-align: center; padding: 2.5rem 1.5rem; }
.db-empty-icon { width: 52px; height: 52px; background: var(--mist-100); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto .85rem; }
.db-empty-text { font-size: 13px; color: var(--mist-500); margin-bottom: 1.1rem; }

/* ── Activity feed ───────────────────────────────────────── */
.db-activity { padding: 0 .5rem .5rem; }
.dba-item { display: flex; align-items: center; gap: 10px; padding: .7rem .85rem; border-radius: 8px; transition: background .1s; }
.dba-item:hover { background: var(--mist-50); }
.dba-icon { width: 34px; height: 34px; border-radius: 9px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.dba-body { flex: 1; min-width: 0; }
.dba-title { font-size: 13px; font-weight: 600; color: var(--mist-800); }
.dba-time { font-size: 11px; color: var(--mist-400); margin-top: 1px; }
.dba-amt { font-size: 13px; font-weight: 700; white-space: nowrap; flex-shrink: 0; }
.dba-amt.pos { color: var(--em-600); }
.dba-amt.neg { color: var(--mist-600); }
.dba-empty { text-align: center; padding: 2rem 1rem; font-size: 13px; color: var(--mist-400); }
.db-see-all { display: block; text-align: center; padding: .75rem; font-size: 12px; font-weight: 600; color: var(--em-600); text-decoration: none; border-top: 1px solid var(--mist-100); transition: background .1s; }
.db-see-all:hover { background: var(--mist-50); }

/* ── Account status card ─────────────────────────────────── */
.db-status-list { padding: 0 1.4rem .85rem; display: flex; flex-direction: column; gap: .5rem; }
.db-status-row { display: flex; align-items: center; justify-content: space-between; padding: .7rem .9rem; background: var(--mist-50); border-radius: 9px; }
.dbs-left { display: flex; align-items: center; gap: .75rem; }
.dbs-icon { width: 30px; height: 30px; border-radius: 8px; display: flex; align-items: center; justify-content: center; }
.dbs-label { font-size: 12.5px; font-weight: 600; color: var(--mist-700); }
.dbs-sub { font-size: 11px; color: var(--mist-400); margin-top: 1px; }
.dbs-badge { font-size: 11px; font-weight: 700; padding: .25rem .65rem; border-radius: 20px; }
.dbs-badge.ok   { background: #ecfdf5; color: #059669; }
.dbs-badge.warn { background: #fffbeb; color: #92400e; }
.dbs-badge.off  { background: var(--mist-100); color: var(--mist-500); }

/* ── Responsive ──────────────────────────────────────────── */
@media(max-width:600px) {
  .db-header { flex-direction: column; align-items: flex-start; gap: .85rem; }
  .db-hdr-actions { width: 100%; }
  .db-hdr-actions .qbtn { flex: 1; justify-content: center; }
  .db-chart-wrap { height: 160px; }
  .dba-item { padding: .6rem .5rem; }
}
</style>

<div class="db-wrap">

<?php /* KYC banner — only show if KYC is enabled in admin */ ?>
<?php if (platform_setting('kyc_enabled','1') === '1'): ?>
  <?php if ($kycStatus === 'not_submitted' || $kycStatus === 'rejected'): ?>
  <div class="db-notice warn">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    <span><?= $kycStatus === 'rejected'
      ? '<strong>KYC rejected.</strong> Please resubmit your identity documents to restore full access.'
      : '<strong>Identity verification required.</strong> Verify your ID to unlock withdrawals and higher limits.'
    ?></span>
    <a href="/investor/kyc"><?= $kycStatus === 'rejected' ? 'Resubmit documents' : 'Verify now' ?></a>
  </div>
  <?php elseif ($kycStatus === 'pending'): ?>
  <div class="db-notice info">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <span>Your identity documents are <strong>under review</strong>. We'll notify you once verified — usually within 24 hours.</span>
    <a href="/investor/kyc">View status</a>
  </div>
  <?php endif; ?>
<?php endif; ?>

<?php /* Page header */ ?>
<div class="db-header">
  <div>
    <div class="db-greeting" id="db-greeting">Hello, <?= htmlspecialchars($firstName) ?>.</div>
    <div class="db-date" id="db-date">&nbsp;·&nbsp; Investor Portal</div>
  </div>
  <div class="db-hdr-actions">
    <a href="/investor/wallet" class="qbtn outline">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
      Deposit
    </a>
    <a href="/investor/investments" class="qbtn primary">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      New investment
    </a>
  </div>
</div>

<?php /* Pending invoice notice */ ?>
<?php if (!empty($pendingInvoices)): $pi = $pendingInvoices[0]; $piCount = count($pendingInvoices); ?>
<div style="display:flex;align-items:center;gap:.75rem;padding:.65rem .9rem;background:#FFFBEB;border:1px solid #FDE68A;border-radius:9px">
  <div style="width:6px;height:6px;border-radius:50%;background:#F59E0B;flex-shrink:0"></div>
  <div style="flex:1;font-size:12.5px;color:#78350F;line-height:1.4">
    <?php if ($piCount === 1): ?>
      <strong><?= htmlspecialchars($pi['title']) ?></strong> — payment invoice of <?= fmt_currency((float)$pi['amount']) ?> due <?= date('d M Y', strtotime($pi['due_date'])) ?>
    <?php else: ?>
      You have <strong><?= $piCount ?> pending payment invoices</strong> requiring attention
    <?php endif; ?>
  </div>
  <a href="/investor/invoices/<?= htmlspecialchars($pi['reference']) ?>" style="font-size:12px;font-weight:700;color:#1B6CA8;text-decoration:none;white-space:nowrap;flex-shrink:0">View &amp; pay →</a>
</div>
<?php endif; ?>

<?php /* Onboarding checklist — hidden once all steps complete */ ?>
<?php if (!$onboardingComplete):
  $obSteps = [
    'email_verified'   => ['Verify email',       '/investor/kyc',         'Confirm your email address'],
    'kyc_verified'     => ['Complete KYC',        '/investor/kyc',         'Verify your identity'],
    'funded_wallet'    => ['Fund wallet',          '/investor/wallet',      'Make your first deposit'],
    'first_investment' => ['Make an investment',   '/investor/investments', 'Start earning returns'],
  ];
  $obDone  = count(array_filter($onboarding));
  $obTotal = count($obSteps);
  $obPct   = $obTotal ? round($obDone / $obTotal * 100) : 0;
  $obCirc  = 2 * M_PI * 14;
  $obOffset = $obCirc - ($obCirc * $obPct / 100);
?>
<style>
.ob-banner{display:flex;align-items:center;gap:14px;padding:11px 14px;background:var(--surface);border:1px solid var(--border);border-radius:var(--r);cursor:pointer;transition:background .15s;user-select:none}
.ob-banner:hover{background:var(--mist-50)}
.ob-pips{display:flex;gap:5px;align-items:center;margin-top:5px}
.ob-pip{height:3px;flex:1;border-radius:99px;background:var(--mist-100);overflow:hidden}
.ob-pip-fill{height:100%;border-radius:99px;background:var(--em-500);width:0;transition:width .4s}
.ob-pip.done .ob-pip-fill{width:100%}
.ob-steps{display:none;border-top:1px solid var(--border)}
.ob-steps.open{display:block}
.ob-row{display:flex;align-items:center;gap:11px;padding:9px 14px;text-decoration:none;color:inherit;transition:background .12s}
.ob-row:hover{background:var(--mist-50)}
.ob-icon{width:27px;height:27px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:12px}
.ob-icon.done{background:var(--em-50);color:var(--em-600)}
.ob-icon.todo{background:var(--mist-50);color:var(--mist-400);border:1.5px dashed var(--mist-300)}
.ob-name{font-size:13px;font-weight:600;color:var(--mist-900)}
.ob-name.done{color:var(--mist-400);text-decoration:line-through;font-weight:500}
.ob-desc{font-size:11px;color:var(--mist-400);margin-top:1px}
.ob-cta{font-size:12px;font-weight:600;color:var(--em-600);white-space:nowrap;margin-left:auto}
@media(max-width:480px){.ob-cta{display:none}}
</style>
<div style="margin-bottom:.5rem;border:1px solid var(--border);border-radius:var(--r);overflow:hidden">
  <div class="ob-banner" id="ob-banner" onclick="obToggle()" role="button" aria-expanded="false" aria-controls="ob-steps">
    <svg width="34" height="34" viewBox="0 0 34 34" fill="none" aria-hidden="true">
      <circle cx="17" cy="17" r="14" stroke="var(--mist-200)" stroke-width="2.5"/>
      <circle cx="17" cy="17" r="14" stroke="var(--em-500)" stroke-width="2.5"
        stroke-dasharray="<?= round($obCirc,2) ?>" stroke-dashoffset="<?= round($obOffset,2) ?>"
        stroke-linecap="round" transform="rotate(-90 17 17)"/>
      <text x="17" y="21" text-anchor="middle" font-size="9.5" font-weight="600" fill="var(--mist-700)"><?= $obDone ?>/<?= $obTotal ?></text>
    </svg>
    <div style="flex:1;min-width:0">
      <div style="font-size:13px;font-weight:600;color:var(--mist-900)">Finish setting up your account</div>
      <div class="ob-pips">
        <?php foreach ($obSteps as $key => $_): ?>
        <div class="ob-pip <?= $onboarding[$key] ? 'done' : '' ?>"><div class="ob-pip-fill"></div></div>
        <?php endforeach; ?>
      </div>
    </div>
    <svg id="ob-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--mist-400)" stroke-width="2" style="flex-shrink:0;transition:transform .2s"><polyline points="6 9 12 15 18 9"/></svg>
  </div>
  <div class="ob-steps" id="ob-steps">
    <?php foreach ($obSteps as $key => [$label, $link, $sub]):
      $done = $onboarding[$key];
    ?>
    <a class="ob-row" href="<?= $done ? '#' : $link ?>">
      <div class="ob-icon <?= $done ? 'done' : 'todo' ?>">
        <?php if ($done): ?>
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
        <?php else: ?>
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="9"/></svg>
        <?php endif; ?>
      </div>
      <div style="flex:1;min-width:0">
        <div class="ob-name <?= $done ? 'done' : '' ?>"><?= $label ?></div>
        <div class="ob-desc"><?= $sub ?></div>
      </div>
      <?php if (!$done): ?><span class="ob-cta">Go &rarr;</span><?php endif; ?>
    </a>
    <?php endforeach; ?>
  </div>
</div>
<script>
function obToggle(){
  var s=document.getElementById('ob-steps'),c=document.getElementById('ob-chevron'),b=document.getElementById('ob-banner');
  var open=s.classList.toggle('open');
  c.style.transform=open?'rotate(180deg)':'';
  b.setAttribute('aria-expanded',open);
}
</script>
<?php endif; ?>

<?php /* Stat strip */ ?>
<div class="db-stats">
  <div class="db-stat">
    <div class="db-stat-lbl">Wallet balance</div>
    <div class="db-stat-val"><?= fmt_currency($stats['balance']) ?></div>
    <div class="db-stat-sub">Available to invest or withdraw</div>
  </div>
  <div class="db-stat">
    <div class="db-stat-lbl">Total invested</div>
    <div class="db-stat-val"><?= fmt_currency($stats['total_invested']) ?></div>
    <div class="db-stat-sub"><?= (int)$stats['active_count'] ?> active position<?= $stats['active_count'] !== 1 ? 's' : '' ?></div>
  </div>
  <div class="db-stat">
    <div class="db-stat-lbl">Returns earned</div>
    <div class="db-stat-val em"><?= fmt_currency($stats['total_earned']) ?></div>
    <div class="db-stat-sub up">
      <?php if ($stats['total_invested'] > 0 && $stats['total_earned'] > 0): ?>
        <?= number_format($stats['total_earned'] / $stats['total_invested'] * 100, 2) ?>% of invested amount
      <?php else: ?>
        Cumulative returns paid
      <?php endif; ?>
    </div>
  </div>
  <div class="db-stat">
    <div class="db-stat-lbl">Portfolio value</div>
    <div class="db-stat-val"><?= fmt_currency($stats['balance'] + $stats['total_invested']) ?></div>
    <div class="db-stat-sub">Wallet + active investments</div>
  </div>
</div>

<?php /* Main columns */ ?>
<div class="db-cols">

  <!-- Left column -->
  <div>

    <!-- Earnings chart -->
    <div class="db-card">
      <div class="dbc-head">
        <span class="dbc-title">Earnings over time</span>
        <div class="db-period-tabs">
          <button class="db-ptab active" data-range="7d">7D</button>
          <button class="db-ptab" data-range="30d">30D</button>
          <button class="db-ptab" data-range="1y">1Y</button>
        </div>
      </div>
      <div class="db-chart-meta">
        <div>
          <div class="db-chart-total" id="db-chart-total"><?= fmt_currency($stats['total_earned']) ?></div>
          <div class="db-chart-sub">Cumulative returns &amp; commissions</div>
        </div>
      </div>
      <div class="db-chart-wrap"><canvas id="dbChart"></canvas></div>
    </div>

    <!-- Active positions -->
    <div class="db-card">
      <div class="dbc-head">
        <span class="dbc-title">Active investments</span>
        <a href="/investor/portfolio" class="dbc-link">View portfolio →</a>
      </div>

      <?php if (empty($holdings)): ?>
      <div class="db-empty">
        <div class="db-empty-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--mist-400)" stroke-width="1.6"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
        </div>
        <div class="db-empty-text">You have no active investments yet.</div>
        <a href="/investor/investments" class="qbtn primary" style="height:38px;padding:0 1.5rem">Browse opportunities</a>
      </div>
      <?php else: ?>
      <?php foreach (array_slice($holdings, 0, 5) as $h):
        $s    = strtotime($h['start_date'] ?? 'now');
        $e    = strtotime($h['end_date']   ?? 'now');
        $prog = ($e > $s) ? (int) min(100, max(0, (time() - $s) / ($e - $s) * 100)) : 0;
        $roi  = (float)($h['roi'] ?? $h['inv_roi'] ?? 0);
      ?>
      <a class="db-holding" href="/investor/investments/<?= (int)$h['investment_id'] ?>">
        <div>
          <div class="dbh-name"><?= htmlspecialchars($h['name']) ?></div>
          <div class="dbh-row">
            <span class="dbh-pill <?= htmlspecialchars($h['type']) ?>"><?= $h['type'] === 'real_estate' ? 'Real Estate' : 'Index Fund' ?></span>
            <span class="dbh-meta">Matures <?= fmt_date($h['end_date']) ?></span>
          </div>
          <div class="dbh-prog"><div class="dbh-prog-fill" style="width:<?= $prog ?>%"></div></div>
        </div>
        <div class="dbh-right">
          <div class="dbh-amount"><?= fmt_currency((float)$h['amount']) ?></div>
          <div class="dbh-roi"><?= number_format($roi, 1) ?>% total</div>
        </div>
      </a>
      <?php endforeach; ?>
      <?php if (count($holdings) > 5): ?>
      <a href="/investor/portfolio" class="db-see-all">+<?= count($holdings) - 5 ?> more — view full portfolio</a>
      <?php endif; ?>
      <?php endif; ?>
    </div>

  </div>

  <!-- Right column -->
  <div>

    <!-- Recent activity -->
    <div class="db-card">
      <div class="dbc-head">
        <span class="dbc-title">Recent activity</span>
        <a href="/investor/transactions" class="dbc-link">View all</a>
      </div>
      <?php if (empty($recent_tx)): ?>
        <div class="dba-empty">No transactions yet.</div>
      <?php else: ?>
      <div class="db-activity">
        <?php foreach ($recent_tx as $tx):
          [$icoName, $icoColor, $icoBg] = nv_tx_icon($tx['type']);
          $isPos = in_array($tx['type'], ['deposit','return','referral_commission','adjustment'], true) && $tx['amount'] > 0;
          $isNeg = in_array($tx['type'], ['withdrawal','investment'], true);
        ?>
        <div class="dba-item">
          <div class="dba-icon" style="background:<?= $icoBg ?>">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="<?= $icoColor ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><?= nv_icon($icoName) ?></svg>
          </div>
          <div class="dba-body">
            <div class="dba-title"><?= htmlspecialchars($txLabels[$tx['type']] ?? ucfirst($tx['type'])) ?></div>
            <div class="dba-time"><?= time_ago($tx['created_at']) ?><?= $tx['method'] ? ' · ' . ucfirst($tx['method']) : '' ?></div>
          </div>
          <div class="dba-amt <?= $isNeg ? 'neg' : ($isPos ? 'pos' : '') ?>">
            <?= $isPos ? '+' : ($isNeg ? '−' : '') ?><?= fmt_currency((float)$tx['amount']) ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <a href="/investor/transactions" class="db-see-all">All transactions</a>
      <?php endif; ?>
    </div>

    <!-- Account status -->
    <div class="db-card">
      <div class="dbc-head">
        <span class="dbc-title">Account status</span>
        <a href="/investor/profile" class="dbc-link">Manage</a>
      </div>
      <div class="db-status-list">
        <?php
        $kyc2FA      = !empty($user['two_fa_enabled']);
        $kycOk       = $kycStatus === 'verified';
        $emailOk     = !empty($user['email_verified']);
        $kycEnabled  = platform_setting('kyc_enabled','1') === '1';
        $emailVerReq = platform_setting('email_verification_enabled','1') === '1';
        ?>
        <?php if ($kycEnabled): ?>
        <div class="db-status-row">
          <div class="dbs-left">
            <div class="dbs-icon" style="background:<?= $kycOk ? '#ecfdf5' : '#fffbeb' ?>">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="<?= $kycOk ? '#059669' : '#d97706' ?>" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <div>
              <div class="dbs-label">Identity (KYC)</div>
              <div class="dbs-sub"><?= $kycOk ? 'Verified — full access' : ucfirst(str_replace('_',' ',$kycStatus)) ?></div>
            </div>
          </div>
          <span class="dbs-badge <?= $kycOk ? 'ok' : 'warn' ?>"><?= $kycOk ? 'Verified' : 'Pending' ?></span>
        </div>
        <?php endif; ?>

        <div class="db-status-row">
          <div class="dbs-left">
            <div class="dbs-icon" style="background:<?= $kyc2FA ? '#ecfdf5' : '#f1f5f9' ?>">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="<?= $kyc2FA ? '#059669' : '#94a3b8' ?>" stroke-width="2"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
            </div>
            <div>
              <div class="dbs-label">Two-factor auth</div>
              <div class="dbs-sub"><?= $kyc2FA ? 'Authenticator app active' : 'Not configured' ?></div>
            </div>
          </div>
          <span class="dbs-badge <?= $kyc2FA ? 'ok' : 'off' ?>"><?= $kyc2FA ? 'Enabled' : 'Off' ?></span>
        </div>

        <?php if ($emailVerReq): ?>
        <div class="db-status-row">
          <div class="dbs-left">
            <div class="dbs-icon" style="background:<?= $emailOk ? '#ecfdf5' : '#fffbeb' ?>">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="<?= $emailOk ? '#059669' : '#d97706' ?>" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            </div>
            <div>
              <div class="dbs-label">Email address</div>
              <div class="dbs-sub"><?= htmlspecialchars($user['email'] ?? '') ?></div>
            </div>
          </div>
          <span class="dbs-badge <?= $emailOk ? 'ok' : 'warn' ?>"><?= $emailOk ? 'Verified' : 'Unverified' ?></span>
        </div>
        <?php else: ?>
        <div class="db-status-row">
          <div class="dbs-left">
            <div class="dbs-icon" style="background:#ecfdf5">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            </div>
            <div>
              <div class="dbs-label">Email address</div>
              <div class="dbs-sub"><?= htmlspecialchars($user['email'] ?? '') ?></div>
            </div>
          </div>
          <span class="dbs-badge ok">Active</span>
        </div>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>

</div><!-- .db-wrap -->

<script>
var ranges = <?= json_encode($chartData) ?>;
var sym = '<?= addslashes(platform_setting('platform_symbol','$')) ?>';

var ctx = document.getElementById('dbChart').getContext('2d');
var grad = ctx.createLinearGradient(0, 0, 0, 188);
grad.addColorStop(0, 'rgba(16,185,129,.12)');
grad.addColorStop(1, 'rgba(16,185,129,0)');

var chart = new Chart(ctx, {
  type: 'line',
  data: {
    labels: ranges['7d'].labels,
    datasets: [{
      data: ranges['7d'].data,
      borderColor: '#10B981',
      backgroundColor: grad,
      borderWidth: 1.8,
      fill: true,
      tension: 0.38,
      pointRadius: 0,
      pointHoverRadius: 4,
      pointHoverBackgroundColor: '#10B981',
      pointHoverBorderColor: '#fff',
      pointHoverBorderWidth: 2,
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        backgroundColor: '#0B1120',
        padding: 9,
        cornerRadius: 7,
        titleFont: { size: 11, family: 'Inter, sans-serif' },
        bodyFont:  { size: 12, weight: '700', family: 'Inter, sans-serif' },
        callbacks: {
          label: function(c) {
            return ' ' + sym + Number(c.raw).toLocaleString('en-US', { minimumFractionDigits: 2 });
          }
        }
      }
    },
    scales: {
      x: {
        grid: { display: false },
        border: { display: false },
        ticks: { color: '#94A3B8', font: { size: 11 }, maxRotation: 0 }
      },
      y: {
        position: 'right',
        grid: { color: '#F8FAFC' },
        border: { display: false },
        ticks: {
          color: '#94A3B8',
          font: { size: 11 },
          callback: function(v) {
            if (v >= 1000) return sym + (v / 1000).toFixed(1) + 'k';
            return sym + v;
          }
        }
      }
    }
  }
});

document.querySelectorAll('.db-ptab').forEach(function(tab) {
  tab.addEventListener('click', function() {
    document.querySelectorAll('.db-ptab').forEach(function(t) { t.classList.remove('active'); });
    tab.classList.add('active');
    var r = ranges[tab.dataset.range];
    chart.data.labels = r.labels;
    chart.data.datasets[0].data = r.data;
    chart.update();
  });
});
</script>
<script>
(function() {
  var now  = new Date();
  var hour = now.getHours();
  var greeting = hour < 12 ? 'Good morning' : (hour < 18 ? 'Good afternoon' : 'Good evening');
  var days  = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
  var months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
  var dateStr = days[now.getDay()] + ', ' + months[now.getMonth()] + ' ' + now.getDate() + ', ' + now.getFullYear();
  document.getElementById('db-greeting').textContent = greeting + ', <?= addslashes($firstName) ?>.';
  document.getElementById('db-date').innerHTML = dateStr + ' &nbsp;&middot;&nbsp; Investor Portal';
})();
</script>
