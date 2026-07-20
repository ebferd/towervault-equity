<?php /* views/admin/investment_form.php */
$inv   = $investment ?? null;
$isNew = $inv === null;
$type  = $inv['type'] ?? 'real_estate';
?>
<div class="page-header">
  <h1 class="page-title"><?= $isNew ? 'New Investment' : 'Edit Investment' ?></h1>
  <p class="page-sub"><?= $isNew ? 'Create a new real estate or index fund product.' : 'Update investment details.' ?></p>
</div>

<div id="inv-alert"></div>

<form id="inv-form" enctype="multipart/form-data" method="POST" action="<?= $isNew ? '/admin/investments/create' : '/admin/investments/'.$inv['id'] ?>">
  <input type="hidden" name="_token" value="<?= csrf_token() ?>"/>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem">

    <!-- CORE -->
    <div class="section">
      <div class="section-head"><span class="section-title">Core Details</span></div>
      <div class="section-body">
        <div class="fg"><label class="fl">Investment Name</label><input class="fi" name="name" value="<?= htmlspecialchars($inv['name']??'') ?>" required placeholder="e.g. Harbour View Residences"/></div>
        <div class="fg">
          <label class="fl">Type</label>
          <select class="fsel" name="type" id="inv-type" onchange="toggleType()">
            <option value="real_estate" <?= $type==='real_estate'?'selected':'' ?>>Real Estate</option>
            <option value="index_fund"  <?= $type==='index_fund' ?'selected':'' ?>>Index Fund</option>
          </select>
        </div>
        <div class="fg">
          <label class="fl">Status</label>
          <select class="fsel" name="status">
            <?php foreach (['active'=>'Active','funded'=>'Fully Funded','coming_soon'=>'Coming Soon','closed'=>'Closed'] as $v=>$l): ?>
              <option value="<?= $v ?>" <?= ($inv['status']??'active')===$v?'selected':'' ?>><?= $l ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="fg"><label class="fl">Short Description</label><input class="fi" name="short_desc" value="<?= htmlspecialchars($inv['short_desc']??'') ?>" placeholder="One-line summary for investment cards"/></div>
        <div class="fg"><label class="fl">Full Description</label><textarea class="fta" name="description" style="min-height:110px" placeholder="Detailed investment overview…"><?= htmlspecialchars($inv['description']??'') ?></textarea></div>
      </div>
    </div>

    <!-- FINANCIAL -->
    <div style="display:flex;flex-direction:column;gap:1.5rem">
      <div class="section">
        <div class="section-head"><span class="section-title">Financial Terms</span></div>
        <div class="section-body">
          <div class="frow">
            <div class="fg"><label class="fl">Total ROI (%) <span class="fl-opt">— total return over the full duration, not per year</span></label><input class="fi" type="number" name="roi" value="<?= htmlspecialchars($inv['roi']??'') ?>" min="0" max="1000" step="0.01" required placeholder="e.g. 30"/></div>
            <div class="fg"><label class="fl">Payout Frequency</label>
              <select class="fsel" name="payout_frequency">
                <?php foreach (['daily'=>'Daily','weekly'=>'Weekly','monthly'=>'Monthly','quarterly'=>'Quarterly','semi_annual'=>'Semi-Annual','at_maturity'=>'At Maturity'] as $v=>$l): ?>
                  <option value="<?= $v ?>" <?= ($inv['payout_frequency']??'monthly')===$v?'selected':'' ?>><?= $l ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="fg">
            <label class="fl">Duration</label>
            <div style="display:flex;gap:.65rem">
              <input class="fi" type="number" name="duration_value" value="<?= htmlspecialchars($inv['duration_value']??'') ?>" min="1" required placeholder="e.g. 36" style="flex:1"/>
              <select class="fsel" name="duration_unit" style="width:120px">
                <option value="months" <?= ($inv['duration_unit']??'months')==='months'?'selected':'' ?>>Months</option>
                <option value="years"  <?= ($inv['duration_unit']??'')==='years' ?'selected':'' ?>>Years</option>
                <option value="weeks"  <?= ($inv['duration_unit']??'')==='weeks' ?'selected':'' ?>>Weeks</option>
                <option value="days"   <?= ($inv['duration_unit']??'')==='days'  ?'selected':'' ?>>Days</option>
              </select>
            </div>
          </div>
          <div class="frow">
            <div class="fg"><label class="fl">Min. Investment (<?= htmlspecialchars(platform_setting('platform_symbol','$')) ?>)</label><input class="fi" type="number" name="min_investment" value="<?= htmlspecialchars($inv['min_investment']??'1000') ?>" min="0" step="1" required/></div>
            <div class="fg"><label class="fl">Max. Investment <span class="fl-opt">(leave blank for unlimited)</span></label><input class="fi" type="number" name="max_investment" value="<?= htmlspecialchars($inv['max_investment']??'') ?>" min="0" step="1"/></div>
          </div>
          <div class="fg"><label class="fl">Funding Target <span class="fl-opt">(optional)</span></label><input class="fi" type="number" name="funding_target" value="<?= htmlspecialchars($inv['funding_target']??'') ?>" min="0" step="1000"/></div>
        </div>
      </div>

      <!-- VISIBILITY -->
      <div class="section">
        <div class="section-head"><span class="section-title">Visibility &amp; Notifications</span></div>
        <div style="padding:0 1.5rem">
          <?php foreach ([['is_featured','Featured','"Featured" badge on investment card'],['is_verified','Verified Badge','"Verified" trust badge'],['notify_on_launch','Notify on Launch','Email all active investors']] as [$key,$l,$sub]):
            $checked = !empty($inv[$key]);
          ?>
            <div class="toggle-row">
              <div><div class="tr-label"><?= $l ?></div><div class="tr-sub"><?= $sub ?></div></div>
              <label class="toggle"><input type="checkbox" name="<?= $key ?>" value="1" <?= $checked?'checked':'' ?>/><div class="t-track"></div><div class="t-thumb"></div></label>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- REAL ESTATE FIELDS -->
  <div id="re-fields" style="margin-top:1.5rem;<?= $type!=='real_estate'?'display:none':'' ?>">
    <div class="section">
      <div class="section-head"><span class="section-title">Real Estate Details</span></div>
      <div class="section-body">
        <div class="frow">
          <div class="fg"><label class="fl">Property Type</label><input class="fi" name="property_type" value="<?= htmlspecialchars($inv['property_type']??'') ?>" placeholder="e.g. Residential, Commercial, Industrial"/></div>
          <div class="fg"><label class="fl">Year Built</label><input class="fi" type="number" name="year_built" value="<?= htmlspecialchars($inv['year_built']??'') ?>" placeholder="e.g. 2020"/></div>
        </div>
        <div class="fg"><label class="fl">Street Address</label><input class="fi" name="street_address" value="<?= htmlspecialchars($inv['street_address']??'') ?>" placeholder="Full street address"/></div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.75rem">
          <div class="fg"><label class="fl">City</label><input class="fi" name="city" value="<?= htmlspecialchars($inv['city']??'') ?>"/></div>
          <div class="fg"><label class="fl">State / Region</label><input class="fi" name="state_region" value="<?= htmlspecialchars($inv['state_region']??'') ?>"/></div>
          <div class="fg"><label class="fl">Country</label><input class="fi" name="country" value="<?= htmlspecialchars($inv['country']??'') ?>"/></div>
        </div>
        <div class="fg"><label class="fl">Google Maps Link</label><input class="fi" type="url" name="maps_link" value="<?= htmlspecialchars($inv['maps_link']??'') ?>" placeholder="https://goo.gl/maps/…"/></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem">
          <div class="fg"><label class="fl">Property Size</label><input class="fi" name="property_size" value="<?= htmlspecialchars($inv['property_size']??'') ?>" placeholder="e.g. 1,200 sqm"/></div>
          <div class="fg"><label class="fl">Total Units</label><input class="fi" name="total_units" value="<?= htmlspecialchars($inv['total_units']??'') ?>" placeholder="e.g. 48"/></div>
        </div>
      </div>
    </div>
  </div>

  <!-- INDEX FUND FIELDS -->
  <div id="if-fields" style="margin-top:1.5rem;<?= $type!=='index_fund'?'display:none':'' ?>">
    <div class="section">
      <div class="section-head"><span class="section-title">Index Fund Details</span></div>
      <div class="section-body">
        <div class="frow">
          <div class="fg"><label class="fl">Ticker Symbol</label><input class="fi" name="ticker" value="<?= htmlspecialchars($inv['ticker']??'') ?>" placeholder="e.g. SPY"/></div>
          <div class="fg"><label class="fl">Fund Category</label><input class="fi" name="fund_category" value="<?= htmlspecialchars($inv['fund_category']??'') ?>" placeholder="e.g. Global Equities"/></div>
        </div>
        <div class="frow">
          <div class="fg"><label class="fl">Risk Level</label>
            <select class="fsel" name="risk_level">
              <option value="">Select risk level</option>
              <?php foreach (['low'=>'Low','low_medium'=>'Low–Medium','medium'=>'Medium','medium_high'=>'Medium–High','high'=>'High'] as $v=>$l): ?>
                <option value="<?= $v ?>" <?= ($inv['risk_level']??'')===$v?'selected':'' ?>><?= $l ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="fg"><label class="fl">Management Fee (%)</label><input class="fi" type="number" name="management_fee" value="<?= htmlspecialchars($inv['management_fee']??'') ?>" step="0.01" min="0" placeholder="e.g. 0.5"/></div>
        </div>
        <div class="fg"><label class="fl">Benchmark Index</label><input class="fi" name="benchmark" value="<?= htmlspecialchars($inv['benchmark']??'') ?>" placeholder="e.g. S&amp;P 500"/></div>
        <div class="fg">
          <label class="fl">Top Holdings <span class="fl-opt">(one per line)</span></label>
          <textarea class="fta" name="fund_holdings" placeholder="Apple Inc&#10;Microsoft Corp&#10;Amazon.com Inc&#10;NVIDIA Corp" style="min-height:90px"><?php
            if (!empty($inv['holdings'])) echo htmlspecialchars(implode("\n", array_column($inv['holdings'],'holding_name')));
          ?></textarea>
        </div>
      </div>
    </div>
  </div>

  <!-- Documents -->
  <div class="section" style="margin-top:1.5rem">
    <div class="section-head"><span class="section-title">Documents <span class="section-meta">(PDF, JPG, PNG)</span></span></div>
    <div class="section-body">
      <?php if (!empty($inv['documents'])): foreach ($inv['documents'] as $doc): ?>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:.6rem .9rem;background:var(--surface2);border:1px solid var(--border);border-radius:var(--r);margin-bottom:.5rem;font-size:12.5px">
          <div style="display:flex;align-items:center;gap:8px"><?= svgIcon('doc',14,'var(--text3)') ?><?= htmlspecialchars($doc['name']) ?></div>
          <a href="/<?= htmlspecialchars($doc['file_path']) ?>" target="_blank" class="btn btn-outline btn-sm"><?= svgIcon('eye',11) ?>View</a>
        </div>
      <?php endforeach; endif; ?>
      <input type="file" class="fi" name="documents[]" accept=".pdf,.jpg,.jpeg,.png" multiple style="padding:7px 12px"/>
      <div style="font-size:11px;color:var(--text3);margin-top:4px">Upload multiple documents (prospectus, legal docs, reports)</div>
    </div>
  </div>

  <div style="margin-top:1.5rem;display:flex;gap:.75rem">
    <?php if ($isNew): ?>
      <button type="submit" class="btn btn-primary btn-lg" id="inv-btn"><?= svgIcon('plus',14,'#fff') ?>Create Investment</button>
    <?php else: ?>
      <button type="submit" class="btn btn-primary btn-lg" id="inv-btn"><?= svgIcon('check',14,'#fff') ?>Save Changes</button>
      <a href="/admin/investments" class="btn btn-outline btn-lg">Cancel</a>
    <?php endif; ?>
  </div>
