<?php /* views/admin/withdrawals.php */ ?>
<div class="page-header"><h1 class="page-title">Withdrawal Requests</h1><p class="page-sub">Review and process investor withdrawal requests.</p></div>
<div class="tabs" style="margin-bottom:1.5rem">
  <?php foreach ([['all','All'],['pending','Pending'],['approved','Approved'],['completed','Completed'],['rejected','Rejected']] as [$f,$l]): ?>
    <a href="/admin/withdrawals?status=<?= $f ?>" class="tab<?= ($filter??'pending')===$f?' active':'' ?>"><?= $l ?></a>
  <?php endforeach; ?>
</div>
<div class="section">
  <div class="tbl-overflow">
    <table class="data-table">
      <thead><tr><th>Reference</th><th>Investor</th><th>Amount</th><th>Method &amp; Destination</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($withdrawals as $w):
        $det = is_array($w['details']) ? $w['details'] : (json_decode($w['details'] ?? '{}', true) ?: []);
      ?>
        <tr>
          <td style="font-family:monospace;font-size:11.5px;color:var(--text3)"><?= htmlspecialchars($w['reference']) ?></td>
          <td><div style="font-weight:500"><?= htmlspecialchars($w['user_name']) ?></div><div style="font-size:11px;color:var(--text3)"><?= htmlspecialchars($w['email']) ?></div></td>
          <td style="font-weight:700"><?= fmt_currency((float)$w['amount']) ?></td>
          <td>
            <div style="font-size:12px;font-weight:600;margin-bottom:2px"><?= htmlspecialchars(ucfirst($w['method'])) ?></div>
            <?php if ($w['method']==='crypto'): ?>
              <div style="font-size:10.5px;color:var(--text3)"><?= htmlspecialchars($det['coin'] ?? '') ?></div>
              <div style="font-family:monospace;font-size:10px;color:var(--text2);word-break:break-all;max-width:180px"><?= htmlspecialchars($det['address'] ?? $det['wallet_address'] ?? '—') ?></div>
            <?php elseif ($w['method']==='paypal'): ?>
              <div style="font-size:11px;color:var(--text2)"><?= htmlspecialchars($det['paypal_email'] ?? $det['email'] ?? '—') ?></div>
            <?php elseif ($w['method']==='wire'): ?>
              <div style="font-size:10.5px;color:var(--text2)"><?= htmlspecialchars($det['account_name'] ?? '') ?></div>
              <div style="font-family:monospace;font-size:10px;color:var(--text3)"><?= htmlspecialchars($det['account_number'] ?? $det['iban'] ?? '—') ?></div>
              <?php if (!empty($det['bank_name'])): ?><div style="font-size:10px;color:var(--text3)"><?= htmlspecialchars($det['bank_name']) ?></div><?php endif; ?>
            <?php else: ?>
              <div style="font-size:11px;color:var(--text3)">—</div>
            <?php endif; ?>
          </td>
          <td style="font-size:12px;color:var(--text3)"><?= fmt_date($w['created_at']) ?></td>
          <td><?= badge($w['status']) ?><?php if (!empty($w['rejection_note'])): ?><div style="font-size:10px;color:var(--red);margin-top:2px" title="<?= htmlspecialchars($w['rejection_note']) ?>">Reason on hover</div><?php endif; ?></td>
          <td>
            <div style="display:flex;gap:4px;flex-wrap:wrap">
              <?php if ($w['status']==='pending'): ?>
                <button onclick="wdAction(<?= $w['id'] ?>,'approve')" class="btn btn-sm" style="background:var(--green-bg);color:var(--green);border:1px solid var(--green-b)"><?= svgIcon('check',11,'var(--green)') ?>Approve</button>
                <button onclick="wdAction(<?= $w['id'] ?>,'reject')" class="btn btn-sm" style="background:var(--red-bg);color:var(--red);border:1px solid var(--red-b)"><?= svgIcon('x',11,'var(--red)') ?>Reject</button>
              <?php elseif ($w['status']==='approved'): ?>
                <button onclick="wdAction(<?= $w['id'] ?>,'complete')" class="btn btn-primary btn-sm"><?= svgIcon('check',11,'#fff') ?>Mark Complete</button>
              <?php else: ?><span style="font-size:11px;color:var(--text3)">—</span><?php endif; ?>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($withdrawals)): ?><tr><td colspan="7" style="text-align:center;color:var(--text3);padding:2.5rem">No withdrawal requests.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<script>
async function wdAction(id, action) {
  let note = '';
  if (action === 'reject') { note = prompt('Rejection reason (required):'); if (!note) return; }
  const fd = new FormData();
  if (note) fd.append('note', note);
  const data = await post('/admin/withdrawals/'+id+'/'+action, fd, true);
  if (data.success) location.reload();
  else showFlash(data.error||'Failed.','err');
}
</script>
