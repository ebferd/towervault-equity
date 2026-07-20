<?php /* views/admin/reports.php */ ?>
<div class="page-header"><h1 class="page-title">Platform Reports</h1><p class="page-sub">Financial overview and performance metrics.</p></div>

<div class="stats-grid" style="margin-bottom:1.5rem">
  <?php foreach ([
    ['Total Users',         $stats['total_users']??0,          'active: '.($stats['active_users']??0)],
    ['KYC Pending',         $stats['kyc_pending']??0,          'Awaiting review'],
    ['Total Invested',      fmt_currency((float)($stats['total_invested']??0)), 'Across all active positions'],
    ['Total Returns Paid',  fmt_currency((float)($stats['total_returns_paid']??0)), 'Credited to investors'],
    ['Wallet Balances',     fmt_currency((float)($stats['total_wallet_balance']??0)), 'Investor wallets combined'],
    ['Pending Withdrawals', $stats['pending_withdrawals']??0,  'Awaiting processing'],
    ['Active Investments',  $stats['active_investments']??0,   'Listed products'],
    ['Open Tickets',        $stats['open_tickets']??0,         'Awaiting response'],
  ] as [$l,$v,$s]): ?>
    <div class="stat-card"><div class="sc-label"><?= $l ?></div><div class="sc-value"><?= $v ?></div><div class="sc-sub"><?= htmlspecialchars($s) ?></div></div>
  <?php endforeach; ?>
</div>

<div class="section">
  <div class="section-head"><span class="section-title">Monthly Transaction Summary</span><span class="section-meta">Last 12 months</span></div>
  <?php if (empty($monthly)): ?>
    <div style="padding:2.5rem;text-align:center;color:var(--text3)">No transaction data yet.</div>
  <?php else: ?>
  <div class="tbl-overflow">
    <table class="data-table">
      <thead><tr><th>Month</th><th>Deposits</th><th>Investments</th><th>Returns Paid</th><th>Withdrawals</th><th>Active Investors</th></tr></thead>
      <tbody>
      <?php foreach ($monthly as $row): ?>
        <tr>
          <td style="font-weight:600"><?= htmlspecialchars(date('M Y', strtotime($row['month'].'-01'))) ?></td>
          <td style="color:var(--green);font-weight:600"><?= fmt_currency((float)$row['deposits']) ?></td>
          <td style="color:var(--accent);font-weight:600"><?= fmt_currency((float)$row['investments']) ?></td>
          <td style="color:var(--green)"><?= fmt_currency((float)$row['returns']) ?></td>
          <td style="color:var(--warn)"><?= fmt_currency((float)$row['withdrawals']) ?></td>
          <td style="font-weight:600"><?= $row['active_users'] ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
