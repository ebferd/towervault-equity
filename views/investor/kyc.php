<?php
/* KYC — $user, $submission */
$kycStatus  = $user['kyc_status'] ?? 'not_started';
$subStatus  = $submission['status'] ?? null;
$isVerified = $kycStatus === 'verified';
$isPending  = $kycStatus === 'pending';
$isRejected = $subStatus === 'rejected';
?>

<style>
.kyc-layout{display:grid;grid-template-columns:1fr 320px;gap:1.25rem;align-items:start}
@media(max-width:900px){.kyc-layout{grid-template-columns:1fr}}
.frow{display:grid;grid-template-columns:1fr 1fr;gap:.75rem}
@media(max-width:560px){.frow{grid-template-columns:1fr}}

/* Status banner */
.kyc-banner{border-radius:14px;padding:1.25rem 1.4rem;display:flex;align-items:flex-start;gap:1rem;margin-bottom:1.5rem}
.kyc-banner.verified{background:#ecfdf5;border:1px solid #a7f3d0}
.kyc-banner.pending{background:#fffbeb;border:1px solid #fde68a}
.kyc-banner.rejected{background:#fef2f2;border:1px solid #fecaca}
.kyc-banner-icon{width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.kyc-banner.verified .kyc-banner-icon{background:#d1fae5}
.kyc-banner.pending .kyc-banner-icon{background:#fef3c7}
.kyc-banner.rejected .kyc-banner-icon{background:#fee2e2}
.kyc-banner-title{font-size:14px;font-weight:700;margin-bottom:.25rem}
.kyc-banner.verified .kyc-banner-title{color:#065f46}
.kyc-banner.pending .kyc-banner-title{color:#92400e}
.kyc-banner.rejected .kyc-banner-title{color:#991b1b}
.kyc-banner-sub{font-size:12.5px}
.kyc-banner.verified .kyc-banner-sub{color:#047857}
.kyc-banner.pending .kyc-banner-sub{color:#b45309}
.kyc-banner.rejected .kyc-banner-sub{color:#b91c1c}

/* Steps sidebar */
.kyc-steps-card{background:#fff;border:1px solid var(--mist-100);border-radius:16px;padding:1.25rem 1.4rem}
.kyc-step{display:flex;gap:.85rem;padding:.75rem 0;border-bottom:1px solid var(--mist-100)}
.kyc-step:last-child{border-bottom:none;padding-bottom:0}
.kyc-step-num{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0;margin-top:1px}
.kyc-step-num.done{background:#ecfdf5;color:#059669}
.kyc-step-num.active{background:var(--navy);color:#fff}
.kyc-step-num.idle{background:var(--mist-100);color:var(--mist-400)}
.kyc-step-title{font-size:13px;font-weight:600;color:var(--mist-900);margin-bottom:.2rem}
.kyc-step-sub{font-size:11.5px;color:var(--mist-400)}

/* Upload zones */
.upload-grid{display:grid;grid-template-columns:1fr 1fr;gap:.85rem;margin-top:.75rem}
@media(max-width:480px){.upload-grid{grid-template-columns:1fr}}
.upload-zone{border:1.5px dashed var(--mist-200);border-radius:12px;padding:1.25rem 1rem;text-align:center;cursor:pointer;transition:border-color .18s,background .18s;position:relative}
.upload-zone:hover{border-color:var(--em-400);background:var(--em-50)}
.upload-zone.has-file{border-color:var(--em-500);background:#f0fdf4;border-style:solid}
.upload-zone input{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}
.upload-icon{width:36px;height:36px;background:var(--mist-100);border-radius:9px;display:flex;align-items:center;justify-content:center;margin:0 auto .65rem}
.upload-zone.has-file .upload-icon{background:#dcfce7}
.upload-lbl{font-size:12px;font-weight:600;color:var(--mist-700);margin-bottom:.2rem}
.upload-zone.has-file .upload-lbl{color:#065f46}
.upload-sub{font-size:11px;color:var(--mist-400)}
.upload-zone.has-file .upload-sub{color:#16a34a}
.upload-fname{font-size:10.5px;color:#059669;margin-top:.35rem;font-weight:600;word-break:break-all}
</style>

<div class="page-header">
  <div>
    <h1 class="greet">Identity Verification</h1>
    <p class="greet-sub">Verify your identity to unlock full platform access and higher investment limits.</p>
  </div>
</div>

<?php if ($isVerified): ?>
<div class="kyc-banner verified">
  <div class="kyc-banner-icon">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
  </div>
  <div>
    <div class="kyc-banner-title">Identity Verified</div>
    <div class="kyc-banner-sub">Your account has full access. Thank you for completing verification.</div>
  </div>
</div>

<?php elseif ($isPending): ?>
<div class="kyc-banner pending">
  <div class="kyc-banner-icon">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
  </div>
  <div>
    <div class="kyc-banner-title">Verification Under Review</div>
    <div class="kyc-banner-sub">Your documents were submitted and are being reviewed. This typically takes 1–2 business days.</div>
  </div>
</div>

<?php elseif ($isRejected): ?>
<div class="kyc-banner rejected">
  <div class="kyc-banner-icon">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
  </div>
  <div>
    <div class="kyc-banner-title">Verification Rejected</div>
    <div class="kyc-banner-sub"><?= htmlspecialchars($submission['rejection_reason'] ?? 'Your documents could not be verified. Please resubmit with clearer images.') ?></div>
  </div>
</div>
<?php endif; ?>

<div class="kyc-layout">
  <!-- LEFT: Form -->
  <div>
    <?php if ($isVerified): ?>
    <!-- Verified state summary -->
    <div class="card">
      <div class="card-head"><span class="card-head-title">Verified information</span></div>
      <div style="padding:1rem 1.4rem">
        <div class="kv-grid" style="grid-template-columns:1fr 1fr">
          <div><div class="kv-lbl">Full legal name</div><div class="kv-val"><?= htmlspecialchars($submission['full_legal_name'] ?? '—') ?></div></div>
          <div><div class="kv-lbl">Date of birth</div><div class="kv-val"><?= !empty($submission['date_of_birth']) ? date('M j, Y', strtotime($submission['date_of_birth'])) : '—' ?></div></div>
          <div><div class="kv-lbl">Document type</div><div class="kv-val"><?= htmlspecialchars(str_replace('_',' ', ucfirst($submission['id_type'] ?? '—'))) ?></div></div>
          <div><div class="kv-lbl">Submitted</div><div class="kv-val"><?= !empty($submission['created_at']) ? date('M j, Y', strtotime($submission['created_at'])) : '—' ?></div></div>
        </div>
      </div>
    </div>

    <?php else: ?>
    <!-- Submission form -->
    <div class="card">
      <div class="card-head"><span class="card-head-title">Submit your documents</span></div>
      <div id="kyc-alert" style="padding:0 1.4rem"></div>
      <form id="kyc-form" style="padding:1rem 1.4rem" enctype="multipart/form-data">
        <input type="hidden" name="_token" value="<?= csrf_token() ?>"/>

        <!-- Step 1: Personal info -->
        <div style="margin-bottom:1.5rem">
          <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:1rem">
            <div style="width:24px;height:24px;background:var(--navy);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;flex-shrink:0">1</div>
            <span style="font-size:13px;font-weight:700;color:var(--mist-900)">Personal information</span>
          </div>
          <div class="frow">
            <div class="fg">
              <label class="fl">Full legal name <span style="color:#dc2626">*</span></label>
              <input class="fi" type="text" name="full_name" placeholder="As it appears on your ID" required
                value="<?= htmlspecialchars(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''))) ?>"/>
            </div>
            <div class="fg">
              <label class="fl">Date of birth <span style="color:#dc2626">*</span></label>
              <input class="fi" type="date" name="date_of_birth" required
                max="<?= date('Y-m-d', strtotime('-18 years')) ?>"
                value="<?= htmlspecialchars($user['date_of_birth'] ?? '') ?>"/>
            </div>
          </div>
          <div class="fg">
            <label class="fl">ID document type <span style="color:#dc2626">*</span></label>
            <select class="fi" name="id_type" required>
              <option value="">Select document type</option>
              <option value="passport" <?= ($submission['id_type']??'')==='passport'?'selected':'' ?>>Passport</option>
              <option value="national_id" <?= ($submission['id_type']??'')==='national_id'?'selected':'' ?>>National ID Card</option>
              <option value="drivers_license" <?= ($submission['id_type']??'')==='drivers_license'?'selected':'' ?>>Driver's License</option>
            </select>
          </div>
        </div>

        <!-- Step 2: Documents -->
        <div style="margin-bottom:1.5rem">
          <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:1rem">
            <div style="width:24px;height:24px;background:var(--navy);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;flex-shrink:0">2</div>
            <span style="font-size:13px;font-weight:700;color:var(--mist-900)">Upload documents</span>
          </div>
          <div class="upload-grid">
            <?php
            $uploadFields = [
              'doc_front' => ['ID Front', 'Front side of your document'],
              'doc_back'  => ['ID Back',  'Back side of your document'],
            ];
            foreach ($uploadFields as $name => [$label, $hint]): ?>
            <div class="upload-zone" id="zone-<?= $name ?>">
              <input type="file" name="<?= $name ?>" accept=".jpg,.jpeg,.png,.pdf" onchange="markFile(this,'<?= $name ?>')"/>
              <div class="upload-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--mist-400)" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="12" y2="12"/><line x1="15" y1="15" x2="12" y2="12"/></svg>
              </div>
              <div class="upload-lbl"><?= $label ?></div>
              <div class="upload-sub"><?= $hint ?></div>
              <div class="upload-fname" id="fn-<?= $name ?>"></div>
            </div>
            <?php endforeach; ?>
          </div>
          <div style="margin-top:.65rem;font-size:11.5px;color:var(--mist-400)">
            Accepted formats: JPG, PNG, PDF &middot; Max 5 MB per file &middot; All documents must be clearly readable
          </div>
        </div>

        <button type="submit" class="qbtn primary" style="height:44px;width:100%;font-size:14px" id="kyc-submit-btn">
          <span id="kyc-submit-label">Submit for verification</span>
        </button>
      </form>
    </div>
    <?php endif; ?>
  </div>

  <!-- RIGHT: Info sidebar -->
  <div>
    <!-- Steps checklist -->
    <div class="kyc-steps-card" style="margin-bottom:1rem">
      <div class="kv-lbl" style="margin-bottom:.85rem">Verification steps</div>
      <?php
      $steps = [
        ['Personal info', 'Full name and date of birth', $kycStatus !== 'not_started'],
        ['Upload documents', 'Government-issued ID — front &amp; back', !empty($submission)],
        ['Under review', 'Team reviews your submission', in_array($kycStatus,['pending','verified'])],
        ['Verified', 'Full platform access unlocked', $kycStatus === 'verified'],
      ];
      foreach ($steps as $i => [$stepTitle, $sub, $done]):
        $isActive = !$done && ($i === 0 || $steps[$i-1][2]);
        $numClass = $done ? 'done' : ($isActive ? 'active' : 'idle');
      ?>
      <div class="kyc-step">
        <div class="kyc-step-num <?= $numClass ?>">
          <?php if ($done): ?>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          <?php else: ?>
          <?= $i + 1 ?>
          <?php endif; ?>
        </div>
        <div>
          <div class="kyc-step-title" style="<?= $done ? 'color:var(--em-600)' : '' ?>"><?= $stepTitle ?></div>
          <div class="kyc-step-sub"><?= $sub ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Tips -->
    <div class="card" style="padding:1.1rem 1.4rem">
      <div class="kv-lbl" style="margin-bottom:.85rem">Tips for faster approval</div>
      <?php foreach ([
        ['Ensure documents are not expired', 'We accept documents valid within the last 5 years.'],
        ['Clear, well-lit photos', 'No blur, glare, or cut-off edges.'],
        ['Selfie must show your face clearly', 'Remove sunglasses. Hold the ID beside your face.'],
        ['Address proof must be recent', 'Dated within the last 3 months.'],
      ] as [$tip, $detail]): ?>
      <div style="display:flex;gap:.65rem;margin-bottom:.75rem;align-items:flex-start">
        <div style="width:18px;height:18px;background:var(--em-50);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px">
          <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="var(--em-600)" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <div>
          <div style="font-size:12.5px;font-weight:600;color:var(--mist-800);margin-bottom:.15rem"><?= $tip ?></div>
          <div style="font-size:11.5px;color:var(--mist-400)"><?= $detail ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<script>
function markFile(input, name) {
  const zone = document.getElementById('zone-' + name);
  const fn   = document.getElementById('fn-' + name);
  if (input.files && input.files[0]) {
    zone.classList.add('has-file');
    fn.textContent = input.files[0].name;
    const icon = zone.querySelector('.upload-icon svg');
    if (icon) icon.setAttribute('stroke', 'var(--em-600)');
  }
}

document.getElementById('kyc-form')?.addEventListener('submit', async e => {
  e.preventDefault();
  const btn   = document.getElementById('kyc-submit-btn');
  const label = document.getElementById('kyc-submit-label');
  btn.disabled = true;
  label.innerHTML = '<span class="spinner" style="border-color:rgba(255,255,255,.4);border-top-color:#fff"></span> Submitting…';
  const data = await post('/investor/kyc', new FormData(e.target), true);
  btn.disabled = false;
  label.textContent = 'Submit for verification';
  if (data.success) {
    window.location.href = data.redirect || '/investor/dashboard';
  } else {
    document.getElementById('kyc-alert').innerHTML =
      `<div class="alert-banner err" style="margin-bottom:.75rem">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span>${data.error}</span>
      </div>`;
    window.scrollTo({top:0,behavior:'smooth'});
  }
});
</script>
