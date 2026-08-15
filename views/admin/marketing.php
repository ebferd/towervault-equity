<div class="page-head">
  <div>
    <h1 class="page-title">Marketing</h1>
    <p class="page-sub">Send a designed outreach email to potential clients. You write the subject and message — we handle the layout.</p>
  </div>
</div>

<div id="mk-alert"></div>

<div class="mk-grid">
  <!-- ─── Compose ─── -->
  <div>
    <div class="section">
      <div class="section-head"><span class="section-title">Message</span><span class="section-meta">You control the wording; the design stays consistent</span></div>
      <div class="section-body">
        <div class="fg">
          <label class="fl">Subject line <span style="color:#C0392B">*</span></label>
          <input class="fi" id="mk-subject" maxlength="255" placeholder="e.g. A new investment opportunity is now open"/>
        </div>
        <div class="fg">
          <label class="fl">Headline <span class="fl-opt">(optional — large text above your message)</span></label>
          <input class="fi" id="mk-headline" maxlength="255" placeholder="e.g. Invest in assets you can actually see"/>
        </div>
        <div class="fg">
          <label class="fl">Message body <span style="color:#C0392B">*</span> <span class="fl-opt">(plain text — line breaks are preserved)</span></label>
          <textarea class="fi" id="mk-body" rows="9" style="resize:vertical" placeholder="Write your message here. Introduce your company, the opportunity, and why it's worth a look."></textarea>
        </div>
      </div>
    </div>

    <div class="section" style="margin-top:1.25rem">
      <div class="section-head"><span class="section-title">Featured opportunities</span><span class="section-meta">Tick any you want to showcase — each renders a live card that matches the dashboard</span></div>
      <div class="section-body">
        <?php if (empty($investments)): ?>
          <p class="fl-opt">No opportunities yet. Create one under Investments to feature it here.</p>
        <?php else: ?>
          <div class="mk-opps">
            <?php foreach ($investments as $inv): ?>
              <label class="mk-opp">
                <input type="checkbox" class="mk-opp-cb" value="<?= (int)$inv['id'] ?>"/>
                <span class="mk-opp-body">
                  <span class="mk-opp-name"><?= htmlspecialchars($inv['name']) ?></span>
                  <span class="mk-opp-meta">
                    <?= htmlspecialchars((string)$inv['roi']) ?>% ROI ·
                    <?= $inv['type'] === 'real_estate' ? 'Real Estate' : 'Index Fund' ?><?= $inv['status'] !== 'active' ? ' · ' . htmlspecialchars($inv['status']) : '' ?>
                  </span>
                </span>
              </label>
            <?php endforeach; ?>
          </div>
          <p class="fl-opt" style="margin-top:.6rem">Each card pulls its ROI, minimum, duration, payout and funding bar straight from the opportunity — always up to date. Leave all unticked to send with no card.</p>
        <?php endif; ?>
        <div class="fg" style="margin-top:1rem;max-width:340px">
          <label class="fl">Button label <span class="fl-opt">(optional)</span></label>
          <input class="fi" id="mk-cta-label" maxlength="120" placeholder="Visit website"/>
          <p class="fl-opt" style="margin-top:.4rem">The button always links to your website — you never need to paste a link.</p>
        </div>
      </div>
    </div>

    <div class="section" style="margin-top:1.25rem">
      <div class="section-head"><span class="section-title">Recipients</span><span class="section-meta">Potential clients — not existing users</span></div>
      <div class="section-body">
        <div class="fg">
          <label class="fl">Email addresses <span style="color:#C0392B">*</span> <span class="fl-opt">(paste as many as you like — separated by comma, space, or new line)</span></label>
          <textarea class="fi" id="mk-recipients" rows="7" style="resize:vertical;font-family:monospace;font-size:12.5px" placeholder="jane@example.com&#10;john@example.com, mark@example.com"></textarea>
          <p class="fl-opt" style="margin-top:.4rem"><span id="mk-count">0</span> valid address(es) detected. Anyone who has unsubscribed is skipped automatically. Max 500 per send.</p>
        </div>
      </div>
    </div>
  </div>

  <!-- ─── Send panel ─── -->
  <div>
    <div class="section mk-sticky">
      <div class="section-head"><span class="section-title">Test &amp; send</span></div>
      <div class="section-body">
        <div class="fg">
          <label class="fl">Send a test to yourself</label>
          <input class="fi" id="mk-test-email" value="<?= htmlspecialchars($adminEmail) ?>" placeholder="you@example.com"/>
        </div>
        <button type="button" class="btn btn-ghost" id="mk-test-btn" style="width:100%;justify-content:center"><?= svgIcon('send',14) ?>Send test email</button>

        <div style="height:1px;background:var(--line,#E5E7EB);margin:1.25rem 0"></div>

        <button type="button" class="btn btn-primary btn-lg" id="mk-send-btn" style="width:100%;justify-content:center"><?= svgIcon('send',14,'#fff') ?>Send campaign</button>
        <p class="fl-opt" style="margin-top:.6rem;text-align:center">This sends the email to everyone in the recipients box. Always send a test to yourself first.</p>

        <div class="mk-stat"><span>On the suppression list</span><b><?= (int)$unsubCount ?> unsubscribed</b></div>
      </div>
    </div>
  </div>
</div>

