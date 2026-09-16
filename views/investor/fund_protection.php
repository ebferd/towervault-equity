<?php
/* views/investor/fund_protection.php — public + in-dashboard legal page */
  $brand   = $brand   ?? platform_setting('platform_name', 'NexVest');
  $support = $support ?? platform_setting('platform_support_email', platform_setting('platform_email', ''));
  $sym     = $sym     ?? platform_setting('platform_symbol', '$');
  $addr    = platform_setting('platform_address', '');
  $isPublic = $isPublic ?? false;
  $effDate = date('j F Y');
  $docRef  = 'FP-' . date('Y') . '-01';
  $pct     = 45;                         // protected percentage
  $circ    = 2 * M_PI * 52;              // ring circumference (r=52)
  $dash    = round($circ * $pct / 100, 1);
  $ex = fn($v) => htmlspecialchars((string)$v);
?>
<style>
  .fp{
    --em:#059669; --em-deep:#047857; --em-bright:#10B981; --em-tint:#ECFDF5;
    --navy:#0B1120; --navy-2:#14213A; --gold:#C9A24B;
    --ink:#0E1B15; --muted:#55635B; --faint:#8B988F; --line:#E3EAE6; --soft:#F6F9F7; --paper:#fff;
    --shadow:0 1px 2px rgba(11,17,32,.04),0 20px 46px -26px rgba(11,17,32,.22);
    color:var(--ink); max-width:920px; margin:0 auto;
  }
  .fp *{box-sizing:border-box}
  .fp h1,.fp h2,.fp h3,.fp h4{margin:0}
  .fp p{margin:0}

  /* Document header */
  .fp-eyebrow{display:inline-flex;align-items:center;gap:8px;font-size:11px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--em-deep)}
  .fp-eyebrow .ln{width:22px;height:2px;background:var(--em);border-radius:2px}
  .fp-title{font-family:'Fraunces',Georgia,serif;font-weight:600;font-size:clamp(28px,4.4vw,42px);letter-spacing:-1px;line-height:1.08;margin:14px 0 12px}
  .fp-sub{font-size:15px;color:var(--muted);line-height:1.65;max-width:64ch}
  .fp-meta{display:flex;flex-wrap:wrap;gap:8px;margin-top:18px}
  .fp-chip{display:inline-flex;align-items:center;gap:7px;background:var(--paper);border:1px solid var(--line);border-radius:99px;padding:6px 13px;font-size:12px;color:var(--muted);font-weight:500}
  .fp-chip b{color:var(--ink);font-weight:600}
  .fp-chip svg{width:13px;height:13px;color:var(--em-deep)}

  /* 45% guarantee hero */
  .fp-hero{position:relative;overflow:hidden;background:var(--navy);border-radius:22px;color:#fff;
    padding:34px 36px;margin:26px 0;display:grid;grid-template-columns:auto 1fr;gap:34px;align-items:center;box-shadow:var(--shadow)}
  .fp-hero::before{content:"";position:absolute;inset:0;background:radial-gradient(70% 130% at 100% 0,rgba(16,185,129,.18),transparent 55%);pointer-events:none}
  .fp-ring{position:relative;width:132px;height:132px;flex-shrink:0}
  .fp-ring svg{transform:rotate(-90deg);display:block}
  .fp-ring .pctv{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center}
  .fp-ring .pctn{font-family:'Fraunces',serif;font-size:32px;font-weight:600;line-height:1}
  .fp-ring .pctl{font-size:9px;letter-spacing:.16em;text-transform:uppercase;color:rgba(255,255,255,.55);margin-top:3px}
  .fp-hero .ht{position:relative;z-index:1}
  .fp-hero .hk{font-size:10.5px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:var(--em-bright);margin-bottom:10px}
  .fp-hero h2{font-family:'Fraunces',serif;font-weight:600;font-size:clamp(20px,2.6vw,26px);line-height:1.2;letter-spacing:-.4px;margin-bottom:10px}
  .fp-hero p{font-size:13.5px;line-height:1.7;color:rgba(255,255,255,.72);max-width:52ch}
  .fp-hero .tags{display:flex;flex-wrap:wrap;gap:8px;margin-top:16px}
  .fp-hero .tag{display:inline-flex;align-items:center;gap:7px;font-size:12px;font-weight:600;color:#fff;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.14);border-radius:99px;padding:6px 12px}
  .fp-hero .tag svg{width:14px;height:14px;color:var(--em-bright)}

  /* Two pillars */
  .fp-pillars{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin:22px 0}
  .fp-pill{background:var(--paper);border:1px solid var(--line);border-radius:16px;padding:22px 22px 20px;box-shadow:var(--shadow)}
  .fp-pill .ic{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;background:var(--em-tint);color:var(--em-deep);margin-bottom:14px}
  .fp-pill .ic svg{width:22px;height:22px}
  .fp-pill h3{font-size:15.5px;font-weight:600;margin-bottom:6px}
  .fp-pill p{font-size:13px;color:var(--muted);line-height:1.65}

  /* Legal document paper */
  .fp-paper{background:var(--paper);border:1px solid var(--line);border-radius:18px;box-shadow:var(--shadow);overflow:hidden;margin:22px 0}
  .fp-paper-hd{background:linear-gradient(180deg,#fbfdfc,#f4f8f6);border-bottom:1px solid var(--line);padding:18px 30px;display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap}
  .fp-paper-hd .t{font-size:12.5px;font-weight:700;letter-spacing:.04em;color:var(--ink)}
  .fp-paper-hd .r{font-size:11px;color:var(--faint);font-family:'DejaVu Sans Mono',ui-monospace,monospace}
  .fp-body{padding:8px 30px 30px}
  .fp-clause{padding:22px 0;border-bottom:1px solid #EFF3F0}
  .fp-clause:last-child{border-bottom:none}
  .fp-clause h3{display:flex;gap:12px;align-items:baseline;font-size:16px;font-weight:600;letter-spacing:-.2px;margin-bottom:10px}
  .fp-clause h3 .no{flex-shrink:0;font-family:'Fraunces',serif;color:var(--em-deep);font-weight:600;font-size:15px;min-width:22px}
  .fp-clause p{font-size:13.6px;color:#3f4a44;line-height:1.75;margin-bottom:10px}
  .fp-clause p:last-child{margin-bottom:0}
  .fp-clause .sub{padding-left:34px}
  .fp-clause .sub p{margin-bottom:8px}
  .fp-clause .sub p b.n{color:var(--em-deep);font-weight:600;margin-right:6px}

  /* refund steps */
  .fp-steps{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin:14px 0 4px;padding-left:34px}
  .fp-step{background:var(--soft);border:1px solid var(--line);border-radius:13px;padding:15px 15px 14px;position:relative}
  .fp-step .sn{width:24px;height:24px;border-radius:50%;background:var(--navy);color:#fff;font-size:11.5px;font-weight:700;display:flex;align-items:center;justify-content:center;margin-bottom:10px}
  .fp-step h4{font-size:12.5px;font-weight:600;margin-bottom:4px}
  .fp-step p{font-size:11.5px;color:var(--muted);line-height:1.55;margin:0}

  /* coverage table */
  .fp-tbl{width:100%;border-collapse:collapse;margin:6px 0 2px;font-size:13px}
  .fp-tbl th{text-align:left;background:var(--soft);color:var(--muted);font-size:10.5px;letter-spacing:.05em;text-transform:uppercase;font-weight:700;padding:10px 14px;border:1px solid var(--line)}
  .fp-tbl td{padding:11px 14px;border:1px solid var(--line);color:#3f4a44;line-height:1.55;vertical-align:top}
  .fp-tbl td .yes{color:var(--em-deep);font-weight:600}
  .fp-tbl td .no{color:#9a6a2b;font-weight:600}

  /* seal / attestation */
  .fp-seal{display:flex;align-items:center;gap:16px;background:var(--em-tint);border:1px solid color-mix(in srgb,var(--em) 22%,transparent);border-radius:14px;padding:18px 22px;margin:22px 0}
  .fp-seal .badge{width:52px;height:52px;border-radius:50%;flex-shrink:0;background:var(--paper);border:2px solid var(--em);display:flex;align-items:center;justify-content:center;color:var(--em-deep)}
  .fp-seal .badge svg{width:26px;height:26px}
  .fp-seal h4{font-size:14px;font-weight:600;margin-bottom:3px}
  .fp-seal p{font-size:12.5px;color:var(--muted);line-height:1.6}

  /* CTA (public) */
  .fp-cta{background:var(--navy);border-radius:18px;padding:28px 30px;margin:22px 0;color:#fff;display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap;position:relative;overflow:hidden}
  .fp-cta::before{content:"";position:absolute;inset:0;background:radial-gradient(60% 120% at 0 100%,rgba(16,185,129,.16),transparent 55%);pointer-events:none}
  .fp-cta .ct{position:relative;z-index:1}
  .fp-cta h3{font-family:'Fraunces',serif;font-weight:600;font-size:20px;margin-bottom:5px}
  .fp-cta p{font-size:13px;color:rgba(255,255,255,.7)}
  .fp-cta .btns{display:flex;gap:10px;position:relative;z-index:1;flex-wrap:wrap}
  .fp-cta a{display:inline-flex;align-items:center;height:44px;padding:0 20px;border-radius:11px;font-size:13.5px;font-weight:600;text-decoration:none}
  .fp-cta a.p{background:var(--em);color:#fff}
  .fp-cta a.p:hover{background:var(--em-bright)}
  .fp-cta a.o{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.16);color:#fff}

  /* risk small print */
  .fp-risk{background:var(--navy);border:1px solid rgba(255,255,255,.08);border-radius:14px;padding:20px 24px;margin:22px 0 6px;position:relative;overflow:hidden}
  .fp-risk::before{content:"";position:absolute;inset:0;background:radial-gradient(70% 120% at 0 0,rgba(201,162,75,.14),transparent 60%);pointer-events:none}
  .fp-risk .rh{display:flex;align-items:center;gap:8px;font-size:10px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--gold);margin-bottom:8px;position:relative}
  .fp-risk .rh svg{width:14px;height:14px}
  .fp-risk p{font-size:11.5px;line-height:1.75;color:rgba(255,255,255,.62);position:relative;max-width:92ch}
  .fp-foot-note{text-align:center;font-size:11px;color:var(--faint);margin:16px 0 6px}

  @media(prefers-reduced-motion:no-preference){.fp-ring .arc{animation:fpdraw 1.4s ease-out forwards}}
  @keyframes fpdraw{from{stroke-dashoffset:<?= $circ ?>}}

  @media(max-width:760px){
    .fp-hero{grid-template-columns:1fr;gap:22px;padding:26px 22px;text-align:center}
    .fp-ring{margin:0 auto}
    .fp-hero p{margin:0 auto}
    .fp-hero .tags{justify-content:center}
    .fp-pillars{grid-template-columns:1fr}
    .fp-steps{grid-template-columns:1fr 1fr}
    .fp-body{padding:6px 18px 22px}
    .fp-paper-hd{padding:15px 18px}
    .fp-clause .sub,.fp-steps{padding-left:0}
  }
  @media(max-width:440px){.fp-steps{grid-template-columns:1fr}}
</style>

<div class="fp">

  <!-- Document header -->
  <header>
    <span class="fp-eyebrow"><span class="ln"></span>Investor Protection Policy</span>
    <h1 class="fp-title">Fund Protection</h1>
    <p class="fp-sub">This policy sets out how <?= $ex($brand) ?> safeguards the money you invest — how your capital is ring-fenced and used strictly for its intended purpose, and how a guaranteed portion of every investment is protected under our Capital Protection commitment.</p>
    <div class="fp-meta">
      <span class="fp-chip"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>Effective&nbsp;<b><?= $ex($effDate) ?></b></span>
      <span class="fp-chip"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>Document&nbsp;<b><?= $ex($docRef) ?></b></span>
      <span class="fp-chip"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>Version&nbsp;<b>1.0</b></span>
    </div>
  </header>

  <!-- 45% guarantee hero -->
  <section class="fp-hero">
    <div class="fp-ring">
      <svg width="132" height="132" viewBox="0 0 132 132">
        <circle cx="66" cy="66" r="52" fill="none" stroke="rgba(255,255,255,.12)" stroke-width="12"/>
        <circle class="arc" cx="66" cy="66" r="52" fill="none" stroke="#10B981" stroke-width="12" stroke-linecap="round"
                stroke-dasharray="<?= $dash ?> <?= round($circ - $dash, 1) ?>"/>
      </svg>
      <div class="pctv"><span class="pctn"><?= $pct ?>%</span><span class="pctl">Protected</span></div>
    </div>
    <div class="ht">
      <div class="hk">Capital Protection Guarantee</div>
      <h2>A guaranteed <?= $pct ?>% of your invested capital is protected.</h2>
      <p>Every investment placed through <?= $ex($brand) ?> is insured up to <?= $pct ?>% of the capital committed. In the event an investment does not go as planned, a guaranteed <?= $pct ?>% of your invested capital is refunded to you — independent of the project's outcome.</p>
      <div class="tags">
        <span class="tag"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>Insured up to <?= $pct ?>%</span>
        <span class="tag"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2 4 6v6c0 5 4 8 8 10 4-2 8-5 8-10V6z"/></svg>Ring-fenced funds</span>
        <span class="tag"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>Purpose-bound use</span>
      </div>
    </div>
  </section>

  <!-- Two pillars -->
  <div class="fp-pillars">
    <div class="fp-pill">
      <div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a4 4 0 0 1 8 0v2M12 12v3"/></svg></div>
      <h3>Your money stays for your project</h3>
      <p>Funds you invest are used <b>solely</b> for the specific opportunity you chose. They are never drawn on to run the company, cover overheads, or finance any unrelated activity.</p>
    </div>
    <div class="fp-pill">
      <div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2 4 6v6c0 5 4 8 8 10 4-2 8-5 8-10V6z"/><path d="M9 12l2 2 4-4"/></svg></div>
      <h3><?= $pct ?>% capital protection</h3>
      <p>Should an investment underperform or fail, you are guaranteed the return of <b><?= $pct ?>% of your invested capital</b> under our protection commitment — a floor beneath your downside.</p>
    </div>
  </div>

  <!-- Legal document -->
  <article class="fp-paper">
    <div class="fp-paper-hd">
      <span class="t"><?= $ex(strtoupper($brand)) ?> — FUND PROTECTION POLICY</span>
      <span class="r">Ref <?= $ex($docRef) ?> · v1.0</span>
    </div>
    <div class="fp-body">

      <section class="fp-clause">
        <h3><span class="no">1.</span>Purpose &amp; scope</h3>
        <p>This Fund Protection Policy (the "Policy") describes the protections that apply to funds invested by clients ("you", "the investor") through <?= $ex($brand) ?> (the "Company", "we", "us"). It forms part of, and should be read together with, our Terms of Service and the specific terms published for each investment opportunity.</p>
        <p>The Policy applies to all capital committed to an investment opportunity from the moment your position becomes active until the capital and any returns due are settled to your wallet.</p>
      </section>

      <section class="fp-clause">
        <h3><span class="no">2.</span>Segregation and ring-fencing of investor funds</h3>
        <p>Investor capital is <b>ring-fenced</b> and allocated exclusively to the opportunity for which it was invested. Your funds are recorded against your position and directed only to the underlying purpose of that specific project.</p>
        <div class="sub">
          <p><b class="n">2.1</b>Investor funds are <b>not</b> used to finance the Company's own operations, salaries, marketing, administrative overheads, or any activity unrelated to the project you funded.</p>
          <p><b class="n">2.2</b>Investor funds are <b>not</b> commingled with the Company's operating accounts and are not used to meet the obligations of other, unrelated projects.</p>
          <p><b class="n">2.3</b>Each investment is tracked to a verifiable certificate and position record, so the use of your capital can be attributed to its stated purpose at all times.</p>
        </div>
      </section>

      <section class="fp-clause">
        <h3><span class="no">3.</span>The <?= $pct ?>% Capital Protection Guarantee</h3>
        <p>Every investment is protected — insured up to <b><?= $pct ?>% of the capital committed</b> — under the Company's Capital Protection commitment (the "Guarantee").</p>
        <div class="sub">
          <p><b class="n">3.1</b>If an investment does not go as planned — including where the underlying project underperforms, is unwound, or fails to return the expected capital — you are guaranteed a refund of <b><?= $pct ?>% of your invested capital</b> for that position.</p>
          <p><b class="n">3.2</b>The Guarantee is calculated on the principal capital you committed to the affected position, before any returns already paid to you.</p>
          <p><b class="n">3.3</b>The protected amount is credited to your <?= $ex($brand) ?> wallet, from which it may be withdrawn or reinvested, subject to standard verification.</p>
          <p><b class="n">3.4</b>The Guarantee is a minimum protected floor. Where a project performs and settles in full, you receive the full capital and returns due under that opportunity's terms — the Guarantee does not cap your recovery.</p>
        </div>
      </section>

      <section class="fp-clause">
        <h3><span class="no">4.</span>How the protection is applied</h3>
        <p>If a protected event occurs, the refund is processed through a clear, documented sequence:</p>
        <div class="fp-steps">
          <div class="fp-step"><div class="sn">1</div><h4>Assessment</h4><p>The affected position and its outcome are reviewed and confirmed against the project terms.</p></div>
          <div class="fp-step"><div class="sn">2</div><h4>Calculation</h4><p>The protected amount is calculated as <?= $pct ?>% of the principal capital committed.</p></div>
          <div class="fp-step"><div class="sn">3</div><h4>Credit</h4><p>The protected amount is credited to your wallet and a transaction record is issued.</p></div>
          <div class="fp-step"><div class="sn">4</div><h4>Settlement</h4><p>You may withdraw the funds or reinvest them into another opportunity.</p></div>
        </div>
      </section>

      <section class="fp-clause">
        <h3><span class="no">5.</span>Scope, limitations &amp; risk disclosure</h3>
        <p>The Guarantee protects a defined portion of your capital; it is not a promise that investments cannot lose value. Investing carries risk, and the portion of capital above the protected floor remains at risk.</p>
        <table class="fp-tbl">
          <thead><tr><th style="width:42%">Item</th><th>Position under this Policy</th></tr></thead>
          <tbody>
            <tr><td>Use of your funds</td><td><span class="yes">Ring-fenced</span> — used solely for the project you invested in.</td></tr>
            <tr><td>Protected capital</td><td><span class="yes"><?= $pct ?>% guaranteed</span> refund of principal if the investment does not go as planned.</td></tr>
            <tr><td>Returns / profit</td><td>Targeted per each opportunity's terms; <span class="no">not guaranteed</span> and may vary.</td></tr>
            <tr><td>Capital above <?= $pct ?>%</td><td>Remains <span class="no">at risk</span> and is subject to the outcome of the project.</td></tr>
          </tbody>
        </table>
        <p style="margin-top:12px">Stated returns are targets set out in each product's terms and are not guaranteed. Values can fall as well as rise, and past performance does not indicate future results.</p>
      </section>

      <section class="fp-clause">
        <h3><span class="no">6.</span>Custody, oversight &amp; record-keeping</h3>
        <p>Positions, transactions and certificates are recorded and retained so that every allocation of investor capital and every protected refund can be independently reconciled. You can review your positions, transaction history and certificates at any time from your dashboard.</p>
      </section>

      <section class="fp-clause">
        <h3><span class="no">7.</span>Making a claim &amp; contact</h3>
        <p>In most cases the protection is applied automatically when a protected event is confirmed — no action is needed from you. If you believe a protected event has occurred and has not been actioned, contact us and we will review your position.</p>
        <?php if ($support): ?><p>Support: <b style="color:var(--em-deep)"><?= $ex($support) ?></b></p><?php endif; ?>
        <?php if ($addr): ?><p style="color:var(--faint);font-size:12.5px"><?= $ex($addr) ?></p><?php endif; ?>
      </section>

    </div>
  </article>

  <!-- Attestation seal -->
  <div class="fp-seal">
    <div class="badge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2 4 6v6c0 5 4 8 8 10 4-2 8-5 8-10V6z"/><path d="M9 12l2 2 4-4"/></svg></div>
    <div>
      <h4>An official commitment of <?= $ex($brand) ?></h4>
      <p>This Policy reflects the protections we apply to investor funds. It is issued by <?= $ex($brand) ?> and effective as of <?= $ex($effDate) ?> (Ref <?= $ex($docRef) ?>).</p>
    </div>
  </div>

  <?php if ($isPublic): ?>
  <!-- Public CTA -->
  <section class="fp-cta">
    <div class="ct">
      <h3>Invest with a protected floor.</h3>
      <p>Create your account to browse opportunities — each with clear terms and <?= $pct ?>% capital protection.</p>
    </div>
    <div class="btns">
      <a class="p" href="/register">Create account</a>
      <a class="o" href="/login">Sign in</a>
    </div>
  </section>
  <?php endif; ?>

  <!-- Risk small print -->
  <div class="fp-risk">
    <div class="rh"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4M12 17h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg>Important information</div>
    <p>This Policy summarises the protections <?= $ex($brand) ?> applies to investor funds and does not constitute financial, investment, legal or tax advice. The Capital Protection Guarantee covers <?= $pct ?>% of principal capital as described above; the remaining capital is at risk. Investing involves risk, including the possible loss of the unprotected portion of your capital. Please read each opportunity's full terms, together with our Terms of Service and Privacy Policy, before investing, and seek independent advice if you are unsure.</p>
  </div>

  <p class="fp-foot-note">&copy; <?= date('Y') ?> <?= $ex($brand) ?> · Fund Protection Policy · <?= $ex($docRef) ?></p>

</div>
