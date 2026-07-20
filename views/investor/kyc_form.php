<div class="card" style="max-width:640px;margin:0 auto">
  <div class="card-head"><span class="card-head-title">Submit identity documents</span></div>
  <div style="padding:1.25rem">
    <div id="kyc-alert"></div>
    <form id="kyc-form" enctype="multipart/form-data">
      <input type="hidden" name="_token" value="<?= csrf_token() ?>"/>

      <div class="fg"><label class="fl">Full legal name</label><input class="fi" type="text" name="full_name" placeholder="As it appears on your ID" required/></div>
      <div class="fg"><label class="fl">Date of birth</label><input class="fi" type="date" name="date_of_birth" required/></div>
      <div class="fg">
        <label class="fl">ID document type</label>
        <select class="fi" name="id_type" required>
          <option value="">Select document type</option>
          <option value="passport">Passport</option>
          <option value="national_id">National ID card</option>
          <option value="drivers_license">Driver's license</option>
        </select>
      </div>

      <div class="kv-lbl" style="margin:1.25rem 0 .75rem;padding-bottom:.5rem;border-bottom:1px solid var(--mist-100)">Identity document</div>
      <div class="upload-grid">
        <label class="upload-box" id="zone-front">
          <input type="file" name="doc_front" accept=".jpg,.jpeg,.png,.pdf" required onchange="markZone('zone-front',this)"/>
          <svg class="upload-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
          <div class="upload-lbl" data-default="ID front side">ID front side</div>
          <div class="upload-hint">JPG, PNG or PDF &middot; Max 5MB</div>
        </label>
        <label class="upload-box" id="zone-back">
          <input type="file" name="doc_back" accept=".jpg,.jpeg,.png,.pdf" required onchange="markZone('zone-back',this)"/>
          <svg class="upload-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
          <div class="upload-lbl" data-default="ID back side">ID back side</div>
          <div class="upload-hint">JPG, PNG or PDF &middot; Max 5MB</div>
        </label>
      </div>

      <div class="kv-lbl" style="margin:1.25rem 0 .75rem;padding-bottom:.5rem;border-bottom:1px solid var(--mist-100)">Biometric &amp; address</div>
      <div class="upload-grid">
        <label class="upload-box" id="zone-selfie">
          <input type="file" name="doc_selfie" accept=".jpg,.jpeg,.png" required onchange="markZone('zone-selfie',this)"/>
          <svg class="upload-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
          <div class="upload-lbl" data-default="Selfie with ID">Selfie with ID</div>
          <div class="upload-hint">JPG or PNG &middot; Max 5MB</div>
        </label>
        <label class="upload-box" id="zone-addr">
          <input type="file" name="proof_of_address" accept=".jpg,.jpeg,.png,.pdf" required onchange="markZone('zone-addr',this)"/>
          <svg class="upload-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
          <div class="upload-lbl" data-default="Proof of address">Proof of address</div>
          <div class="upload-hint">Dated within 3 months</div>
        </label>
      </div>

      <div class="alert-banner" style="background:var(--blue-50);border:1px solid #BFDBFE;color:var(--blue-600)">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span>All documents are encrypted and stored securely in compliance with GDPR and international data protection standards.</span>
      </div>
      <button type="submit" class="qbtn primary" style="width:100%;height:44px" id="kyc-btn"><span>Submit for review</span></button>
    </form>
  </div>
</div>
<script>
function markZone(id, input) {
  const zone = document.getElementById(id);
  const lbl = zone.querySelector('.upload-lbl');
  if (input.files[0]) {
    zone.classList.add('has-file');
    const name = input.files[0].name;
    lbl.textContent = name.length > 24 ? name.slice(0, 22) + '…' : name;
  } else {
    zone.classList.remove('has-file');
    lbl.textContent = lbl.dataset.default;
  }
}
document.getElementById('kyc-form').addEventListener('submit', async e => {
  e.preventDefault();
  const btn = document.getElementById('kyc-btn');
  const label = btn.querySelector('span');
  btn.disabled = true;
  label.innerHTML = '<span class="spinner"></span> Submitting…';
  const fd = new FormData(e.target);
  const data = await post('/investor/kyc', fd, true);
  if (data.success) {
    window.location.href = data.redirect || '/investor/dashboard';
  } else {
    btn.disabled = false;
    label.textContent = 'Submit for review';
    document.getElementById('kyc-alert').innerHTML = '<div class="alert-banner err"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg><span>' + (data.error || 'Submission failed.') + '</span></div>';
  }
});
</script>
