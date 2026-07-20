<?php
$isRE = $investment['type'] === 'real_estate';
$sym  = platform_setting('platform_symbol', '$');
$locTxt = $isRE ? trim(implode(', ', array_filter([$investment['city'] ?? '', $investment['country'] ?? '']))) : '';
?>
<div class="breadcrumb"><a href="/investor/investments">Investments</a> &rsaquo; <?= htmlspecialchars($investment['name']) ?></div>

<div class="page-header">
  <div>
    <h1 class="greet"><?= htmlspecialchars($investment['name']) ?></h1>
    <p class="greet-sub"><?= htmlspecialchars($investment['short_desc'] ?? '') ?></p>
  </div>
  <div class="quick-actions" style="align-items:center">
    <span class="status-badge <?= htmlspecialchars($investment['status']) ?>"><?= $investment['status'] === 'coming_soon' ? 'Coming soon' : ucfirst($investment['status']) ?></span>
    <?php if ($investment['status'] === 'active'): ?>
      <button class="qbtn primary" onclick="document.getElementById('idi-modal').style.display='flex'">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><?= nv_icon('invest') ?></svg>
        Invest now
      </button>
    <?php endif; ?>
  </div>
</div>

<div class="detail-grid">
  <div>
    <div class="detail-hero <?= $isRE ? 're' : 'if' ?>" style="<?= $investment['image'] ? '' : 'background:linear-gradient(165deg,#0B1120 0%,' . ($isRE ? '#172643 55%,#1E3A5F' : '#0F2E26 55%,#0E4536') . ' 100%)' ?>">
      <?php if ($investment['image']): ?>
        <img src="<?= htmlspecialchars(file_url($investment['image'])) ?>" alt="<?= htmlspecialchars($investment['name']) ?>"/>
      <?php else: ?>
        <?= nv_hero_art($investment['type'], (int)$investment['id']) ?>
      <?php endif; ?>
    </div>

    <?php if ($investment['description']): ?>
    <div class="detail-section">
      <div class="detail-section-head">Overview</div>
      <div class="detail-body"><?= nl2br(htmlspecialchars($investment['description'])) ?></div>
    </div>
    <?php endif; ?>

    <?php if ($isRE): ?>
    <div class="detail-section">
      <div class="detail-section-head">Property details</div>
      <div class="kv-grid">
        <?php foreach ([
          ['Type', $investment['property_type'] ?? '—'],
          ['Location', $locTxt ?: '—'],
          ['Size', $investment['property_size'] ?? '—'],
          ['Units', $investment['total_units'] ?? '—'],
          ['Year built', $investment['year_built'] ?? '—'],
        ] as [$l, $v]): ?>
          <div class="kv-item"><div class="kv-lbl"><?= $l ?></div><div class="kv-val"><?= htmlspecialchars((string)$v) ?></div></div>
        <?php endforeach; ?>
      </div>
      <?php if ($investment['maps_link']): ?>
        <div style="padding:.85rem 1.25rem;border-top:1px solid var(--mist-100)">
          <a href="<?= htmlspecialchars($investment['maps_link']) ?>" target="_blank" class="qbtn outline" style="display:inline-flex">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><?= nv_icon('pin') ?></svg>
            View on map
          </a>
        </div>
      <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="detail-section">
      <div class="detail-section-head">Fund details</div>
      <div class="kv-grid">
        <?php foreach ([
          ['Ticker', $investment['ticker'] ?? '—'],
          ['Category', $investment['fund_category'] ?? '—'],
          ['Risk', ucwords(str_replace('_', ' ', $investment['risk_level'] ?? '—'))],
          ['Mgmt. fee', $investment['management_fee'] ? $investment['management_fee'] . '%' : '—'],
          ['Benchmark', $investment['benchmark'] ?? '—'],
        ] as [$l, $v]): ?>
          <div class="kv-item"><div class="kv-lbl"><?= $l ?></div><div class="kv-val"><?= htmlspecialchars((string)$v) ?></div></div>
        <?php endforeach; ?>
      </div>
      <?php if (!empty($fundHoldings)): ?>
        <div style="padding:1rem 1.25rem;border-top:1px solid var(--mist-100)">
          <div class="kv-lbl" style="margin-bottom:.6rem">Top holdings</div>
          <div style="display:flex;flex-wrap:wrap;gap:.4rem">
            <?php foreach ($fundHoldings as $h): ?><span class="tag-chip"><?= htmlspecialchars($h['holding_name']) ?></span><?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="detail-section">
      <div class="detail-section-head">Documents</div>
      <?php if (!empty($docs)): ?>
      <div>
        <?php foreach ($docs as $d): ?>
          <div class="doc-row">
            <div class="doc-name"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--mist-400)" stroke-width="1.8"><?= nv_icon('doc') ?></svg><?= htmlspecialchars($d['name']) ?></div>
            <a href="<?= htmlspecialchars(file_url($d['file_path'])) ?>" target="_blank" class="row-link">Download</a>
          </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
      <p style="font-size:13px;color:var(--mist-400);padding:.5rem 0">No documents have been uploaded for this investment yet.</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- Sidebar -->
  <div class="sidebar-card">
    <div class="sidebar-roi">
      <div class="sidebar-roi-lbl">Total ROI</div>
      <div class="sidebar-roi-val"><?= htmlspecialchars((string)$investment['roi']) ?>%</div>
      <div class="sidebar-roi-sub"><?= (int)$investment['duration_value'] . ' ' . ucwords(rtrim($investment['duration_unit'], 's') . 's') ?></div>
    </div>
    <div class="sidebar-body">
      <div class="fact-row"><span>Min. investment</span><span><?= fmt_currency((float)$investment['min_investment']) ?></span></div>
      <div class="fact-row"><span>Max. investment</span><span><?= $investment['max_investment'] ? fmt_currency((float)$investment['max_investment']) : 'No limit' ?></span></div>
      <div class="fact-row"><span>Payout</span><span><?= ucwords(str_replace('_', ' ', $investment['payout_frequency'] ?? 'monthly')) ?></span></div>

      <?php if ($investment['funding_target'] > 0):
        $pct = min(100, round(((float)$investment['funding_raised'] / (float)$investment['funding_target']) * 100));
      ?>
        <div class="card-prog" style="margin-top:1rem">
          <div class="card-prog-top"><span><?= $pct ?>% Funded</span><span><b><?= fmt_currency((float)$investment['funding_raised']) ?></b></span></div>
          <div class="card-prog-bar"><div class="card-prog-fill" style="width:<?= $pct ?>%"></div></div>
        </div>
      <?php endif; ?>

      <?php if ($holding): ?>
        <div class="position-chip">
          <div class="pc-amt">Your position: <?= fmt_currency((float)$holding['amount']) ?></div>
          <div class="pc-earn">Earned: +<?= fmt_currency((float)$holding['total_earned']) ?></div>
        </div>
      <?php endif; ?>

      <?php if ($investment['status'] === 'active'): ?>
        <button class="qbtn primary" style="width:100%;margin-top:1rem" onclick="document.getElementById('idi-modal').style.display='flex'">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><?= nv_icon('invest') ?></svg>
          Invest now
        </button>
      <?php endif; ?>
      <a href="/investor/calculator?inv=<?= (int)$investment['id'] ?>" class="qbtn outline" style="width:100%;margin-top:.5rem">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><?= nv_icon('calc') ?></svg>
        Calculate returns
      </a>
    </div>
  </div>