<?php if (!empty($recent)): ?>
<div class="section" style="margin-top:1.5rem">
  <div class="section-head"><span class="section-title">Recent campaigns</span></div>
  <div class="section-body" style="padding:0">
    <table class="tbl" style="width:100%;border-collapse:collapse">
      <thead><tr>
        <th style="text-align:left">Subject</th><th>Recipients</th><th>Sent</th><th>Failed</th><th style="text-align:right">Date</th>
      </tr></thead>
      <tbody>
        <?php foreach ($recent as $c): ?>
          <tr>
            <td style="text-align:left"><?= htmlspecialchars($c['subject']) ?></td>
            <td style="text-align:center"><?= (int)$c['recipient_count'] ?></td>
            <td style="text-align:center;color:#0f7a4a;font-weight:600"><?= (int)$c['sent_count'] ?></td>
            <td style="text-align:center;color:<?= (int)$c['failed_count'] ? '#C0392B' : '#9CA3AF' ?>"><?= (int)$c['failed_count'] ?></td>
            <td style="text-align:right;color:#9CA3AF"><?= date('M j, Y g:i A', strtotime($c['created_at'])) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<style>
  .mk-grid{display:grid;grid-template-columns:1fr 340px;gap:1.25rem;align-items:start}
  .mk-sticky{position:sticky;top:1rem}
  .mk-stat{display:flex;justify-content:space-between;align-items:center;font-size:12.5px;color:#6B7280;margin-top:1.25rem;padding-top:1rem;border-top:1px solid #F0F2F7}
  .mk-stat b{color:#111827;font-weight:600}
  .tbl th{font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:#9CA3AF;font-weight:600;padding:12px 16px;border-bottom:1px solid #F0F2F7}
  .tbl td{font-size:13px;color:#374151;padding:13px 16px;border-bottom:1px solid #F5F6F8}
  .tbl tbody tr:last-child td{border-bottom:none}
  .mk-opps{display:grid;grid-template-columns:1fr 1fr;gap:.6rem}
  .mk-opp{display:flex;gap:.65rem;align-items:flex-start;border:1px solid #E5E7EB;border-radius:10px;padding:.7rem .8rem;cursor:pointer;transition:border-color .15s,background .15s}
  .mk-opp:hover{border-color:#C7CDD6}
  .mk-opp input{margin-top:2px;accent-color:#1E3A5F;width:16px;height:16px;flex-shrink:0;cursor:pointer}
  .mk-opp:has(input:checked){border-color:#1E3A5F;background:#F5F8FC}
  .mk-opp-body{display:flex;flex-direction:column;gap:2px;min-width:0}
  .mk-opp-name{font-size:13.5px;font-weight:600;color:#111827;line-height:1.3}
  .mk-opp-meta{font-size:11.5px;color:#9CA3AF}
  @media(max-width:900px){.mk-grid{grid-template-columns:1fr}.mk-sticky{position:static}.mk-opps{grid-template-columns:1fr}}
</style>

<script>
const $ = id => document.getElementById(id);

// live recipient counter
function countRecipients(){
  const raw = $('mk-recipients').value.trim();
  if(!raw){ $('mk-count').textContent = '0'; return 0; }
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  const set = new Set(raw.split(/[\s,;]+/).map(s=>s.trim().toLowerCase()).filter(e=>e && re.test(e)));
  $('mk-count').textContent = set.size;
  return set.size;
}
$('mk-recipients').addEventListener('input', countRecipients);

function payload(){
  const ids = Array.from(document.querySelectorAll('.mk-opp-cb:checked')).map(c=>c.value);
  return {
    subject:   $('mk-subject').value.trim(),
    headline:  $('mk-headline').value.trim(),
    body:      $('mk-body').value.trim(),
    featured_ids: ids,
    cta_label: $('mk-cta-label').value.trim(),
  };
}
function validate(){
  if(!$('mk-subject').value.trim() || !$('mk-body').value.trim()){
    $('mk-alert').innerHTML = '<div class="alert alert-err">Subject and message body are required.</div>';
    window.scrollTo({top:0,behavior:'smooth'});
    return false;
  }
  return true;
}

// send test
$('mk-test-btn').addEventListener('click', async function(){
  if(!validate()) return;
  const email = $('mk-test-email').value.trim();
  if(!email){ $('mk-alert').innerHTML = '<div class="alert alert-err">Enter a test email address.</div>'; return; }
  setLoading(this, true, 'Sending…');
  const data = await post('/admin/marketing/test', { ...payload(), test_email: email });
  setLoading(this, false);
  $('mk-alert').innerHTML = data.success
    ? '<div class="alert alert-ok">' + data.message + '</div>'
    : '<div class="alert alert-err">' + (data.error || 'Test failed.') + '</div>';
  window.scrollTo({top:0,behavior:'smooth'});
});

// send campaign
$('mk-send-btn').addEventListener('click', async function(){
  if(!validate()) return;
  const n = countRecipients();
  if(n === 0){ $('mk-alert').innerHTML = '<div class="alert alert-err">Add at least one valid recipient email.</div>'; window.scrollTo({top:0,behavior:'smooth'}); return; }
  if(!confirm('Send this campaign to ' + n + ' recipient(s)? Make sure you have sent yourself a test first.')) return;
  setLoading(this, true, 'Sending…');
  const data = await post('/admin/marketing/send', { ...payload(), recipients: $('mk-recipients').value });
  setLoading(this, false);
  if(data.success){
    $('mk-alert').innerHTML = '<div class="alert alert-ok">' + data.message + '</div>';
    setTimeout(()=>location.reload(), 1400);
  } else {
    $('mk-alert').innerHTML = '<div class="alert alert-err">' + (data.error || 'Send failed.') + '</div>';
  }
  window.scrollTo({top:0,behavior:'smooth'});
});
</script>
