<?php /* views/admin/deposits.php */ ?>
<div class="page-header">
  <h1 class="page-title">Deposit Management</h1>
  <p class="page-sub">Review investor deposit submissions and confirm or reject payments.</p>
</div>

<?php if ($pendingCount > 0): ?>
<div class="alert alert-warn" style="margin-bottom:1.5rem">
  <?= svgIcon('bell',14,'var(--warn)') ?>
  <strong><?= $pendingCount ?></strong> deposit<?= $pendingCount > 1 ? 's' : '' ?> awaiting review.
</div>
<?php endif; ?>

<!-- Filter tabs -->
<div class="tabs" style="margin-bottom:1.5rem">
  <?php foreach (['submitted'=>'Awaiting Review','pending'=>'Pending','paid'=>'Approved','rejected'=>'Rejected','all'=>'All'] as $f=>$l): ?>
    <a href="/admin/deposits?status=<?= $f ?>" class="tab<?= $filter===$f?' active':'' ?>"><?= $l ?></a>
  <?php endforeach; ?>
</div>

<div class="section">
  <div class="tbl-overflow">
    <table class="data-table">
      <thead>
        <tr>
          <th>Investor</th>
          <th>Reference</th>
          <th>Amount</th>
          <th>Method / Coin</th>
          <th>Wallet / Details</th>
          <th>Submitted</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php if (empty($data)): ?>
        <tr><td colspan="8" style="text-align:center;color:var(--text3);padding:2.5rem">No deposits found.</td></tr>
      <?php else: foreach ($data as $dep): ?>
        <tr>
          <td>
            <div style="font-weight:500"><?= htmlspecialchars($dep['user_name']) ?></div>
            <div style="font-size:11px;color:var(--text3)"><?= htmlspecialchars($dep['user_email']) ?></div>
          </td>
          <td style="font-family:monospace;font-size:12px"><?= htmlspecialchars($dep['reference']) ?></td>
          <td style="font-weight:700;color:var(--green)"><?= fmt_currency((float)$dep['amount']) ?></td>
          <td>
            <span class="badge"><?= ucfirst($dep['method']) ?></span>
            <?php if ($dep['coin']): ?><span style="font-size:11px;color:var(--text3);display:block;margin-top:2px"><?= strtoupper(htmlspecialchars($dep['coin'])) ?></span><?php endif; ?>
          </td>
          <td style="font-size:11.5px;font-family:monospace;max-width:160px;word-break:break-all;color:var(--text2)">
            <?= htmlspecialchars($dep['wallet_address'] ?? '—') ?>
          </td>
          <td style="font-size:12px;color:var(--text2)"><?= fmt_date($dep['created_at']) ?></td>
          <td><?= badge($dep['status']) ?></td>
          <td>
            <?php if (in_array($dep['status'], ['pending','submitted'])): ?>
              <div style="display:flex;gap:4px">
                <button class="btn btn-sm" style="background:var(--green-bg);color:var(--green);border:1px solid var(--green-b)"
                  onclick="openApprove(<?= $dep['id'] ?>,'<?= htmlspecialchars($dep['user_name']) ?>','<?= htmlspecialchars(fmt_currency((float)$dep['amount'])) ?>')">
                  <?= svgIcon('check',11,'var(--green)') ?> Approve
                </button>
                <button class="btn btn-sm" style="background:var(--red-bg);color:var(--red);border:1px solid var(--red-b)"
                  onclick="openReject(<?= $dep['id'] ?>,'<?= htmlspecialchars($dep['user_name']) ?>')">
                  <?= svgIcon('x',11,'var(--red)') ?> Reject
                </button>
              </div>
            <?php elseif ($dep['admin_note']): ?>
              <span style="font-size:11px;color:var(--text3)" title="<?= htmlspecialchars($dep['admin_note']) ?>">Note: <?= htmlspecialchars(substr($dep['admin_note'],0,30)) ?>…</span>
            <?php else: ?>
              <span style="font-size:11.5px;color:var(--text3)">—</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  <?php renderPagination($page, $pages, '/admin/deposits?status='.$filter.'&'); ?>