</div>

<?php
$_cryptoAddr = [
    'BTC'  => platform_setting('crypto_btc_address', ''),
    'ETH'  => platform_setting('crypto_eth_address', ''),
    'USDT' => platform_setting('crypto_usdt_address', ''),
    'USDC' => platform_setting('crypto_usdc_address', ''),
];
$_ppEmail = platform_setting('paypal_email', '');
$_ppMe    = platform_setting('paypal_me_link', '');
$_wireBnk = platform_setting('wire_bank_name', '');
$_wireAcc = platform_setting('wire_account_name', '');
$_wireNum = platform_setting('wire_account_number', '');
$_wireRt  = platform_setting('wire_routing', '');
$_wireSw  = platform_setting('wire_swift', '');
?>
<!-- Invest Modal -->
<div id="idi-modal" class="modal-overlay" style="display:none">
  <div class="modal">
    <div class="modal-head">
      <h3 class="modal-title" id="idi-modal-title">Invest</h3>
      <button class="modal-close" onclick="idiClose()">&times;</button>
    </div>
    <div class="modal-body">
      <!-- Step 1: amount + method -->
      <div id="idi-step1">
        <div id="idi-alert"></div>
        <form id="idi-form">
          <input type="hidden" name="_token" value="<?= csrf_token() ?>"/>
          <input type="hidden" name="investment_id" value="<?= (int)$investment['id'] ?>"/>
          <?php if (!empty($userMinOverride) && $userMinOverride > (float)$investment['min_investment']): ?>
            <div class="alert-banner warn" style="margin-bottom:.85rem;font-size:12.5px;display:flex;gap:8px;align-items:flex-start">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;margin-top:1px"><path d="M12 9v4M12 17h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>
              <span>Your account has a minimum investment of <strong><?= fmt_currency($userMinOverride) ?></strong>.<?= $userMinNote !== '' ? ' ' . htmlspecialchars($userMinNote) : '' ?></span>
            </div>
          <?php endif; ?>
          <div class="fg">
            <label class="fl">Amount (min. <?= fmt_currency((float)$effectiveMin) ?>)</label>
            <div class="fi-prefix"><span class="fi-sym"><?= htmlspecialchars($sym) ?></span><input class="fi" type="number" name="amount" id="idi-amount" min="<?= $effectiveMin ?>" value="<?= $effectiveMin ?>" oninput="updateProj()"/></div>
          </div>
          <div class="proj-box"><span>Projected total return (<?= (int)$investment['duration_value'] . ' ' . htmlspecialchars($investment['duration_unit']) ?>)</span><span id="idi-proj">—</span></div>
          <label class="fl" style="margin-bottom:.5rem">Payment method</label>
          <div style="margin-bottom:.5rem">
            <?php if ($walletBalance >= (float)$effectiveMin): ?>
              <div onclick="idm('wallet')" id="idim-wallet" class="pmethod-row">
                <div><div class="pmethod-name">Wallet Balance</div><div class="pmethod-sub">Available: <?= fmt_currency($walletBalance) ?> — instant activation</div></div>
                <div id="idir-wallet" class="pmethod-radio"></div>
              </div>
            <?php endif; ?>
            <?php foreach (array_filter([platform_setting('payment_crypto','1')==='1'?['crypto','Cryptocurrency','BTC · ETH · USDT · USDC']:null,platform_setting('payment_paypal','1')==='1'?['paypal','PayPal','Instant transfer']:null,platform_setting('payment_wire','1')==='1'?['wire','Wire Transfer','3–5 business days']:null]) as [$mid,$ml,$ms]): ?>
              <div onclick="idm('<?= $mid ?>')" id="idim-<?= $mid ?>" class="pmethod-row">
                <div><div class="pmethod-name"><?= $ml ?></div><div class="pmethod-sub"><?= $ms ?></div></div>
                <div id="idir-<?= $mid ?>" class="pmethod-radio"></div>
              </div>
            <?php endforeach; ?>
          </div>
          <button type="submit" class="qbtn primary" style="width:100%;height:44px;margin-top:.5rem" id="idi-btn"><span>Confirm investment</span></button>
        </form>
      </div>

      <!-- Step 2: payment instructions (shown after invest() succeeds) -->
      <div id="idi-step2" style="display:none">
        <div id="idi-pay-instructions"></div>
        <div style="border-top:1px solid var(--mist-100);padding-top:1rem;margin-top:1rem">
          <p style="font-size:12.5px;color:var(--mist-500);margin-bottom:.75rem">Once you have sent the payment, click the button below. Our team will review and activate your investment.</p>
          <form id="idi-confirm-form" enctype="multipart/form-data">
            <input type="hidden" name="_token" value="<?= csrf_token() ?>"/>
            <input type="hidden" name="reference" id="idi-conf-ref"/>
            <div class="fg"><label class="fl">Proof of payment <span style="color:var(--mist-400);font-weight:400">(optional — screenshot or receipt)</span></label><input type="file" class="fi" name="proof" accept="image/*,.pdf" style="padding:9px 12px;height:auto"/></div>
            <button type="submit" class="qbtn primary" style="width:100%;height:44px" id="idi-conf-btn"><span>I have sent the payment</span></button>
          </form>
          <div id="idi-conf-alert" style="margin-top:.75rem"></div>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
