<?php /* views/admin/kyc_detail.php */ ?>
<div class="page-header"><div><h1 class="page-title">KYC Review</h1><p class="page-sub">Review documents for <?= htmlspecialchars($kyc['user_name']) ?></p></div></div>
<div style="display:grid;grid-template-columns:340px 1fr;gap:1.5rem;align-items:start">
  <div class="section">
    <div class="section-head"><span class="section-title">Investor Details</span></div>
    <div style="padding:1rem 1.5rem">
      <?php foreach ([['Name',$kyc['user_name']],['Email',$kyc['email']],['ID Type',ucwords(str_replace('_',' ',$kyc['id_type']))],['Full Legal Name',$kyc['full_legal_name']],['Date of Birth',$kyc['date_of_birth']??''],['Submitted',fmt_datetime($kyc['submitted_at'])]] as [$l,$v]): ?>
        <div style="display:flex;justify-content:space-between;padding:.6rem 0;border-bottom:1px solid var(--border);font-size:12.5px"><span style="color:var(--text3);font-weight:500"><?= $l ?></span><span style="color:var(--text);font-weight:600"><?= htmlspecialchars($v) ?></span></div>
      <?php endforeach; ?>
    </div>
    <div style="padding:1rem 1.5rem">
      <div id="kyc-alert"></div>
      <div style="display:flex;flex-direction:column;gap:.5rem">
        <button class="btn btn-success btn-block" onclick="decide('approve')"><?= svgIcon('check',13,'#fff') ?>Approve KYC</button>
        <button class="btn btn-danger btn-block" onclick="document.getElementById('reject-box').style.display=''"><?= svgIcon('x',13,'#fff') ?>Reject KYC</button>
      </div>
      <div id="reject-box" style="display:none;margin-top:.85rem">
        <div class="fg"><label class="fl">Rejection Reason</label><textarea class="fta" id="reject-reason" placeholder="e.g. Document was blurry. Please resubmit a clearer photo." style="min-height:80px"></textarea></div>
        <button class="btn btn-danger btn-block" onclick="decide('reject')"><?= svgIcon('send',13,'#fff') ?>Send Rejection</button>
      </div>
    </div>
  </div>
  <div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
      <?php foreach (['doc_front'=>'ID Front','doc_back'=>'ID Back'] as $field => $label): ?>
        <div class="section">
          <div class="section-head"><span class="section-title"><?= $label ?></span></div>
          <div style="padding:1rem;text-align:center">
            <?php if (!empty($kyc[$field])): ?>
              <?php $ext = strtolower(pathinfo($kyc[$field], PATHINFO_EXTENSION)); ?>
              <?php if (in_array($ext,['jpg','jpeg','png','webp'])): ?>
                <img src="<?= htmlspecialchars(file_url($kyc[$field])) ?>" style="max-width:100%;max-height:200px;border-radius:var(--r)" alt="<?= $label ?>"/>
              <?php else: ?>
                <div style="padding:1.5rem"><?= svgIcon('doc',28,'var(--text3)') ?></div>
              <?php endif; ?>
              <a href="<?= htmlspecialchars(file_url($kyc[$field])) ?>" target="_blank" class="btn btn-outline btn-sm" style="margin-top:.75rem"><?= svgIcon('eye',12) ?>View Document</a>
            <?php else: ?><div style="padding:1.5rem;color:var(--text3);font-size:12px">Not uploaded</div><?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<script>
async function decide(action) {
  const reason = document.getElementById('reject-reason')?.value?.trim();
  if (action === 'reject' && !reason) { document.getElementById('kyc-alert').innerHTML = '<div class="alert alert-err">Please provide a rejection reason.</div>'; return; }
  const url  = action === 'approve' ? '/admin/kyc/<?= $kyc['id'] ?>/approve' : '/admin/kyc/<?= $kyc['id'] ?>/reject';
  const data = await post(url, action === 'reject' ? { reason } : {});
  if (data.success) { document.getElementById('kyc-alert').innerHTML = '<div class="alert alert-ok"><?= svgIcon('check',14,'var(--green)') ?> KYC '+action+'d successfully.</div>'; setTimeout(()=>history.back(),1800); }
  else document.getElementById('kyc-alert').innerHTML = '<div class="alert alert-err">' + (data.error||'Failed.') + '</div>';
}
</script>
