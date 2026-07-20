<?php
/* Referrals — $user, $referrals, $commissions, $stats */
$refLink  = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'nexvest.com') . '/register?ref=' . ($user['referral_code'] ?? '');
$pName    = platform_setting('platform_name', 'NexVest');
$shareMsg = 'Join ' . $pName . ' and start earning passive income from real estate and index fund investments. Use my referral link: ' . $refLink;
$commRate = (int) platform_setting('referral_commission', 5);
?>
<style>
.ref-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:.65rem;margin-bottom:1rem}
@media(max-width:420px){.ref-stats{grid-template-columns:1fr 1fr}}
.ref-stat{background:var(--mist-50);border:1px solid var(--mist-100);border-radius:12px;padding:1rem 1.1rem;text-align:center}
.ref-stat-val{font-size:1.3rem;font-weight:700;color:var(--mist-900)}
.ref-stat-val.green{color:var(--em-600)}
.ref-stat-lbl{font-size:10.5px;color:var(--mist-400);margin-top:.25rem;text-transform:uppercase;letter-spacing:.07em;font-weight:600}

.r-card{background:#fff;border:1px solid var(--mist-200);border-radius:14px;overflow:hidden;margin-bottom:1rem}
.r-card-head{display:flex;align-items:center;gap:.65rem;padding:1rem 1.25rem;border-bottom:1px solid var(--mist-100)}
.r-card-icon{width:30px;height:30px;border-radius:8px;background:var(--mist-100);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.r-card-title{font-size:13.5px;font-weight:600;color:var(--mist-900)}
.r-card-body{padding:1.1rem 1.25rem}

.ref-link-box{display:flex;align-items:center;gap:.5rem;background:var(--mist-50);border:1px solid var(--mist-200);border-radius:9px;padding:.65rem .9rem;margin-bottom:.75rem;word-break:break-all}
.ref-link-text{font-size:11.5px;font-family:monospace;color:var(--mist-600);flex:1;min-width:0}
.share-btn-row{display:flex;gap:.5rem;flex-wrap:wrap}
.share-btn-row .qbtn{flex:1;min-width:100px;height:38px;font-size:12.5px;justify-content:center}

.how-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:.65rem}
@media(max-width:480px){.how-grid{grid-template-columns:1fr}}
.how-item{background:var(--mist-50);border:1px solid var(--mist-100);border-radius:12px;padding:1rem;text-align:center}
.how-num{width:28px;height:28px;border-radius:50%;background:var(--em-50);border:1.5px solid var(--em-100);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:var(--em-600);margin:0 auto .6rem}
.how-title{font-size:13px;font-weight:600;color:var(--mist-900);margin-bottom:.3rem}
.how-sub{font-size:11.5px;color:var(--mist-400);line-height:1.5}

.ref-row{display:flex;align-items:center;gap:.75rem;padding:.85rem 0;border-bottom:1px solid var(--mist-100);flex-wrap:wrap}
.ref-row:last-child{border-bottom:none}
.ref-av{width:34px;height:34px;border-radius:50%;background:var(--em-100);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:var(--em-700);flex-shrink:0}
.ref-name{font-size:13px;font-weight:600;color:var(--mist-900)}
.ref-date{font-size:11px;color:var(--mist-400);margin-top:.15rem}
.ref-comm{font-size:13px;font-weight:700;color:var(--em-600);white-space:nowrap;margin-left:auto}
.ref-comm.none{color:var(--mist-300)}

.comm-row{display:flex;align-items:center;justify-content:space-between;padding:.7rem 0;border-bottom:1px solid var(--mist-100)}
.comm-row:last-child{border-bottom:none}
.comm-desc{font-size:13px;font-weight:600;color:var(--mist-900)}
.comm-date{font-size:11px;color:var(--mist-400);margin-top:.15rem}
.comm-amt{font-size:14px;font-weight:700;color:var(--em-600)}
</style>

<div class="page-header">
  <div>
    <h1 class="greet">Referral Program</h1>
    <p class="greet-sub">Invite friends and earn <?= $commRate ?>% commission when they invest.</p>
  </div>
</div>

<!-- Stats -->
<div class="ref-stats">
  <div class="ref-stat">
    <div class="ref-stat-val"><?= (int)$stats['total'] ?></div>
    <div class="ref-stat-lbl">Total referred</div>
  </div>
  <div class="ref-stat">
    <div class="ref-stat-val"><?= (int)$stats['invested'] ?></div>
    <div class="ref-stat-lbl">Invested</div>
  </div>
  <div class="ref-stat">
    <div class="ref-stat-val green"><?= fmt_currency((float)$stats['total_comm']) ?></div>
    <div class="ref-stat-lbl">Total earned</div>
  </div>
</div>

<!-- Referral link -->
<div class="r-card">
  <div class="r-card-head">
    <div class="r-card-icon">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--mist-500)" stroke-width="1.8"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
    </div>
    <div class="r-card-title">Your referral link</div>
  </div>
  <div class="r-card-body">
    <div class="ref-link-box">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="var(--mist-400)" stroke-width="1.8" style="flex-shrink:0"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
      <span class="ref-link-text"><?= htmlspecialchars($refLink) ?></span>
    </div>
    <div class="share-btn-row">
      <button class="qbtn outline" data-copy="<?= htmlspecialchars($refLink) ?>">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
        Copy link
      </button>
      <a href="https://wa.me/?text=<?= urlencode($shareMsg) ?>" target="_blank" rel="noopener" class="qbtn outline">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 21l1.65-3.8a9 9 0 1 1 3.4 3.4z"/></svg>
        WhatsApp
      </a>
      <button onclick="if(navigator.share){navigator.share({title:'<?= htmlspecialchars(addslashes('Join '.$pName)) ?>',text:'<?= htmlspecialchars(addslashes($shareMsg)) ?>',url:'<?= htmlspecialchars($refLink) ?>'}).catch(()=>{})}else{copyText('<?= htmlspecialchars($refLink) ?>',this)}" class="qbtn primary">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.8"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
        Share
      </button>
    </div>
  </div>