const IDI_CRYPTO = <?= json_encode(array_filter($_cryptoAddr)) ?>;
const IDI_PP_EMAIL = <?= json_encode($_ppEmail) ?>;
const IDI_PP_ME   = <?= json_encode($_ppMe) ?>;
const IDI_WIRE    = {bank:<?= json_encode($_wireBnk) ?>,acc:<?= json_encode($_wireAcc) ?>,num:<?= json_encode($_wireNum) ?>,rt:<?= json_encode($_wireRt) ?>,sw:<?= json_encode($_wireSw) ?>};
const IDI_SYM     = <?= json_encode($sym) ?>;
let idiM = null, idiCoin = null;

function idiClose(){
  document.getElementById('idi-modal').style.display='none';
  document.getElementById('idi-step1').style.display='';
  document.getElementById('idi-step2').style.display='none';
  document.getElementById('idi-modal-title').textContent='Invest';
  document.getElementById('idi-alert').innerHTML='';
  document.getElementById('idi-conf-alert').innerHTML='';
}
function idm(m){
  idiM=m;
  ['wallet','crypto','paypal','wire'].forEach(id=>{
    const el=document.getElementById('idim-'+id);
    const r=document.getElementById('idir-'+id);
    if(!el)return;
    el.classList.toggle('sel', id===m);
    r.classList.toggle('sel', id===m);
  });
}
function updateProj(){
  const amt=parseFloat(document.getElementById('idi-amount').value)||0;
  const total=amt*<?= (float)$investment['roi'] ?>/100;
  document.getElementById('idi-proj').textContent='+'+IDI_SYM+total.toLocaleString('en-US',{minimumFractionDigits:2})+' (<?= htmlspecialchars((string)$investment['roi']) ?>% total)';
}
updateProj();

