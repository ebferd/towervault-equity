<?php /* views/investor/support.php */ ?>
<style>
.supp-head{display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1rem;margin-bottom:1.25rem}
.supp-new-form{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:1.35rem;margin-bottom:1.5rem}
.supp-form-title{font-size:14px;font-weight:600;color:var(--text);margin-bottom:1rem}
.supp-card{background:var(--surface);border:1px solid var(--border);border-radius:14px;margin-bottom:1rem;overflow:hidden}
.supp-card-head{display:flex;align-items:flex-start;justify-content:space-between;padding:1rem 1.25rem;border-bottom:1px solid var(--border);gap:.75rem}
.supp-card-subject{font-size:14px;font-weight:600;color:var(--text)}
.supp-card-ref{font-size:11px;color:var(--text3);margin-top:.2rem}
.supp-messages{padding:.85rem 1.1rem;display:flex;flex-direction:column;gap:.7rem;max-height:320px;overflow-y:auto}
.supp-bubble{display:flex}
.supp-bubble.user{justify-content:flex-end}
.supp-bubble-inner{max-width:80%;padding:.65rem .9rem;font-size:13px;line-height:1.55;border-radius:12px}
.supp-bubble.user .supp-bubble-inner{background:var(--em-600);color:#fff;border-radius:12px 2px 12px 12px}
.supp-bubble.support .supp-bubble-inner{background:var(--mist-50);color:var(--text);border:1px solid var(--border);border-radius:2px 12px 12px 12px}
.supp-bubble-meta{font-size:10px;margin-top:4px;opacity:.65}
.supp-reply{padding:.75rem 1.1rem;border-top:1px solid var(--border);display:flex;gap:.6rem;align-items:flex-end}
.supp-reply textarea{flex:1;padding:.65rem .9rem;border:1px solid var(--border);border-radius:10px;font-size:13px;font-family:'Inter',sans-serif;resize:vertical;min-height:80px;outline:none;color:var(--text);background:var(--surface);transition:border-color .15s}
.supp-reply textarea:focus{border-color:var(--em-500)}
@media(max-width:500px){.supp-bubble-inner{max-width:95%}}
</style>

<div class="supp-head">
  <div>
    <h1 class="greet">Support</h1>
    <p class="greet-sub">Raise a ticket and our team will respond within 24 hours.</p>
  </div>
  <button class="qbtn primary" style="height:38px" onclick="toggleNewTicket()">
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    New Ticket
  </button>
</div>

<!-- New Ticket Form -->
<div id="new-ticket-form" style="display:none" class="supp-new-form">
  <div class="supp-form-title">New Support Ticket</div>
  <div id="nt-alert"></div>
  <form id="nt-form">
    <input type="hidden" name="_token" value="<?= csrf_token() ?>"/>
    <div class="fg"><label class="fl">Subject</label><input class="fi" name="subject" placeholder="Briefly describe your issue" required/></div>
    <div class="fg"><label class="fl">Message</label><textarea class="fta" name="message" placeholder="Provide as much detail as possible…" required></textarea></div>
    <div style="display:flex;gap:.65rem">
      <button type="submit" class="qbtn primary" id="nt-btn" style="height:40px"><span>Submit Ticket</span></button>
      <button type="button" class="qbtn outline" style="height:40px" onclick="toggleNewTicket()">Cancel</button>
    </div>
  </form>
</div>

<!-- Tickets list -->
<?php if (empty($tickets)): ?>
  <div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:4rem 2rem;text-align:center;color:var(--text3)">
    <?= svgIcon('headset',32,'var(--border)') ?>
    <p style="margin-top:.85rem;font-size:14px">No support tickets yet.<br><span style="font-size:12px">Click "New Ticket" to get help from our team.</span></p>
  </div>
<?php else: foreach ($tickets as $t): ?>
  <div class="supp-card">
    <div class="supp-card-head">
      <div>
        <div class="supp-card-subject"><?= htmlspecialchars($t['subject']) ?></div>
        <div class="supp-card-ref">Ref: <?= htmlspecialchars($t['reference']) ?> &middot; <?= fmt_date($t['updated_at']) ?></div>
      </div>
      <?= badge($t['status']) ?>
    </div>
    <div class="supp-messages">
      <?php foreach (($t['messages'] ?? []) as $msg): ?>
        <div class="supp-bubble <?= $msg['sender_type'] === 'user' ? 'user' : 'support' ?>">
          <div class="supp-bubble-inner">
            <?= nl2br(htmlspecialchars($msg['message'])) ?>
            <div class="supp-bubble-meta"><?= $msg['sender_type']==='support'?'Support &middot; ':'' ?><?= time_ago($msg['created_at']) ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <?php if ($t['status'] === 'open' || $t['status'] === 'in_progress'): ?>
      <div class="supp-reply">
        <textarea id="reply-<?= $t['id'] ?>" placeholder="Type your reply…"></textarea>
        <button class="qbtn primary" style="height:40px;flex-shrink:0" onclick="sendReply(<?= $t['id'] ?>)">
          <?= svgIcon('send',13,'#fff') ?> Send
        </button>
      </div>
    <?php endif; ?>
  </div>
<?php endforeach; endif; ?>

<script>
function toggleNewTicket() {
  const f = document.getElementById('new-ticket-form');
  f.style.display = f.style.display === 'none' ? '' : 'none';
}
document.getElementById('nt-form').addEventListener('submit', async e => {
  e.preventDefault();
  const btn  = document.getElementById('nt-btn');
  btn.disabled = true;
  btn.querySelector('span').innerHTML = '<span class="spinner" style="border-color:rgba(255,255,255,.4);border-top-color:#fff"></span> Submitting…';
  const fd   = new FormData(e.target);
  const data = await post('/investor/support/ticket', fd, true);
  btn.disabled = false;
  btn.querySelector('span').textContent = 'Submit Ticket';
  if (data.success) {
    document.getElementById('nt-alert').innerHTML = '<div class="alert-banner ok">Ticket submitted! Reference: <strong>' + data.reference + '</strong></div>';
    e.target.reset();
    setTimeout(() => location.reload(), 2500);
  } else {
    document.getElementById('nt-alert').innerHTML = '<div class="alert-banner err">' + (data.error || 'Failed to submit ticket.') + '</div>';
  }
});
async function sendReply(ticketId) {
  const ta  = document.getElementById('reply-' + ticketId);
  const msg = ta.value.trim();
  if (!msg) return;
  const data = await post('/investor/support/reply', { ticket_id: ticketId, message: msg });
  if (data.success) location.reload();
  else alert('Failed to send reply. Please try again.');
}
</script>