</form>

<script>
function toggleType() {
  const type = document.getElementById('inv-type').value;
  document.getElementById('re-fields').style.display = type === 'real_estate' ? '' : 'none';
  document.getElementById('if-fields').style.display = type === 'index_fund'  ? '' : 'none';
}

<?php if ($isNew): ?>
document.getElementById('inv-form').addEventListener('submit', async e => {
  e.preventDefault();
  const btn  = document.getElementById('inv-btn');
  setLoading(btn, true, 'Creating…');
  const fd   = new FormData(e.target);
  const data = await post('/admin/investments/create', fd, true);
  setLoading(btn, false);
  if (data.success) window.location.href = data.redirect || '/admin/investments';
  else document.getElementById('inv-alert').innerHTML = '<div class="alert alert-err">' + (data.error || 'Failed.') + '</div>';
});
<?php else: ?>
document.getElementById('inv-form').addEventListener('submit', async e => {
  e.preventDefault();
  const btn  = document.getElementById('inv-btn');
  setLoading(btn, true, 'Saving…');
  const fd   = new FormData(e.target);
  const data = await post('/admin/investments/<?= $inv['id'] ?>', fd, true);
  setLoading(btn, false);
  if (data.success) document.getElementById('inv-alert').innerHTML = '<div class="alert alert-ok"><?= svgIcon('check',14,'var(--green)') ?> ' + data.message + '</div>';
  else document.getElementById('inv-alert').innerHTML = '<div class="alert alert-err">' + (data.error || 'Failed.') + '</div>';
});
<?php endif; ?>
</script>
