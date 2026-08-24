<?php
$sent   = (int) $campaign['sent_count'];
$opened = (int) $openedCount;
$notOpened = max(0, count($recipients) - $opened);
$rate   = $sent > 0 ? round($opened / $sent * 100) : 0;
?>
<div class="page-head">
  <div>
    <a href="/admin/marketing" style="font-size:12.5px;color:var(--text3);text-decoration:none">&larr; Back to Marketing</a>
    <h1 class="page-title" style="margin-top:.3rem"><?= htmlspecialchars($campaign['subject']) ?></h1>
    <p class="page-sub">Sent <?= date('M j, Y g:i A', strtotime($campaign['created_at'])) ?></p>
  </div>
</div>

<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.25rem">
  <?php foreach ([
      ['Recipients', (int)$campaign['recipient_count'], '#111827'],
      ['Sent',       $sent,   '#0f7a4a'],
      ['Opened',     $opened . ' (' . $rate . '%)', '#1E3A5F'],
      ['Not opened', $notOpened, '#9CA3AF'],
    ] as [$lbl,$val,$col]): ?>
    <div class="section" style="padding:1.1rem 1.25rem;margin:0">
      <div style="font-size:11px;letter-spacing:.5px;text-transform:uppercase;color:var(--text3);margin-bottom:6px"><?= $lbl ?></div>
      <div style="font-size:22px;font-weight:700;color:<?= $col ?>"><?= $val ?></div>
    </div>
  <?php endforeach; ?>
</div>

<div class="section">
  <div class="section-head" style="gap:.75rem">
    <span class="section-title">Recipients</span>
    <div style="display:flex;gap:.4rem;align-items:center;margin-left:auto">
      <select id="mc-filter" class="fi" style="width:auto;padding:6px 10px;font-size:12.5px">
        <option value="all">All (<?= count($recipients) ?>)</option>
        <option value="opened">Opened (<?= $opened ?>)</option>
        <option value="not">Not opened (<?= $notOpened ?>)</option>
      </select>
      <button type="button" class="btn btn-ghost" id="mc-copy" style="font-size:12.5px"><?= svgIcon('file',13) ?>Copy non-openers</button>
    </div>
  </div>
  <div class="section-body" style="padding:0">
    <?php if (empty($recipients)): ?>
      <p style="padding:1.5rem;color:var(--text3);font-size:13px">No recipient records for this campaign. (Open-tracking applies to campaigns sent after the feature was added.)</p>
    <?php else: ?>
    <table class="tbl" style="width:100%;border-collapse:collapse">
      <thead><tr>
        <th style="text-align:left">Email</th><th>Status</th><th>Opens</th><th style="text-align:right">First opened</th>
      </tr></thead>
      <tbody id="mc-rows">
        <?php foreach ($recipients as $r):
          $isOpen = $r['opened_at'] !== null; ?>
          <tr class="mc-row" data-open="<?= $isOpen ? 'opened' : 'not' ?>" data-email="<?= htmlspecialchars($r['email']) ?>">
            <td style="text-align:left;font-family:monospace;font-size:12.5px"><?= htmlspecialchars($r['email']) ?></td>
            <td style="text-align:center">
              <?php if ($isOpen): ?>
                <span style="display:inline-block;background:#E7F3EC;color:#0f7a4a;font-size:11px;font-weight:600;padding:3px 10px;border-radius:99px">Opened</span>
              <?php else: ?>
                <span style="display:inline-block;background:#F1F3F6;color:#9CA3AF;font-size:11px;font-weight:600;padding:3px 10px;border-radius:99px">Not opened</span>
              <?php endif; ?>
            </td>
            <td style="text-align:center;color:<?= $isOpen ? '#111827' : '#C4C9D4' ?>"><?= (int)$r['open_count'] ?></td>
            <td style="text-align:right;color:#9CA3AF;font-size:12.5px"><?= $isOpen ? date('M j, g:i A', strtotime($r['opened_at'])) : '—' ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<style>
  .tbl th{font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:#9CA3AF;font-weight:600;padding:12px 16px;border-bottom:1px solid #F0F2F7}
  .tbl td{font-size:13px;color:#374151;padding:12px 16px;border-bottom:1px solid #F5F6F8}
  .tbl tbody tr:last-child td{border-bottom:none}
</style>

<script>
const filterSel = document.getElementById('mc-filter');
if (filterSel) filterSel.addEventListener('change', function(){
  const v = this.value;
  document.querySelectorAll('.mc-row').forEach(row => {
    row.style.display = (v === 'all' || row.dataset.open === v) ? '' : 'none';
  });
});
const copyBtn = document.getElementById('mc-copy');
if (copyBtn) copyBtn.addEventListener('click', function(){
  const emails = Array.from(document.querySelectorAll('.mc-row'))
    .filter(r => r.dataset.open === 'not').map(r => r.dataset.email);
  if (!emails.length) { alert('Everyone opened this one — no non-openers to copy.'); return; }
  navigator.clipboard.writeText(emails.join('\n')).then(() => {
    this.innerHTML = '✓ Copied ' + emails.length + ' email(s)';
    setTimeout(() => location.reload(), 1400);
  });
});
</script>
