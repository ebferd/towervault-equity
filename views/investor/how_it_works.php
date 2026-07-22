<?php /* views/investor/how_it_works.php — investor guide, values pulled from settings */ ?>
<style>
.hiw-intro{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:1.35rem 1.5rem;margin-bottom:1.5rem}
.hiw-intro p{font-size:13.5px;line-height:1.7;color:var(--text2);margin:0}

.hiw-steps{position:relative;margin-bottom:2rem}
.hiw-step{display:flex;gap:1rem;padding-bottom:1.5rem;position:relative}
.hiw-step:last-child{padding-bottom:0}
.hiw-step::before{content:'';position:absolute;left:17px;top:38px;bottom:0;width:2px;background:var(--border)}
.hiw-step:last-child::before{display:none}
.hiw-num{width:36px;height:36px;border-radius:50%;background:var(--em-600);color:#fff;display:flex;
  align-items:center;justify-content:center;font-size:14px;font-weight:700;flex-shrink:0;position:relative;z-index:1}
.hiw-body{flex:1;min-width:0;padding-top:.3rem}
.hiw-title{font-size:14.5px;font-weight:700;color:var(--text);margin-bottom:.35rem}
.hiw-text{font-size:13px;line-height:1.7;color:var(--text3)}
.hiw-text strong{color:var(--text2)}

.hiw-card{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:1.35rem 1.5rem;margin-bottom:1.25rem}
.hiw-card-title{font-size:14px;font-weight:700;color:var(--text);margin-bottom:.75rem;display:flex;align-items:center;gap:.5rem}
.hiw-card p{font-size:13px;line-height:1.7;color:var(--text3);margin:0 0 .6rem}
.hiw-card p:last-child{margin-bottom:0}

.hiw-example{background:var(--mist-50);border:1px solid var(--border);border-radius:10px;padding:.9rem 1.1rem;margin-top:.85rem}
.hiw-ex-row{display:flex;justify-content:space-between;font-size:12.5px;padding:.3rem 0;color:var(--text3)}
.hiw-ex-row.total{border-top:1px solid var(--border);margin-top:.35rem;padding-top:.55rem;font-weight:700;color:var(--text)}

.hiw-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.25rem}
@media(max-width:700px){.hiw-grid{grid-template-columns:1fr}}
.hiw-mini{background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:1.1rem 1.25rem}
.hiw-mini-t{font-size:13px;font-weight:700;color:var(--text);margin-bottom:.3rem}
.hiw-mini-s{font-size:12.5px;line-height:1.65;color:var(--text3)}

.hiw-note{background:#fffbea;border:1px solid #f0d060;border-radius:12px;padding:1rem 1.25rem;margin-bottom:1.5rem;
  font-size:12.5px;line-height:1.7;color:var(--text2)}
.hiw-cta{display:flex;gap:.7rem;flex-wrap:wrap}
</style>

<div class="page-header">
  <div>
    <h1 class="greet">How it works</h1>
    <p class="greet-sub">A short guide to investing, earning, and withdrawing on this platform.</p>
  </div>
</div>

<div class="hiw-intro">
  <p>Every opportunity on this platform states its terms upfront — the total return, how long it runs, how often you're paid, and the minimum to take part. Those terms are fixed the moment you invest and don't change afterwards.</p>
</div>