function idiRow(l,v,mono){return '<div class="wire-row"><span>'+l+'</span><span'+(mono?' style="font-family:monospace"':'')+'>'+v+'</span></div>';}

function idiShowInstructions(method, amount, ref){
  let html = '<div class="reserved-box"><div class="reserved-lbl">Investment Reserved</div><div class="reserved-val">'+IDI_SYM+parseFloat(amount).toLocaleString('en-US',{minimumFractionDigits:2})+' &middot; Ref: '+ref+'</div></div>';
  if (method === 'crypto') {
    const coins = Object.keys(IDI_CRYPTO);
    if (!coins.length){ html += '<p style="color:var(--mist-500);font-size:13px">Crypto addresses not configured yet. Please contact support.</p>'; }
    else {
      idiCoin = idiCoin || coins[0];
      html += '<div style="margin-bottom:.85rem"><label class="fl" style="margin-bottom:.4rem">Select coin</label><div style="display:flex;gap:.4rem;flex-wrap:wrap">';
      coins.forEach(c=>{ html += '<button type="button" onclick="idiSelectCoin(\''+c+'\')" id="idi-coin-'+c+'" class="coin-chip">'+c+'</button>'; });
      html += '</div></div>';
      html += '<div id="idi-coin-addr"></div>';
    }
  } else if (method === 'paypal') {
    html += idiRow('Send to', IDI_PP_EMAIL, true);
    if(IDI_PP_ME) html += '<a href="'+IDI_PP_ME+'" target="_blank" class="qbtn primary" style="width:100%;height:42px;margin:.75rem 0">Pay via PayPal.me</a>';
  } else {
    html += idiRow('Bank',IDI_WIRE.bank,false)+idiRow('Account Name',IDI_WIRE.acc,false)+idiRow('Account Number',IDI_WIRE.num,true)+idiRow('Routing / Sort Code',IDI_WIRE.rt,true)+idiRow('SWIFT / BIC',IDI_WIRE.sw,true)+idiRow('Reference',ref,true);
  }
  document.getElementById('idi-pay-instructions').innerHTML = html;
  if(method==='crypto' && Object.keys(IDI_CRYPTO).length) idiSelectCoin(idiCoin || Object.keys(IDI_CRYPTO)[0]);
}

