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
<div class="page-header" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1rem">
  <div><h1 class="page-title">Transaction History</h1><p class="page-sub">A complete record of all deposits, investments, returns, and withdrawals.</p></div>
  <button type="button" class="qbtn outline" style="height:38px;white-space:nowrap" onclick="document.getElementById('stmt-modal').style.display='flex'">
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
    Download statement
  </button>
</div>

<!-- Statement download modal -->
<div id="stmt-modal" class="modal-overlay" style="display:none">
  <div class="modal" style="max-width:420px">
    <div class="modal-head">
      <h3 class="modal-title">Download account statement</h3>
      <button class="modal-close" onclick="document.getElementById('stmt-modal').style.display='none'">&times;</button>
    </div>
    <div class="modal-body">
      <p style="font-size:13px;color:var(--mist-500);line-height:1.6;margin-bottom:1rem">Choose a period. Your statement is generated as a PDF.</p>
      <div class="fg">
        <label class="fl">Period</label>
        <select class="fi" id="stmt-range" onchange="stmtCustom(this.value)">
          <option value="3m">Last 3 months</option>
          <option value="6m">Last 6 months</option>
          <option value="ytd">This year</option>
          <option value="all">All time</option>
          <option value="custom">Custom range…</option>
        </select>
      </div>
      <div id="stmt-custom" style="display:none;gap:.6rem;grid-template-columns:1fr 1fr;display:grid">
        <div class="fg"><label class="fl">From</label><input class="fi" type="date" id="stmt-from"/></div>
        <div class="fg"><label class="fl">To</label><input class="fi" type="date" id="stmt-to"/></div>
      </div>
      <a href="#" id="stmt-go" class="qbtn primary" style="height:40px;width:100%;justify-content:center;margin-top:.5rem" target="_blank" rel="noopener">Download PDF</a>
    </div>
  </div>
</div>
<script>
function stmtCustom(v){ document.getElementById('stmt-custom').style.display = v==='custom' ? 'grid' : 'none'; }
(function(){
  var go=document.getElementById('stmt-go');
  function build(){
    var v=document.getElementById('stmt-range').value, u='/investor/statement?';
    var today=new Date(), y=today.getFullYear();
    function iso(d){ return d.toISOString().slice(0,10); }
    if(v==='custom'){
      var f=document.getElementById('stmt-from').value, t=document.getElementById('stmt-to').value;
      u+='from='+(f||'')+'&to='+(t||iso(today));
    } else if(v==='all'){ u+='range=all';
    } else if(v==='ytd'){ u+='from='+y+'-01-01&to='+iso(today);
    } else { var m=v==='6m'?6:3; var d=new Date(); d.setMonth(d.getMonth()-m); u+='from='+iso(d)+'&to='+iso(today); }
    go.href=u;
  }
  ['stmt-range','stmt-from','stmt-to'].forEach(function(id){ var e=document.getElementById(id); if(e) e.addEventListener('change',build); });
  build();
})();
</script>
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
