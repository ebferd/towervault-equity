<?php /* views/admin/dashboard.php */ ?>
<div class="page-header"><h1 class="page-title">Admin Overview</h1><p class="page-sub">Platform snapshot as of today.</p></div>

<div class="stats-grid">
  <?php foreach ([
    ['Total Investors', $stats['total_users']??0, ($stats['active_users']??0).' active · '.($stats['kyc_pending']??0).' pending KYC', ''],
    ['Total Funds Held', fmt_currency((float)($stats['total_wallet_balance']??0)), 'Across all investor wallets', 'up'],
    ['Total Invested', fmt_currency((float)($stats['total_invested']??0)), 'Across all positions', 'up'],
    ['Open Tickets', ($stats['open_tickets']??0), ($stats['pending_withdrawals']??0).' withdrawals pending', 'down'],
  ] as [$l,$v,$sub,$cls]): ?>
    <div class="stat-card"><div class="sc-label"><?= $l ?></div><div class="sc-value"><?= $v ?></div><div class="sc-sub <?= $cls ?>"><?= htmlspecialchars($sub) ?></div></div>
  <?php endforeach; ?>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem">
  <div class="section">
    <div class="section-head"><span class="section-title">Recent Investors</span><a href="/admin/users" class="section-link">View All</a></div>
    <div class="tbl-overflow">
      <table class="data-table">
        <thead><tr><th>Investor</th><th>KYC</th><th>Balance</th></tr></thead>
        <tbody>
        <?php foreach ($recent_users as $u): ?>
          <tr>
            <td><div style="display:flex;align-items:center;gap:9px"><div style="width:28px;height:28px;border-radius:50%;background:var(--accent);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:#fff;flex-shrink:0"><?= strtoupper(substr($u['first_name'],0,1).substr($u['last_name'],0,1)) ?></div><div><div style="font-size:13px;font-weight:500"><?= htmlspecialchars($u['first_name'].' '.$u['last_name']) ?></div><div style="font-size:11px;color:var(--text3)"><?= htmlspecialchars($u['country']??'') ?></div></div></div></td>
            <td><?= badge($u['kyc_status']) ?></td>
            <td style="font-weight:600"><?= fmt_currency((float)$u['wallet_balance']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="section">
    <div class="section-head"><span class="section-title">Pending Withdrawals</span><a href="/admin/withdrawals" class="section-link">View All</a></div>
    <div class="tbl-overflow">
      <table class="data-table">
        <thead><tr><th>Investor</th><th>Amount</th><th>Method</th></tr></thead>
        <tbody>
        <?php foreach ($pending_wr as $w): ?>
          <tr>
            <td style="font-weight:500"><?= htmlspecialchars($w['user_name']) ?></td>
            <td style="font-weight:700"><?= fmt_currency((float)$w['amount']) ?></td>
            <td style="color:var(--text2);font-size:12px"><?= htmlspecialchars(ucfirst($w['method'])) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($pending_wr)): ?><tr><td colspan="3" style="text-align:center;color:var(--text3);padding:1.5rem">No pending withdrawals.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="section">
  <div class="section-head"><span class="section-title">Investment Products</span><a href="/admin/investments" class="section-link">Manage</a></div>
  <div class="tbl-overflow">
    <table class="data-table">
      <thead><tr><th>Name</th><th>Type</th><th>ROI</th><th>Progress</th><th>Investors</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach ($investments as $inv):
        $pct = $inv['funding_target'] > 0 ? min(100, round(($inv['funding_raised']/$inv['funding_target'])*100)) : null;
      ?>
        <tr>
          <td style="font-weight:500;max-width:180px"><div style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($inv['name']) ?></div></td>
          <td><span class="badge badge-<?= $inv['type']==='real_estate'?'re':'if' ?>"><?= $inv['type']==='real_estate'?'Real Estate':'Index Fund' ?></span></td>
          <td style="color:var(--green);font-weight:700"><?= $inv['roi'] ?>%</td>
          <td style="min-width:120px">
            <?php if ($pct !== null): ?><div style="font-size:11px;color:var(--text3);margin-bottom:3px"><?= $pct ?>% of <?= fmt_currency((float)$inv['funding_target']) ?></div><div style="height:4px;background:var(--surface3);border-radius:3px;overflow:hidden"><div style="height:100%;background:<?= $pct>=100?'var(--green)':'var(--accent)' ?>;border-radius:3px;width:<?= $pct ?>%"></div></div><?php else: ?><span style="font-size:11.5px;color:var(--text3)"><?= fmt_currency((float)$inv['funding_raised']) ?> raised</span><?php endif; ?>
          </td>
          <td style="text-align:center"><?= $inv['investor_count'] ?></td>
          <td><?= badge($inv['status']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
