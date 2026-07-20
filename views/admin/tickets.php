<?php /* views/admin/tickets.php */ ?>
<div class="page-header"><h1 class="page-title">Support Tickets</h1><p class="page-sub">Manage investor support requests.</p></div>
<div class="tabs" style="margin-bottom:1.5rem">
  <?php foreach ([['open','Open'],['in_progress','In Progress'],['resolved','Resolved'],['closed','Closed'],['all','All']] as [$f,$l]): ?>
    <a href="/admin/tickets?status=<?= $f ?>" class="tab<?= ($filter??'open')===$f?' active':'' ?>"><?= $l ?></a>
  <?php endforeach; ?>
</div>
<div style="display:grid;grid-template-columns:340px 1fr;gap:1.5rem;align-items:start">
  <!-- Ticket list -->
  <div class="section">
    <?php if (empty($tickets)): ?>
      <div style="padding:2.5rem;text-align:center;color:var(--text3)"><?= svgIcon('ticket',28,'var(--border2)') ?><p style="margin-top:.75rem">No tickets.</p></div>
    <?php else: foreach ($tickets as $t): $isActive = isset($active) && $active && $active['id']===$t['id']; ?>
      <a href="/admin/tickets?status=<?= $filter??'open' ?>&ticket=<?= $t['id'] ?>" style="display:block;padding:.9rem 1.25rem;border-bottom:1px solid var(--border);text-decoration:none;background:<?= $isActive?'var(--accent-l)':'' ?>;transition:background .1s">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:.5rem;margin-bottom:.25rem">
          <div style="font-size:13px;font-weight:<?= $isActive?'600':'500' ?>;color:<?= $isActive?'var(--accent)':'var(--text)' ?>;line-height:1.4;flex:1"><?= htmlspecialchars($t['subject']) ?></div>
          <?= badge($t['status']) ?>
        </div>
        <div style="font-size:11.5px;color:var(--text2);margin-bottom:2px"><?= htmlspecialchars($t['user_name']) ?></div>
        <div style="display:flex;justify-content:space-between;font-size:10.5px;color:var(--text3)"><span><?= htmlspecialchars($t['reference']) ?></span><span><?= time_ago($t['updated_at']) ?></span></div>
      </a>
    <?php endforeach; endif; ?>
  </div>

  <!-- Thread -->
  <?php if ($active): ?>
  <div class="section">
    <div class="section-head">
      <div>
        <div class="section-title"><?= htmlspecialchars($active['subject']) ?></div>
        <div style="font-size:11.5px;color:var(--text3);margin-top:2px"><?= htmlspecialchars($active['reference']) ?> · <?= htmlspecialchars($active['user_name']) ?> · <?= htmlspecialchars($active['email']) ?></div>
      </div>
      <div style="display:flex;gap:.5rem">
        <?= badge($active['status']) ?>
        <?php if ($active['status'] !== 'closed'): ?>
          <button class="btn btn-outline btn-sm" onclick="closeTicket(<?= $active['id'] ?>)"><?= svgIcon('x',11) ?>Close</button>
        <?php endif; ?>
      </div>
    </div>
    <div style="padding:1.1rem 1.25rem;display:flex;flex-direction:column;gap:.75rem;max-height:420px;overflow-y:auto">
      <?php foreach ($active['messages'] as $msg): ?>
        <div style="display:flex;<?= $msg['sender_type']==='admin'?'justify-content:flex-end':'' ?>">
          <div style="max-width:78%;padding:.75rem 1rem;font-size:13px;line-height:1.55;border-radius:var(--r);<?= $msg['sender_type']==='admin'?'background:var(--accent);color:#fff':'background:var(--surface2);color:var(--text);border:1px solid var(--border)' ?>">
            <?= nl2br(htmlspecialchars($msg['message'])) ?>
            <div style="font-size:10px;margin-top:4px;opacity:.6;text-align:<?= $msg['sender_type']==='admin'?'right':'left' ?>"><?= $msg['sender_type']==='admin'?'Support Team · ':($active['user_name'].' · ') ?><?= time_ago($msg['created_at']) ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <?php if ($active['status'] !== 'closed'): ?>
    <div style="padding:.85rem 1.25rem;border-top:1px solid var(--border);display:flex;gap:.65rem">
      <textarea id="reply-msg" placeholder="Type your reply to the investor…" style="flex:1;padding:.7rem .9rem;border:1px solid var(--border);font-size:13px;font-family:'Inter',sans-serif;resize:none;height:72px;border-radius:var(--r);outline:none;color:var(--text)"></textarea>
      <button class="btn btn-primary" onclick="sendReply(<?= $active['id'] ?>)"><?= svgIcon('send',13,'#fff') ?>Send</button>
    </div>
    <?php endif; ?>
  </div>
  <?php else: ?>
  <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:3rem;text-align:center;color:var(--text3)">
    <?= svgIcon('headset',28,'var(--border2)') ?>
    <p style="margin-top:.75rem">Select a ticket to view the conversation.</p>
  </div>
  <?php endif; ?>
</div>
<script>
async function sendReply(id) {
  const msg  = document.getElementById('reply-msg').value.trim();
  if (!msg) return;
  const data = await post('/admin/tickets/'+id+'/reply', {message: msg});
  if (data.success) location.reload();
  else showFlash(data.error || 'Failed.', 'err');
}
async function closeTicket(id) {
  if (!confirm('Close this ticket?')) return;
  const data = await post('/admin/tickets/'+id+'/close', {});
  if (data.success) location.reload();
  else showFlash(data.error || 'Failed.', 'err');
}
</script>