function idiSelectCoin(coin){
  idiCoin = coin;
  Object.keys(IDI_CRYPTO).forEach(c=>{
    const b=document.getElementById('idi-coin-'+c);
    if(!b)return;
    b.classList.toggle('sel', c===coin);
  });
  const addr = IDI_CRYPTO[coin]||'';
  const networks = {BTC:'Bitcoin Network',ETH:'ERC-20 Network',USDT:'TRC-20 Network',USDC:'ERC-20 Network'};
  document.getElementById('idi-coin-addr').innerHTML =
    '<div style="margin-top:.75rem">' + idiRow('Network', networks[coin]||coin, false) +
    '<div style="margin-top:.5rem"><div class="kv-lbl" style="margin-bottom:.3rem">Wallet address</div>' +
    '<div class="addr-box" onclick="copyText(\''+addr+'\',this)">'+addr+
    '<div style="font-size:10px;color:var(--mist-400);margin-top:3px">Click to copy</div></div></div></div>';
}

document.getElementById('idi-form').addEventListener('submit',async e=>{
  e.preventDefault();
  if(!idiM){document.getElementById('idi-alert').innerHTML='<div class="alert-banner err">Please select a payment method.</div>';return;}
  const btn=document.getElementById('idi-btn');
  btn.disabled=true;
  btn.querySelector('span').innerHTML='<span class="spinner"></span> Processing…';
  const fd=new FormData(e.target);
  fd.append('method',idiM);
  const data=await post('/investor/invest',fd,true);
  btn.disabled=false;
  if(data.success && data.wallet_paid){
    btn.querySelector('span').innerHTML='✓ Invested!';
    document.getElementById('idi-alert').innerHTML='<div class="alert-banner ok">Investment activated! Redirecting to your portfolio…</div>';
    setTimeout(()=>window.location.href=data.redirect||'/investor/portfolio',2000);
  } else if(data.success){
    document.getElementById('idi-step1').style.display='none';
    document.getElementById('idi-step2').style.display='';
    document.getElementById('idi-modal-title').textContent='Payment Instructions';
    document.getElementById('idi-conf-ref').value=data.invoice_ref;
    idiShowInstructions(data.method, data.amount, data.invoice_ref);
  } else {
    btn.querySelector('span').textContent='Confirm investment';
    document.getElementById('idi-alert').innerHTML='<div class="alert-banner err">'+(data.error||'Failed.')+'</div>';
  }
});

document.getElementById('idi-confirm-form').addEventListener('submit',async e=>{
  e.preventDefault();
  const btn=document.getElementById('idi-conf-btn');
  btn.disabled=true;
  btn.querySelector('span').innerHTML='<span class="spinner"></span> Submitting…';
  const fd=new FormData(e.target);
  if(idiM==='crypto'&&idiCoin) fd.append('coin',idiCoin);
  const data=await post('/investor/deposit/confirm',fd,true);
  if(data.success){
    document.getElementById('idi-conf-alert').innerHTML='<div class="alert-banner ok">Payment submitted! We will review and activate your investment shortly.</div>';
    btn.disabled=true;
    setTimeout(()=>window.location.href='/investor/portfolio',2500);
  } else {
    btn.disabled=false;
    btn.querySelector('span').textContent='I have sent the payment';
    document.getElementById('idi-conf-alert').innerHTML='<div class="alert-banner err">'+(data.error||'Failed.')+'</div>';
  }
});
</script>