<!-- ── Steps ─────────────────────────────────────────────── -->
<div class="hiw-steps">

  <div class="hiw-step">
    <div class="hiw-num">1</div>
    <div class="hiw-body">
      <div class="hiw-title">Set up your account</div>
      <div class="hiw-text">
        You're already registered.
        <?php if ($kycOn): ?>
          Before you can invest, complete <strong>identity verification (KYC)</strong> from the Identity page — upload a valid ID and proof of address. Verification protects your account and is a legal requirement.
        <?php else: ?>
          Add your details on the Profile page so your certificates and records are accurate.
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="hiw-step">
    <div class="hiw-num">2</div>
    <div class="hiw-body">
      <div class="hiw-title">Add funds to your wallet</div>
      <div class="hiw-text">
        Go to <strong>Wallet → Deposit</strong> and choose a payment method<?php
          $methods = [];
          if ($payCrypto) $methods[] = 'cryptocurrency';
          if ($payPaypal) $methods[] = 'PayPal';
          if ($payWire)   $methods[] = 'bank transfer';
          echo $methods ? ' (' . implode(', ', $methods) . ')' : '';
        ?>. The minimum deposit is <strong><?= fmt_currency($minDeposit) ?></strong>.
        Deposits are reviewed and credited to your wallet balance, which you then use to invest.
      </div>
    </div>
  </div>

  <div class="hiw-step">
    <div class="hiw-num">3</div>
    <div class="hiw-body">
      <div class="hiw-title">Choose an investment</div>
      <div class="hiw-text">
        Browse <strong>Investments</strong> to see what's open. Each one shows its total return, duration, payout frequency, and minimum amount before you commit. Open any listing to read the full brief and documents, then enter your amount and confirm.
      </div>
    </div>
  </div>

  <div class="hiw-step">
    <div class="hiw-num">4</div>
    <div class="hiw-body">
      <div class="hiw-title">Earn returns on schedule</div>
      <div class="hiw-text">
        Once active, your position pays out at the frequency stated in its terms — daily, weekly, monthly, quarterly, or at maturity. Each payout is credited straight to your <strong>wallet balance</strong> and appears in Transactions. Track everything from your Portfolio.
      </div>
    </div>
  </div>

  <div class="hiw-step">
    <div class="hiw-num">5</div>
    <div class="hiw-body">
      <div class="hiw-title">Maturity</div>
      <div class="hiw-text">
        On the maturity date, your <strong>original amount is returned</strong> to your wallet. Your returns will already have been paid across the term, so nothing is left outstanding.
      </div>
    </div>
  </div>

  <div class="hiw-step">
    <div class="hiw-num">6</div>
    <div class="hiw-body">
      <div class="hiw-title">Withdraw any time</div>
      <div class="hiw-text">
        Withdraw available wallet funds from <strong>Wallet → Withdraw</strong>. The minimum withdrawal is <strong><?= fmt_currency($minWithdraw) ?></strong>. Requests are reviewed before being released to your chosen destination.
      </div>
    </div>
  </div>

</div>

<!-- ── Understanding returns ─────────────────────────────── -->
<div class="hiw-card">
  <div class="hiw-card-title">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--em-600)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><?= nv_icon('info') ?></svg>
    Understanding the return figure
  </div>
  <p>The percentage shown on an investment is the <strong>total return for the whole term</strong> — not a yearly rate.</p>
  <p>So if a plan shows <strong><?= rtrim(rtrim(number_format($sampleRoi, 2), '0'), '.') ?>%</strong> over its stated duration, that's the total you earn across that entire period. The payout frequency only changes how often it's paid to you — never the total.</p>

  <?php
    $exAmount = 1000;
    $exReturn = $exAmount * ($sampleRoi / 100);
  ?>
  <div class="hiw-example">
    <div class="hiw-ex-row"><span>You invest</span><span><?= fmt_currency($exAmount) ?></span></div>
    <div class="hiw-ex-row"><span>Stated total return</span><span><?= rtrim(rtrim(number_format($sampleRoi, 2), '0'), '.') ?>%</span></div>
    <div class="hiw-ex-row"><span>Returns paid over the term</span><span><?= fmt_currency($exReturn) ?></span></div>
    <div class="hiw-ex-row"><span>Original amount returned at maturity</span><span><?= fmt_currency($exAmount) ?></span></div>
    <div class="hiw-ex-row total"><span>Total back in your wallet</span><span><?= fmt_currency($exAmount + $exReturn) ?></span></div>
  </div>
</div>

<!-- ── Good to know ──────────────────────────────────────── -->
<div class="hiw-grid">
  <div class="hiw-mini">
    <div class="hiw-mini-t">Certificates</div>
    <div class="hiw-mini-s">Every investment issues a certificate with a unique reference. Download it from Certificates — anyone can verify it independently using that reference.</div>
  </div>
  <div class="hiw-mini">
    <div class="hiw-mini-t">Earnings calculator</div>
    <div class="hiw-mini-s">Use the Calculator to model an amount against any open plan and see the payout schedule before you commit.</div>
  </div>
  <div class="hiw-mini">
    <div class="hiw-mini-t">Referrals</div>
    <div class="hiw-mini-s">Share your referral code and earn <strong><?= rtrim(rtrim(number_format($refRate, 2), '0'), '.') ?>%</strong> commission on the first investment made by anyone who joins through it.</div>
  </div>
  <div class="hiw-mini">
    <div class="hiw-mini-t">Need help?</div>
    <div class="hiw-mini-s">Raise a ticket from the Support page for anything needing a written record, or start a live chat there for quick questions.</div>
  </div>
</div>

<div class="hiw-note">
  <strong>A note on risk.</strong> Investing carries risk, including the loss of capital. Stated returns are the targets set out in each product's terms and are not guaranteed. Values can fall as well as rise, and past performance does not indicate future results. Always read the full brief before investing, and seek independent advice if you're unsure.
</div>

<div class="hiw-cta">
  <a href="/investor/investments" class="qbtn primary" style="height:40px;padding:0 1.25rem;display:inline-flex;align-items:center">Browse investments</a>
  <a href="/investor/calculator" class="qbtn outline" style="height:40px;padding:0 1.25rem;display:inline-flex;align-items:center">Open calculator</a>
</div>
