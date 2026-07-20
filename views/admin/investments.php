<?php /* views/admin/investments.php */ ?>
<div class="page-header" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1rem">
  <div><h1 class="page-title">Investment Products</h1><p class="page-sub">Manage real estate listings and index fund products.</p></div>
  <a href="/admin/investments/create" class="btn btn-primary"><?= svgIcon('plus',14,'#fff') ?>New Investment</a>
</div>
<div class="section">
  <div class="tbl-overflow">
    <table class="data-table">
      <thead><tr><th>Name</th><th>Type</th><th>ROI</th><th>Duration</th><th>Progress / Raised</th><th>Investors</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($investments as $inv):
        $pct = $inv['funding_target'] > 0 ? min(100, round(($inv['funding_raised']/$inv['funding_target'])*100)) : null;
      ?>
        <tr>
          <td style="font-weight:500;max-width:180px"><div style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($inv['name']) ?></div></td>
          <td><span class="badge badge-<?= $inv['type']==='real_estate'?'re':'if' ?>"><?= $inv['type']==='real_estate'?'Real Estate':'Index Fund' ?></span></td>
          <td style="color:var(--green);font-weight:700"><?= $inv['roi'] ?>%</td>
          <td style="color:var(--text2)"><?= $inv['duration_value'].' '.ucfirst($inv['duration_unit']).'s' ?></td>
          <td style="min-width:120px">
            <?php if ($pct !== null): ?><div style="font-size:11px;color:var(--text3);margin-bottom:3px"><?= $pct ?>%</div><div style="height:4px;background:var(--surface3);border-radius:3px;overflow:hidden"><div style="height:100%;background:<?= $pct>=100?'var(--green)':'var(--accent)' ?>;width:<?= $pct ?>%;border-radius:3px"></div></div><?php else: ?><span style="font-size:11.5px;color:var(--text3)"><?= fmt_currency((float)$inv['funding_raised']) ?></span><?php endif; ?>
          </td>
          <td style="text-align:center"><?= $inv['investor_count'] ?></td>
          <td><?= badge($inv['status']) ?></td>
          <td>
            <div style="display:flex;gap:4px">
              <a href="/admin/investments/<?= $inv['id'] ?>/edit" class="btn btn-outline btn-sm"><?= svgIcon('edit',11) ?>Edit</a>
              <button class="btn btn-sm" style="background:var(--red-bg);color:var(--red);border:1px solid var(--red-b)" onclick="deleteInv(<?= $inv['id'] ?>,'<?= htmlspecialchars(addslashes($inv['name'])) ?>')"><?= svgIcon('trash',11,'var(--red)') ?></button>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($investments)): ?><tr><td colspan="8" style="text-align:center;color:var(--text3);padding:2.5rem">No investments yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<script>
async function deleteInv(id, name) {
  if (!confirm('Delete "' + name + '"? This cannot be undone.')) return;
  const data = await post('/admin/investments/' + id + '/delete', {});
  if (data.success) location.reload();
  else showFlash(data.error || 'Failed to delete.', 'err');
}
</script>
