<?php /* views/admin/invoices.php */ ?>
<div class="page-header" style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem">
  <div>
    <h1 class="page-title">Payment Invoices</h1>
    <p class="page-sub">Track all payment invoices issued to investors.</p>
  </div>
</div>

<!-- Status tabs -->
<div class="tabs" style="margin-bottom:1.5rem">
  <?php foreach (['pending'=>'Pending','paid'=>'Paid','cancelled'=>'Cancelled','all'=>'All'] as $s=>$l):
    $c = $s === 'all' ? ($counts['total'] ?? 0) : ($counts[$s] ?? 0);
  ?>
    <a href="/admin/invoices?status=<?= $s ?>" class="tab<?= $filter===$s?' active':'' ?>"><?= $l ?><?php if ($c > 0): ?> <span class="badge" style="margin-left:4px"><?= $c ?></span><?php endif; ?></a>
  <?php endforeach; ?>
</div>

<div class="section">
  <div class="tbl-overflow">
    <table class="data-table">
      <thead>
        <tr>
          <th>Investor</th>
          <th>Reference</th>
          <th>Title</th>
          <th>Amount</th>
          <th>Due date</th>
          <th>Method</th>
          <th>Issued</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php if (empty($invoices)): ?>
        <tr><td colspan="9" style="text-align:center;color:var(--text3);padding:2.5rem">No invoices found.</td></tr>
      <?php else: foreach ($invoices as $inv): ?>
        <tr>
          <td>
            <div style="font-weight:500"><?= htmlspecialchars($inv['first_name'].' '.$inv['last_name']) ?></div>
            <div style="font-size:11px;color:var(--text3)"><?= htmlspecialchars($inv['email']) ?></div>
          </td>
          <td style="font-family:monospace;font-size:11.5px"><?= htmlspecialchars($inv['reference']) ?></td>
          <td><?= htmlspecialchars($inv['title']) ?></td>
          <td style="font-weight:700;color:var(--green)"><?= fmt_currency((float)$inv['amount']) ?></td>
          <td><?= date('d M Y', strtotime($inv['due_date'])) ?></td>
          <td><?= badge(ucfirst($inv['payment_method'])) ?></td>
          <td style="font-size:12px;color:var(--text3)"><?= time_ago($inv['created_at']) ?></td>
          <td><?= badge($inv['status']) ?></td>
          <td>
            <a href="/admin/users/<?= (int)$inv['user_id'] ?>" class="btn btn-outline btn-xs">View user</a>
            <?php if ($inv['status'] === 'pending'): ?>
              <button class="btn btn-outline btn-xs" style="color:var(--red);border-color:var(--red-b)" onclick="cancelInvoice(<?= (int)$inv['id'] ?>, this)">Cancel</button>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
async function cancelInvoice(id, btn) {
  if (!confirm('Cancel this invoice? The investor will no longer be able to pay it.')) return;
  btn.disabled = true; btn.textContent = '…';
  const data = await post('/admin/invoices/' + id + '/cancel', {_token: document.querySelector('meta[name="csrf-token"]').content});
  if (data.success) location.reload();
  else { btn.disabled = false; btn.textContent = 'Cancel'; showFlash(data.error || 'Failed.', 'err'); }
}
</script>