</div>

<!-- How it works -->
<div class="r-card">
  <div class="r-card-head">
    <div class="r-card-icon">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--mist-500)" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    </div>
    <div class="r-card-title">How it works</div>
  </div>
  <div class="r-card-body">
    <div class="how-grid">
      <div class="how-item">
        <div class="how-num">1</div>
        <div class="how-title">Share your link</div>
        <div class="how-sub">Send your unique referral link to friends and contacts</div>
      </div>
      <div class="how-item">
        <div class="how-num">2</div>
        <div class="how-title">They sign up &amp; invest</div>
        <div class="how-sub">Your friend registers using your link and makes their first investment</div>
      </div>
      <div class="how-item">
        <div class="how-num">3</div>
        <div class="how-title">You earn <?= $commRate ?>%</div>
        <div class="how-sub">Commission is automatically credited to your wallet balance</div>
      </div>
    </div>
  </div>
</div>

<!-- Referrals list -->
<div class="r-card">
  <div class="r-card-head">
    <div class="r-card-icon">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--mist-500)" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
    </div>
    <div class="r-card-title">Your referrals</div>
    <span style="margin-left:auto;font-size:12px;color:var(--mist-400)"><?= count($referrals) ?> total</span>
  </div>
  <div class="r-card-body" style="padding-top:.25rem;padding-bottom:.25rem">
    <?php if (empty($referrals)): ?>
      <div style="text-align:center;padding:2.5rem 1rem">
        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="var(--mist-300)" stroke-width="1.4" style="margin:0 auto .75rem;display:block"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        <p style="font-size:13.5px;color:var(--mist-400)">No referrals yet. Share your link to get started.</p>
      </div>
    <?php else: ?>
      <?php foreach ($referrals as $r): ?>
      <div class="ref-row">
        <div class="ref-av"><?= strtoupper(substr($r['referred_name'],0,1)) ?></div>
        <div style="flex:1;min-width:0">
          <div class="ref-name"><?= htmlspecialchars($r['referred_name']) ?></div>
          <div class="ref-date"><?= htmlspecialchars($r['referred_email']) ?> &middot; Joined <?= fmt_date($r['joined']) ?></div>
        </div>
        <span class="badge <?= $r['status']==='registered' ? 'pending' : 'active' ?>">
          <?= $r['status']==='registered' ? 'Registered' : 'Invested' ?>
        </span>
        <span class="ref-comm <?= $r['commission_amount']>0 ? '' : 'none' ?>">
          <?= $r['commission_amount']>0 ? '+'.fmt_currency((float)$r['commission_amount']) : '—' ?>
        </span>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<?php if (!empty($commissions)): ?>
<!-- Recent commissions -->
<div class="r-card">
  <div class="r-card-head">
    <div class="r-card-icon">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--mist-500)" stroke-width="1.8"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
    </div>
    <div class="r-card-title">Recent commissions</div>
  </div>
  <div class="r-card-body" style="padding-top:.25rem;padding-bottom:.25rem">
    <?php foreach (array_slice($commissions, 0, 5) as $c): ?>
    <div class="comm-row">
      <div>
        <div class="comm-desc"><?= htmlspecialchars($c['description'] ?? 'Referral commission') ?></div>
        <div class="comm-date"><?= fmt_date($c['created_at']) ?></div>
      </div>
      <div class="comm-amt">+<?= fmt_currency((float)$c['amount']) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>
