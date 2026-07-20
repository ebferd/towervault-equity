<?php /* views/admin/kyc_queue.php */ ?>
<div class="page-header">
  <div><h1 class="page-title">KYC Verification</h1><p class="page-sub">Review and approve identity documents submitted by investors.</p></div>
</div>

<div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1.25rem">
  <?php foreach (['pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected','all'=>'All'] as $s => $l): ?>
    <a href="/admin/kyc?status=<?= $s ?>" class="btn <?= $filter===$s ? 'btn-primary' : 'btn-outline' ?> btn-sm"><?= $l ?></a>
  <?php endforeach; ?>
</div>

<div class="section">
  <?php if (empty($queue)): ?>
    <div style="padding:3rem;text-align:center;color:var(--text3)">
      <?= svgIcon('shield',28,'var(--border2)') ?>
      <p style="margin-top:.75rem">No <?= $filter === 'all' ? '' : $filter ?> KYC submissions.</p>
    </div>
  <?php else: ?>
  <div class="tbl-overflow">
    <table class="data-table">
      <thead><tr><th>Investor</th><th>Country</th><th>ID Type</th><th>Submitted</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($queue as $item): ?>
        <tr>
          <td><div style="font-weight:500"><?= htmlspecialchars($item['user_name']) ?></div><div style="font-size:11px;color:var(--text3)"><?= htmlspecialchars($item['email']) ?></div></td>
          <td style="color:var(--text2)"><?= htmlspecialchars($item['country']??'') ?></td>
          <td style="color:var(--text2)"><?= htmlspecialchars(ucwords(str_replace('_',' ',$item['id_type']))) ?></td>
          <td style="font-size:12px;color:var(--text3)"><?= fmt_date($item['submitted_at']) ?></td>
          <td><?= badge($item['status']) ?></td>
          <td>
            <?php if ($item['status'] === 'pending'): ?>
              <a href="/admin/kyc/<?= $item['id'] ?>" class="btn btn-outline btn-sm"><?= svgIcon('eye',11) ?>Review</a>
            <?php else: ?>
              <a href="/admin/kyc/<?= $item['id'] ?>" class="btn btn-outline btn-sm"><?= svgIcon('eye',11) ?>View</a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