</div>

<!-- Approve Modal -->
<div id="approve-modal" class="modal-overlay" style="display:none">
  <div class="modal" style="max-width:440px">
    <div class="modal-head"><h3 class="modal-title">Confirm Deposit</h3><button class="modal-close" onclick="this.closest('.modal-overlay').style.display='none'">&times;</button></div>
    <div class="modal-body">
      <div id="approve-info" style="background:var(--green-bg);border:1px solid var(--green-b);border-radius:var(--r);padding:.85rem 1rem;margin-bottom:1rem;font-size:13.5px;font-weight:600;color:var(--green)"></div>
      <div class="fg"><label class="fl">Admin Note <span class="fl-opt">(optional)</span></label><input class="fi" id="approve-note" placeholder="e.g. Payment received and verified"/></div>
      <div style="display:flex;gap:.65rem;margin-top:1rem">
        <button class="btn btn-outline" onclick="document.getElementById('approve-modal').style.display='none'">Cancel</button>
        <button class="btn btn-primary" style="flex:1;background:linear-gradient(135deg,var(--green),#1a9940)" id="approve-btn" onclick="submitApprove()">
          <?= svgIcon('check',14,'#fff') ?> Confirm & Credit Wallet
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Reject Modal -->
<div id="reject-modal" class="modal-overlay" style="display:none">
  <div class="modal" style="max-width:440px">
    <div class="modal-head"><h3 class="modal-title">Reject Deposit</h3><button class="modal-close" onclick="this.closest('.modal-overlay').style.display='none'">&times;</button></div>
    <div class="modal-body">
      <div id="reject-info" style="background:var(--red-bg);border:1px solid var(--red-b);border-radius:var(--r);padding:.85rem 1rem;margin-bottom:1rem;font-size:13px;color:var(--red)"></div>
      <div class="fg"><label class="fl">Rejection Reason <span style="color:var(--red)">*</span></label><input class="fi" id="reject-reason" placeholder="e.g. Payment not received, incorrect amount sent"/></div>
      <div style="display:flex;gap:.65rem;margin-top:1rem">
        <button class="btn btn-outline" onclick="document.getElementById('reject-modal').style.display='none'">Cancel</button>
        <button class="btn btn-primary" style="flex:1;background:linear-gradient(135deg,var(--red),#a93226)" id="reject-btn" onclick="submitReject()">
          <?= svgIcon('x',14,'#fff') ?> Reject Deposit
        </button>
      </div>
    </div>
  </div>
</div>

<script>
let _approveId = null, _rejectId = null;

function openApprove(id, name, amount) {
  _approveId = id;
  document.getElementById('approve-info').textContent = 'Credit ' + amount + ' to ' + name + '\'s wallet?';
  document.getElementById('approve-note').value = '';
  document.getElementById('approve-modal').style.display = 'flex';
}

function openReject(id, name) {
  _rejectId = id;
  document.getElementById('reject-info').textContent = 'Reject deposit from ' + name + '? The investor will be notified.';
  document.getElementById('reject-reason').value = '';
  document.getElementById('reject-modal').style.display = 'flex';
}

async function submitApprove() {
  const btn = document.getElementById('approve-btn');
  setLoading(btn, true);
  const data = await post('/admin/deposits/' + _approveId + '/approve', { note: document.getElementById('approve-note').value });
  setLoading(btn, false);
  if (data.success) { showFlash('Deposit approved and wallet credited.', 'ok'); location.reload(); }
  else showFlash(data.error || 'Failed to approve.', 'err');
}

async function submitReject() {
  const reason = document.getElementById('reject-reason').value.trim();
  if (!reason) { showFlash('Please enter a rejection reason.', 'warn'); return; }
  const btn = document.getElementById('reject-btn');
  setLoading(btn, true);
  const data = await post('/admin/deposits/' + _rejectId + '/reject', { reason });
  setLoading(btn, false);
  if (data.success) { showFlash('Deposit rejected.', 'ok'); location.reload(); }
  else showFlash(data.error || 'Failed to reject.', 'err');
}
</script>
