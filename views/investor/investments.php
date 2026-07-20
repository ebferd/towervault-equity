<div class="page-header">
  <div>
    <h1 class="greet">Investments</h1>
    <p class="greet-sub">Curated real estate and index fund opportunities, vetted for transparency and return.</p>
  </div>
</div>

<div class="filter-bar">
  <div class="tabs">
    <?php foreach ([['all','All'],['real_estate','Real Estate'],['index_fund','Index Funds']] as [$t,$l]): ?>
      <a href="/investor/investments?type=<?= $t ?><?= !empty($mine) ? '&mine=1' : '' ?>" class="tab<?= ($type ?? 'all') === $t ? ' active' : '' ?>"><?= htmlspecialchars($l) ?></a>
    <?php endforeach; ?>
  </div>
  <a href="/investor/investments?type=<?= htmlspecialchars($type ?? 'all') ?><?= empty($mine) ? '&mine=1' : '' ?>" class="toggle-mine">
    <span class="switch<?= !empty($mine) ? ' on' : '' ?>"></span>
    My investments only
  </a>
</div>

<?php if (empty($investments)): ?>
  <div class="empty" style="background:#fff;border:1px solid var(--mist-200);border-radius:16px">
    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="var(--mist-300)" stroke-width="1.5"><?= nv_icon('building') ?></svg>
    <p>No investments match this filter.</p>
    <?php if (!empty($mine)): ?><a href="/investor/investments" class="row-link">Clear filters</a><?php endif; ?>
  </div>
<?php else: ?>
  <div class="inv-grid">
    <?php foreach ($investments as $i => $inv):
      $isRE   = $inv['type'] === 'real_estate';
      $myInv  = (float)($inv['my_invested'] ?? 0);
      $pct    = $inv['funding_target'] > 0 ? min(100, round(((float)$inv['funding_raised'] / (float)$inv['funding_target']) * 100)) : null;
      $locTxt = $isRE
          ? trim(implode(', ', array_filter([$inv['city'] ?? '', $inv['country'] ?? ''])))
          : ucwords(str_replace('_', ' ', $inv['risk_level'] ?? 'medium')) . ' risk';
    ?>
      <div class="inv-card">
        <div class="inv-hero <?= $isRE ? 're' : 'if' ?>">
          <?php if (!empty($inv['image'])): ?>
            <img src="<?= file_url($inv['image']) ?>" alt="<?= htmlspecialchars($inv['name']) ?>"/>
          <?php else: ?>
            <?= nv_hero_art($inv['type'], $i) ?>
          <?php endif; ?>
          <div class="hero-fade"></div>
          <?php if (!empty($inv['is_featured'])): ?><div class="ribbon">FEATURED</div><?php endif; ?>
          <div class="type-pill"><?= $isRE ? 'Real Estate' : 'Index Fund' ?></div>
          <div class="hero-roi<?= !empty($inv['is_featured']) ? ' with-ribbon' : '' ?>">
            <div class="hero-roi-val"><?= htmlspecialchars((string)$inv['roi']) ?>%</div>
            <div class="hero-roi-lbl">Total ROI</div>
          </div>
          <div class="stat-strip">
            <div class="stat-strip-item"><div class="stat-strip-lbl">Min.</div><div class="stat-strip-val"><?= fmt_currency((float)$inv['min_investment']) ?></div></div>
            <div class="stat-strip-item"><div class="stat-strip-lbl">Duration</div><div class="stat-strip-val"><?= (int)$inv['duration_value'] . ' ' . ucfirst($inv['duration_unit']) ?></div></div>
            <div class="stat-strip-item"><div class="stat-strip-lbl"><?= $isRE ? 'Payout' : 'Risk' ?></div><div class="stat-strip-val"><?= $isRE ? ucwords(str_replace('_',' ',$inv['payout_frequency'] ?? 'monthly')) : ucwords(str_replace('_',' ',$inv['risk_level'] ?? 'medium')) ?></div></div>
          </div>
        </div>
        <div class="inv-body">
          <div class="inv-name"><?= htmlspecialchars($inv['name']) ?></div>
          <?php if ($locTxt): ?>
            <div class="inv-loc">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><?= $isRE ? nv_icon('pin') : '<circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/>' ?></svg>
              <?= htmlspecialchars($locTxt) ?>
            </div>
          <?php endif; ?>
          <?php if (!empty($inv['short_desc'])): ?><p class="inv-desc"><?= htmlspecialchars($inv['short_desc']) ?></p><?php endif; ?>

          <?php if ($myInv > 0): ?>
            <div class="my-pos">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><?= nv_icon('check') ?></svg>
              You've invested <?= fmt_currency($myInv) ?>
            </div>
          <?php endif; ?>

          <?php if ($pct !== null): ?>
            <div class="card-prog">
              <div class="card-prog-top"><span><?= $pct ?>% Funded</span><span><b><?= fmt_currency((float)$inv['funding_raised']) ?></b> of <?= fmt_currency((float)$inv['funding_target']) ?></span></div>
              <div class="card-prog-bar"><div class="card-prog-fill" style="width:<?= $pct ?>%"></div></div>
            </div>
          <?php endif; ?>

          <div class="inv-foot">
            <a href="/investor/investments/<?= (int)$inv['id'] ?>" class="inv-cta">
              View details
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
