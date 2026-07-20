<?php /* views/admin/ticket_detail.php */ ?>
<div class="page-header" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1rem">
  <div>
    <div style="font-size:11px;color:var(--text3);margin-bottom:.35rem"><a href="/admin/tickets" style="color:var(--accent)">Support Tickets</a> <span style="color:var(--border2)">›</span> <?= htmlspecialchars($ticket['reference']) ?></div>
    <h1 class="page-title"><?= htmlspecialchars($ticket['subject']) ?></h1>
    <p class="page-sub"><?= htmlspecialchars($ticket['user_name']) ?> · <?= htmlspecialchars($ticket['email']) ?> · <?= fmt_date($ticket['created_at']) ?></p>
  </div>
  <div style="display:flex;gap:.65rem;flex-wrap:wrap;align-items:center">
    <?= badge($ticket['status']) ?>
    <?php if ($ticket['status'] !== 'closed'): ?>
      <button class="btn btn-outline btn-sm" onclick="closeTicket(<?= $ticket['id'] ?>)"><?= svgIcon('x',12) ?>Close Ticket</button>
    <?php endif; ?>
    <a href="/admin/tickets" class="btn btn-outline btn-sm">← Back</a>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 280px;gap:1.5rem;align-items:start">
  <!-- Thread -->
  <div class="section">
    <div class="section-head"><span class="section-title">Conversation</span><span class="section-meta"><?= count($ticket['messages']) ?> messages</span></div>
    <div style="padding:1.1rem 1.25rem;display:flex;flex-direction:column;gap:.85rem;max-height:520px;overflow-y:auto">
      <?php foreach ($ticket['messages'] as $msg): ?>
        <div style="display:flex;<?= $msg['sender_type']==='admin'?'justify-content:flex-end':'' ?>">
          <div style="max-width:75%;padding:.85rem 1.1rem;font-size:13px;line-height:1.6;border-radius:var(--r);<?= $msg['sender_type']==='admin'?'background:var(--accent);color:#fff':'background:var(--surface2);color:var(--text);border:1px solid var(--border)' ?>">
            <?= nl2br(htmlspecialchars($msg['message'])) ?>
            <div style="font-size:10px;margin-top:6px;opacity:.6;text-align:<?= $msg['sender_type']==='admin'?'right':'left' ?>">
              <?= $msg['sender_type']==='admin'?'Support Team · ':htmlspecialchars($ticket['user_name']).' · ' ?><?= time_ago($msg['created_at']) ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (empty($ticket['messages'])): ?>
        <div style="text-align:center;color:var(--text3);padding:2rem">No messages yet.</div>
      <?php endif; ?>
    </div>
    <?php if ($ticket['status'] !== 'closed'): ?>
    <div id="reply-result" style="padding:0 1.25rem"></div>
    <div style="padding:.85rem 1.25rem;border-top:1px solid var(--border);display:flex;gap:.65rem">
      <textarea id="reply-msg" placeholder="Type your reply to the investor…" style="flex:1;padding:.75rem .9rem;border:1px solid var(--border);font-size:13px;font-family:'Inter',sans-serif;resize:none;height:80px;border-radius:var(--r);outline:none;color:var(--text);transition:border-color .15s" onfocus="this.style.borderColor='var(--accent)'" onblur="this.style.borderColor='var(--border)'"></textarea>
      <button class="btn btn-primary" style="align-self:flex-end" onclick="sendReply()"><?= svgIcon('send',13,'#fff') ?>Send</button>
    </div>
    <?php endif; ?>
  </div>

  <!-- Sidebar -->
  <div style="display:flex;flex-direction:column;gap:1rem">
    <div class="section">
      <div class="section-head"><span class="section-title">Ticket Info</span></div>
      <div style="padding:0 1.25rem">
        <?php foreach ([['Reference',$ticket['reference']],['Status',ucfirst(str_replace('_',' ',$ticket['status']))],['Priority',ucfirst($ticket['priority']??'medium')],['Created',fmt_datetime($ticket['created_at'])],['Last Updated',time_ago($ticket['updated_at'])]] as [$l,$v]): ?>
          <div style="display:flex;justify-content:space-between;padding:.6rem 0;border-bottom:1px solid var(--border);font-size:12.5px"><span style="color:var(--text3);font-weight:500"><?= $l ?></span><span style="font-weight:600;color:var(--text)"><?= htmlspecialchars($v) ?></span></div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="section">
      <div class="section-head"><span class="section-title">Investor</span></div>
      <div style="padding:1rem 1.25rem">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:.85rem">
          <div style="width:36px;height:36px;background:var(--accent);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#fff;flex-shrink:0"><?= strtoupper(substr($ticket['user_name'],0,2)) ?></div>
          <div><div style="font-size:13px;font-weight:500"><?= htmlspecialchars($ticket['user_name']) ?></div><div style="font-size:11.5px;color:var(--text3)"><?= htmlspecialchars($ticket['email']) ?></div></div>
        </div>
        <a href="/admin/users/<?= $ticket['user_id'] ?>" class="btn btn-outline btn-sm btn-block"><?= svgIcon('user',12) ?>View Profile</a>
      </div>
    </div>
  </div>
</div>

<script>
async function sendReply() {
  const msg  = document.getElementById('reply-msg').value.trim();
  if (!msg) { showFlash('Please enter a reply message.','err'); return; }
  const btn  = document.querySelector('[onclick="sendReply()"]');
  setLoading(btn, true);
  const data = await post('/admin/tickets/<?= $ticket['id'] ?>/reply', { message: msg });
  setLoading(btn, false);
  if (data.success) location.reload();
  else document.getElementById('reply-result').innerHTML = '<div class="alert alert-err" style="margin:.5rem 0">' + (data.error||'Failed.') + '</div>';
}
async function closeTicket(id) {
  if (!confirm('Close this ticket? The investor will be notified.')) return;
  const data = await post('/admin/tickets/'+id+'/close', {});
  if (data.success) location.reload();
  else showFlash(data.error||'Failed.','err');
}
</script>
