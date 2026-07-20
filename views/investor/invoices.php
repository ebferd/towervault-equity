<?php /* views/investor/invoices.php */ ?>
<style>
.inv-list-pg{max-width:800px;margin:0 auto}
.inv-list-head{display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem}
.inv-list-title{font-size:1.25rem;font-weight:800;color:var(--mist-900);letter-spacing:-.3px}
.inv-list-sub{font-size:12.5px;color:var(--mist-400);margin-top:2px}
.inv-tabs{display:flex;gap:4px;padding:4px;background:var(--mist-50);border-radius:10px;width:fit-content;margin-bottom:1.25rem;border:1px solid var(--mist-100)}
.inv-tab{padding:5px 14px;border-radius:7px;font-size:12px;font-weight:600;color:var(--mist-400);text-decoration:none;transition:all .15s}
.inv-tab.active{background:#fff;color:var(--mist-900);box-shadow:0 1px 3px rgba(0,0,0,.08)}
.inv-tab:hover:not(.active){color:var(--mist-700)}
.inv-card-list{display:flex;flex-direction:column;gap:.65rem}
.inv-row{display:flex;align-items:center;gap:1rem;padding:.9rem 1rem;background:#fff;border:1px solid var(--mist-100);border-radius:12px;text-decoration:none;transition:border-color .15s,box-shadow .15s;cursor:pointer}
.inv-row:hover{border-color:var(--em-300);box-shadow:0 2px 8px rgba(0,0,0,.06)}
.inv-row-icon{width:38px;height:38px;border-radius:9px;background:var(--mist-50);border:1px solid var(--mist-100);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.inv-row-icon.pending{background:#FFFBEB;border-color:#FDE68A;color:#D97706}
.inv-row-icon.paid{background:#ECFDF5;border-color:#A7F3D0;color:#059669}
.inv-row-icon.cancelled{background:var(--mist-50);border-color:var(--mist-200);color:var(--mist-400)}
.inv-row-main{flex:1;min-width:0}
.inv-row-title{font-size:13px;font-weight:700;color:var(--mist-900);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.inv-row-meta{font-size:11px;color:var(--mist-400);margin-top:2px}
.inv-row-right{text-align:right;flex-shrink:0}
.inv-row-amt{font-size:13.5px;font-weight:800;color:var(--mist-900);letter-spacing:-.2px}
.inv-row-status{display:inline-flex;align-items:center;gap:4px;font-size:10.5px;font-weight:600;margin-top:3px}
.inv-row-status.pending{color:#D97706}
.inv-row-status.pending::before{content:'';width:5px;height:5px;border-radius:50%;background:#F59E0B;display:inline-block}
.inv-row-status.paid{color:#059669}
.inv-row-status.paid::before{content:'';width:5px;height:5px;border-radius:50%;background:#10B981;display:inline-block}
.inv-row-status.cancelled{color:var(--mist-400)}
.inv-row-chevron{color:var(--mist-200);flex-shrink:0}
.inv-empty{text-align:center;padding:3.5rem 1rem;color:var(--mist-400)}
.inv-empty svg{color:var(--mist-200);margin-bottom:.75rem}
.inv-empty h4{font-size:15px;font-weight:700;color:var(--mist-600);margin-bottom:.35rem}
.inv-empty p{font-size:13px;line-height:1.6}
</style>

<div class="inv-list-pg">
  <div class="inv-list-head">
    <div>
      <div class="inv-list-title">Payment Invoices</div>
      <div class="inv-list-sub">View and pay invoices issued to your account</div>
    </div>
    <a href="/investor/dashboard" class="btn btn-outline btn-sm">← Back to dashboard</a>
  </div>

  <div class="inv-tabs">
    <?php
    $tabs = ['all'=>'All', 'pending'=>'Pending', 'paid'=>'Paid', 'cancelled'=>'Cancelled'];
    foreach ($tabs as $s => $l):
      $cnt = $s === 'all' ? count($invoices_all ?? []) : count(array_filter($invoices_all ?? [], fn($r) => $r['status'] === $s));
    ?>
    <a href="/investor/invoices?status=<?= $s ?>" class="inv-tab<?= $activeTab===$s?' active':'' ?>">
      <?= $l ?><?php if ($cnt > 0): ?> <span style="background:var(--mist-200);border-radius:20px;padding:0 6px;font-size:10px;font-weight:700;color:var(--mist-600)"><?= $cnt ?></span><?php endif; ?>
    </a>
    <?php endforeach; ?>
  </div>

  <div class="inv-card-list">
    <?php if (empty($invoices)): ?>
    <div class="inv-empty">
      <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="13" x2="15" y2="13"/></svg>
      <h4>No invoices here</h4>
      <p>You have no <?= $activeTab !== 'all' ? $activeTab : '' ?> invoices at this time.</p>
    </div>
    <?php else: foreach ($invoices as $inv):
      $overdue = $inv['status'] === 'pending' && strtotime($inv['due_date']) < time();
    ?>
    <a href="/investor/invoices/<?= htmlspecialchars($inv['reference']) ?>" class="inv-row">
      <div class="inv-row-icon <?= htmlspecialchars($inv['status']) ?>">
        <?php if ($inv['status'] === 'paid'): ?>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg>
        <?php elseif ($inv['status'] === 'cancelled'): ?>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        <?php else: ?>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        <?php endif; ?>
      </div>
      <div class="inv-row-main">
        <div class="inv-row-title"><?= htmlspecialchars($inv['title']) ?></div>
        <div class="inv-row-meta">
          <?php if ($inv['status'] === 'paid'): ?>
            Paid <?= date('d M Y', strtotime($inv['paid_at'] ?? $inv['created_at'])) ?>
          <?php elseif ($inv['status'] === 'cancelled'): ?>
            Cancelled · Issued <?= date('d M Y', strtotime($inv['created_at'])) ?>
          <?php elseif ($overdue): ?>
            <span style="color:#DC2626;font-weight:600">Overdue</span> · Due <?= date('d M Y', strtotime($inv['due_date'])) ?>
          <?php else: ?>
            Due <?= date('d M Y', strtotime($inv['due_date'])) ?> · Issued <?= date('d M Y', strtotime($inv['created_at'])) ?>
          <?php endif; ?>
        </div>
      </div>
      <div class="inv-row-right">
        <div class="inv-row-amt"><?= htmlspecialchars(platform_setting('platform_symbol','$')) ?><?= number_format((float)$inv['amount'], 2) ?></div>
        <div class="inv-row-status <?= htmlspecialchars($inv['status']) ?>"><?= ucfirst($inv['status']) ?></div>
      </div>
      <svg class="inv-row-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
    </a>
    <?php endforeach; endif; ?>
  </div>
</div>
