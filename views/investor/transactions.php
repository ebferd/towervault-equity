<?php /* views/investor/transactions.php */ ?>
<style>
@media(max-width:640px){
  .tbl-overflow{display:none}
  .tx-cards{display:block!important}
}
.tx-cards{display:none}
.tx-card{background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:1rem 1.1rem;margin-bottom:.6rem}
.tx-card-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:.4rem}
.tx-card-desc{font-size:13.5px;font-weight:600;color:var(--text)}
.tx-card-ref{font-size:10.5px;font-family:monospace;color:var(--text3);margin-top:.15rem}
.tx-card-bot{display:flex;align-items:center;justify-content:space-between;margin-top:.6rem}
</style>
<div class="page-header"><h1 class="page-title">Transaction History</h1><p class="page-sub">A complete record of all deposits, investments, returns, and withdrawals.</p></div>
<div class="tabs" style="margin-bottom:1.5rem">
  <?php foreach ([['all','All'],['deposit','Deposits'],['investment','Investments'],['return','Returns'],['withdrawal','Withdrawals'],['referral_commission','Referrals']] as [$f,$l]): ?>
    <a href="/investor/transactions?type=<?= $f ?>" class="tab<?= ($filter??'all')===$f?' active':'' ?>"><?= $l ?></a>
  <?php endforeach; ?>
</div>
<div class="section">
  <?php if (empty($data)): ?>
    <div style="padding:3rem;text-align:center;color:var(--text3)">No transactions found.</div>
  <?php else: ?>
  <div class="tbl-overflow">
    <table class="data-table">
      <thead><tr><th>Description</th><th>Reference</th><th>Date</th><th>Amount</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach ($data as $tx):
        $credit = in_array($tx['type'],['return','deposit','referral_commission','adjustment']);
      ?>
        <tr>
          <td style="font-weight:500"><?= htmlspecialchars($tx['description']??ucfirst(str_replace('_',' ',$tx['type']))) ?></td>
          <td style="font-family:monospace;font-size:11.5px;color:var(--text3)"><?= htmlspecialchars($tx['reference']) ?></td>
          <td style="color:var(--text2);font-size:12px"><?= fmt_datetime($tx['created_at']) ?></td>
          <td style="font-weight:700;color:<?= $credit?'var(--green)':'var(--text)' ?>"><?= $credit?'+':'-' ?><?= fmt_currency((float)$tx['amount']) ?></td>
          <td><?= badge($tx['status']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <!-- Mobile cards -->
  <div class="tx-cards">
    <?php foreach ($data as $tx):
      $credit = in_array($tx['type'],['return','deposit','referral_commission','adjustment']);
    ?>
    <div class="tx-card">
      <div class="tx-card-top">
        <div>
          <div class="tx-card-desc"><?= htmlspecialchars($tx['description']??ucfirst(str_replace('_',' ',$tx['type']))) ?></div>
          <div class="tx-card-ref"><?= htmlspecialchars($tx['reference']) ?></div>
        </div>
        <div style="font-weight:700;font-size:15px;color:<?= $credit?'var(--green)':'var(--text)' ?>"><?= $credit?'+':'-' ?><?= fmt_currency((float)$tx['amount']) ?></div>
      </div>
      <div class="tx-card-bot">
        <span style="font-size:11.5px;color:var(--text3)"><?= fmt_datetime($tx['created_at']) ?></span>
        <?= badge($tx['status']) ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php renderPagination($page, $pages, '/investor/transactions?type='.($filter??'all').'&'); ?>
  <?php endif; ?>
</div>
