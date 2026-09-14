<?php /* views/investor/how_it_works.php — investor guide, values pulled from settings */
  $sym   = platform_setting('platform_symbol', '$');
  $fmt   = fn($n) => $sym . number_format((float)$n, 0);
  $minStr = $fmt($minInvest);
  $roi   = (float) $sampleRoi;
  $roiStr = rtrim(rtrim(number_format($roi, 2), '0'), '.');
  $base  = 1000; $ret = $base * $roi / 100; $tot = $base + $ret;
  $invBarH = $tot > 0 ? max(40, round($base / $tot * 96)) : 78;
  $mc = count($payMethods);
  $methodStr = $mc === 0 ? 'any of your available payment methods'
             : ($mc === 1 ? $payMethods[0]
             : implode(', ', array_slice($payMethods, 0, -1)) . ' and ' . end($payMethods));
?>
<style>
  .hiw{
    --em:#059669; --em-bright:#10B981; --em-deep:#047857; --em-tint:#ECFDF5;
    --ink:#0E1B15; --muted:#55635B; --faint:#8B988F; --line:#E3EAE6; --soft:#F4F8F6;
    --navy:#0B1120; --navy-2:#14213A; --gold:#C9A24B; --paper:#fff;
    --shadow:0 1px 2px rgba(11,17,32,.04),0 14px 34px -18px rgba(11,17,32,.16);
    color:var(--ink);
  }
  .hiw *{box-sizing:border-box}
  .hiw h1,.hiw h2,.hiw h3,.hiw h4{margin:0;text-wrap:balance}
  .hiw-eyebrow{display:inline-flex;align-items:center;gap:8px;font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:var(--em-deep)}
  .hiw-eyebrow .ln{width:22px;height:2px;background:var(--em);border-radius:2px}
  .hiw-sec-h{font-family:'Fraunces',serif;font-weight:600;letter-spacing:-.5px;font-size:clamp(21px,3vw,28px);margin:12px 0 8px}
  .hiw-sec-p{font-size:14.5px;color:var(--muted);max-width:60ch;margin:0;line-height:1.6}
  .hiw-label{margin:40px 0 4px}
  @media(prefers-reduced-motion:reduce){.hiw *{animation:none!important}}

  .hiw-hero{display:grid;grid-template-columns:1.05fr .95fr;gap:40px;align-items:center;background:var(--paper);border:1px solid var(--line);border-radius:24px;padding:40px 42px;margin-bottom:8px;box-shadow:var(--shadow);position:relative;overflow:hidden}
  .hiw-hero::before{content:"";position:absolute;inset:0;background:radial-gradient(60% 80% at 100% 0,rgba(16,185,129,.10),transparent 60%);pointer-events:none}
  @media(max-width:800px){.hiw-hero{grid-template-columns:1fr;padding:30px 24px;gap:24px}}
  .hiw-hero h1{font-family:'Fraunces',serif;font-weight:600;font-size:clamp(28px,4.2vw,42px);line-height:1.07;letter-spacing:-1px;margin:14px 0 13px;position:relative;z-index:1}
  .hiw-hero h1 em{font-style:normal;color:var(--em-deep)}
  .hiw-lead{font-size:15.5px;color:var(--muted);max-width:44ch;margin:0 0 24px;line-height:1.6;position:relative;z-index:1}
  .hiw-cta{display:flex;gap:12px;flex-wrap:wrap;position:relative;z-index:1}
  .hiw-btn{display:inline-flex;align-items:center;gap:9px;height:46px;padding:0 22px;border-radius:12px;font-size:14px;font-weight:600;text-decoration:none;border:1px solid transparent;cursor:pointer}
  .hiw-btn.p{background:var(--em);color:#fff;box-shadow:0 12px 26px -12px rgba(5,150,105,.7)}
  .hiw-btn.o{background:var(--paper);color:var(--ink);border-color:var(--line)}
  .hiw-btn svg{width:16px;height:16px}
  .hiw-scene{position:relative;z-index:1;display:flex;align-items:center;justify-content:center}
  .hiw-scene svg{width:100%;height:auto;max-width:360px;overflow:visible}
  .hiw .coin{transform-origin:center;animation:hiwrise 3.2s ease-in-out infinite}
  .hiw .coin.c2{animation-delay:.5s}.hiw .coin.c3{animation-delay:1s}
  @keyframes hiwrise{0%,100%{transform:translateY(0);opacity:.9}50%{transform:translateY(-9px);opacity:1}}
  .hiw .grow{transform-origin:bottom;transform:scaleY(0);animation:hiwgrow .9s cubic-bezier(.2,.7,.3,1) forwards}
  @keyframes hiwgrow{to{transform:scaleY(1)}}
  .hiw .trend{stroke-dasharray:340;stroke-dashoffset:340;animation:hiwdraw 2s ease-out .5s forwards}
  @keyframes hiwdraw{to{stroke-dashoffset:0}}

  .hiw-idea{display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin:34px 0}
  @media(max-width:760px){.hiw-idea{grid-template-columns:1fr}}
  .hiw-idea .c{background:var(--paper);border:1px solid var(--line);border-radius:16px;padding:22px}
  .hiw-idea .ic{width:42px;height:42px;border-radius:12px;background:var(--em-tint);color:var(--em-deep);display:flex;align-items:center;justify-content:center;margin-bottom:13px}
  .hiw-idea .ic svg{width:21px;height:21px}
  .hiw-idea h4{font-size:15px;font-weight:600;margin:0 0 5px}
  .hiw-idea p{font-size:13px;color:var(--muted);margin:0;line-height:1.6}

  .hiw-life{background:var(--paper);border:1px solid var(--line);border-radius:22px;padding:32px 32px 28px;margin:34px 0;box-shadow:var(--shadow)}
  .hiw-flow{display:grid;grid-template-columns:repeat(5,1fr);gap:8px;margin-top:24px}
  @media(max-width:820px){.hiw-flow{grid-template-columns:1fr 1fr}}
  @media(max-width:460px){.hiw-flow{grid-template-columns:1fr}}
  .hiw-node{text-align:center;padding:6px 8px;position:relative}
  .hiw-node .ring{width:64px;height:64px;border-radius:20px;margin:0 auto 13px;display:flex;align-items:center;justify-content:center;background:linear-gradient(150deg,var(--em-bright),var(--em-deep));box-shadow:0 12px 26px -12px rgba(5,150,105,.6)}
  .hiw-node:nth-child(3) .ring{background:linear-gradient(150deg,#F0B94A,#C9821B);box-shadow:0 12px 26px -12px rgba(201,130,27,.55)}
  .hiw-node .ring svg{width:29px;height:29px;color:#fff}
  .hiw-node .st{position:absolute;top:-6px;right:calc(50% - 44px);width:22px;height:22px;border-radius:50%;background:var(--paper);border:2px solid var(--line);font-size:11px;font-weight:700;color:var(--muted);display:flex;align-items:center;justify-content:center}
  .hiw-node h4{font-size:14px;font-weight:600;margin:0 0 5px}
  .hiw-node p{font-size:12px;color:var(--muted);margin:0;line-height:1.5}
  .hiw-node::after{content:"";position:absolute;top:32px;right:-4px;width:8px;height:8px;border-top:2px solid var(--line);border-right:2px solid var(--line);transform:rotate(45deg)}
  .hiw-node:nth-child(5)::after{display:none}
  @media(max-width:820px){.hiw-node::after{display:none}}

  .hiw-jrny{display:grid;grid-template-columns:1fr 1fr;gap:16px}
  @media(max-width:700px){.hiw-jrny{grid-template-columns:1fr}}
  .hiw-jstep{display:flex;gap:16px;background:var(--paper);border:1px solid var(--line);border-radius:16px;padding:20px 22px;align-items:flex-start}
  .hiw-jstep .n{flex-shrink:0;width:40px;height:40px;border-radius:12px;background:var(--navy);color:#fff;font-family:'Fraunces',serif;font-size:17px;font-weight:600;display:flex;align-items:center;justify-content:center}
  .hiw-jstep h4{font-size:15px;font-weight:600;margin:2px 0 5px;display:flex;align-items:center;gap:8px}
  .hiw-jstep h4 svg{width:16px;height:16px;color:var(--em-deep);flex-shrink:0}
  .hiw-jstep p{font-size:13px;color:var(--muted);margin:0;line-height:1.6}
  .hiw-jstep .tag{display:inline-block;font-size:11px;font-weight:600;color:var(--em-deep);background:var(--em-tint);padding:2px 9px;border-radius:99px;margin-top:8px}

  .hiw-ret{display:grid;grid-template-columns:1fr .9fr;gap:32px;align-items:center;background:var(--paper);border:1px solid var(--line);border-radius:22px;padding:32px;margin:34px 0;box-shadow:var(--shadow)}
  @media(max-width:800px){.hiw-ret{grid-template-columns:1fr;gap:22px}}
  .hiw-ret h2 em{font-style:normal;color:var(--em-deep)}
  .hiw-callout{background:var(--em-tint);border:1px solid color-mix(in srgb,var(--em) 22%,transparent);border-radius:12px;padding:13px 16px;font-size:13.5px;color:var(--em-deep);margin:14px 0 0;line-height:1.55}
  .hiw-callout b{font-weight:700}
  .hiw-calc{background:var(--soft);border:1px solid var(--line);border-radius:16px;padding:22px}
  .hiw-calc .row{display:flex;justify-content:space-between;align-items:center;font-size:13.5px;padding:9px 0;color:var(--muted)}
  .hiw-calc .row b{color:var(--ink);font-weight:600;font-variant-numeric:tabular-nums}
  .hiw-calc .row.tot{border-top:1px dashed var(--line);margin-top:6px;padding-top:14px;font-size:15px}
  .hiw-calc .row.tot b{color:var(--em-deep);font-size:19px}
  .hiw-bars{display:flex;align-items:flex-end;gap:16px;height:96px;margin:8px 4px 20px;padding:0 4px}
  .hiw-bar{flex:1;border-radius:8px 8px 0 0;position:relative}
  .hiw-bar.inv{background:var(--navy-2)}
  .hiw-bar.ret{background:linear-gradient(180deg,var(--em-bright),var(--em-deep))}
  .hiw-bar span{position:absolute;top:-19px;left:0;right:0;text-align:center;font-size:11px;font-weight:700;color:var(--ink)}
  .hiw-bar small{position:absolute;bottom:-20px;left:0;right:0;text-align:center;font-size:10.5px;color:var(--faint);font-weight:500}

  .hiw-trust{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
  @media(max-width:760px){.hiw-trust{grid-template-columns:1fr}}
  .hiw-tcard{background:var(--paper);border:1px solid var(--line);border-radius:16px;padding:22px}
  .hiw-tcard .ic{width:40px;height:40px;border-radius:11px;display:flex;align-items:center;justify-content:center;margin-bottom:12px;background:var(--em-tint);color:var(--em-deep)}
  .hiw-tcard .ic svg{width:20px;height:20px}
  .hiw-tcard h4{font-size:14.5px;font-weight:600;margin:0 0 5px}
  .hiw-tcard p{font-size:12.5px;color:var(--muted);margin:0;line-height:1.6}

  .hiw-faq{margin:0;background:var(--paper);border:1px solid var(--line);border-radius:18px;overflow:hidden}
  .hiw-fitem{border-top:1px solid var(--line)}
  .hiw-fitem:first-child{border-top:none}
  .hiw-fq{width:100%;text-align:left;background:none;border:none;padding:18px 22px;font-family:inherit;font-size:14.5px;font-weight:600;color:var(--ink);cursor:pointer;display:flex;justify-content:space-between;align-items:center;gap:14px}
  .hiw-fq .pm{width:22px;height:22px;flex-shrink:0;position:relative}
  .hiw-fq .pm::before,.hiw-fq .pm::after{content:"";position:absolute;background:var(--em-deep);border-radius:2px;transition:.2s}
  .hiw-fq .pm::before{top:10px;left:4px;right:4px;height:2px}
  .hiw-fq .pm::after{left:10px;top:4px;bottom:4px;width:2px}
  .hiw-fitem.open .pm::after{transform:scaleY(0)}
  .hiw-fa{max-height:0;overflow:hidden;transition:max-height .25s ease}
  .hiw-fa p{margin:0;padding:0 22px 18px;font-size:13.5px;color:var(--muted);line-height:1.65}
  .hiw-fitem.open .hiw-fa{max-height:220px}

  .hiw-risk{background:var(--navy);border:1px solid rgba(255,255,255,.08);border-radius:16px;padding:22px 26px;margin:34px 0 0;position:relative;overflow:hidden}
  .hiw-risk::before{content:"";position:absolute;inset:0;background:radial-gradient(70% 120% at 0 0,rgba(16,185,129,.12),transparent 60%);pointer-events:none}
  .hiw-risk-h{display:flex;align-items:center;gap:9px;font-size:10.5px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:var(--em-bright);margin-bottom:9px;position:relative}
  .hiw-risk-h svg{width:15px;height:15px}
  .hiw-risk p{margin:0;font-size:11.5px;line-height:1.75;color:rgba(255,255,255,.6);max-width:80ch;position:relative}
</style>

<div class="hiw">

  <!-- HERO -->
  <section class="hiw-hero">
    <div>
      <span class="hiw-eyebrow"><span class="ln"></span>How it works</span>
      <h1>Own real estate — <em>without</em> owning the headaches.</h1>
      <p class="hiw-lead">We buy properties, renovate them, and let you invest from as little as <b><?= $minStr ?></b>. You earn; we handle the contractors, tenants and paperwork. Here's exactly how it works.</p>
      <div class="hiw-cta">
        <a class="hiw-btn p" href="/investor/investments">Browse opportunities <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
        <a class="hiw-btn o" href="/investor/calculator">Open calculator</a>
      </div>
    </div>
    <div class="hiw-scene">
      <svg viewBox="0 0 360 320" fill="none">
        <ellipse cx="180" cy="290" rx="150" ry="20" fill="var(--em)" opacity=".08"/>
        <rect x="96" y="120" width="120" height="160" rx="10" fill="var(--navy-2)"/>
        <rect x="112" y="140" width="24" height="24" rx="4" fill="var(--em-bright)" opacity=".85"/>
        <rect x="148" y="140" width="24" height="24" rx="4" fill="#3b4a68"/>
        <rect x="184" y="140" width="18" height="24" rx="4" fill="#3b4a68"/>
        <rect x="112" y="176" width="24" height="24" rx="4" fill="#3b4a68"/>
        <rect x="148" y="176" width="24" height="24" rx="4" fill="var(--em-bright)" opacity=".85"/>
        <rect x="184" y="176" width="18" height="24" rx="4" fill="#3b4a68"/>
        <rect x="112" y="212" width="24" height="24" rx="4" fill="#3b4a68"/>
        <rect x="148" y="212" width="24" height="24" rx="4" fill="#3b4a68"/>
        <rect x="184" y="212" width="18" height="24" rx="4" fill="var(--em-bright)" opacity=".85"/>
        <rect x="140" y="250" width="34" height="30" rx="4" fill="#0a1120"/>
        <rect x="96" y="112" width="120" height="12" rx="6" fill="var(--em-deep)"/>
        <g transform="translate(210,150)">
          <rect x="0" y="0" width="120" height="96" rx="12" fill="var(--paper)" stroke="var(--line)"/>
          <rect class="grow" style="animation-delay:.1s" x="14" y="56" width="14" height="28" rx="3" fill="var(--em)" opacity=".35"/>
          <rect class="grow" style="animation-delay:.3s" x="36" y="44" width="14" height="40" rx="3" fill="var(--em)" opacity=".5"/>
          <rect class="grow" style="animation-delay:.5s" x="58" y="30" width="14" height="54" rx="3" fill="var(--em)" opacity=".7"/>
          <rect class="grow" style="animation-delay:.7s" x="80" y="16" width="14" height="68" rx="3" fill="var(--em)"/>
          <path class="trend" d="M20 60 L43 48 L65 34 L88 20" stroke="var(--gold)" stroke-width="3" stroke-linecap="round" fill="none"/>
          <circle cx="88" cy="20" r="4" fill="var(--gold)"/>
        </g>
        <g class="coin"><circle cx="70" cy="90" r="18" fill="var(--gold)"/><text x="70" y="96" font-size="18" font-weight="700" fill="#fff" text-anchor="middle"><?= htmlspecialchars($sym) ?></text></g>
        <g class="coin c2"><circle cx="300" cy="92" r="14" fill="var(--gold)" opacity=".9"/><text x="300" y="97" font-size="14" font-weight="700" fill="#fff" text-anchor="middle"><?= htmlspecialchars($sym) ?></text></g>
        <g class="coin c3"><circle cx="255" cy="60" r="11" fill="var(--gold)" opacity=".8"/></g>
      </svg>
    </div>
  </section>

  <!-- IDEA -->
  <div class="hiw-idea">
    <div class="c"><div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M5 21V8l7-5 7 5v13"/><path d="M9 21v-6h6v6"/></svg></div><h4>We do the hard part</h4><p>Sourcing, renovating, tenants, sale or lease — our team manages the entire property so you don't have to.</p></div>
    <div class="c"><div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1v22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div><h4>Start from <?= $minStr ?></h4><p>No need for tens of thousands. Fund a share of a real project with a small amount and grow from there.</p></div>
    <div class="c"><div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg></div><h4>Clear terms, upfront</h4><p>Return, duration and payout schedule are shown before you invest — and fixed the moment you commit.</p></div>
  </div>

  <!-- LIFECYCLE -->
  <div class="hiw-label"><span class="hiw-eyebrow"><span class="ln"></span>The model</span></div>
  <div class="hiw-life">
    <h2 class="hiw-sec-h">Where your money actually goes</h2>
    <p class="hiw-sec-p">Behind every opportunity is a real property moving through five stages. You invest at stage 3 — we run the rest.</p>
    <div class="hiw-flow">
      <div class="hiw-node"><span class="st">1</span><div class="ring"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M5 21V8l7-5 7 5v13"/><circle cx="12" cy="10" r="1.5" fill="currentColor"/></svg></div><h4>We acquire</h4><p>Buy under-valued or older properties with strong upside.</p></div>
      <div class="hiw-node"><span class="st">2</span><div class="ring"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 6l4 4M3 21l4-1 11-11-3-3L4 17l-1 4z"/><path d="M18 2l4 4"/></svg></div><h4>We renovate</h4><p>Our contractors upgrade and modernise each unit.</p></div>
      <div class="hiw-node"><span class="st">3</span><div class="ring"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></div><h4>You invest</h4><p>Fund the project from <?= $minStr ?> and get a certificate.</p></div>
      <div class="hiw-node"><span class="st">4</span><div class="ring"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg></div><h4>We manage &amp; exit</h4><p>Sell, rent or lease — we handle everything end to end.</p></div>
      <div class="hiw-node"><span class="st">5</span><div class="ring"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg></div><h4>You're paid</h4><p>Capital + profit back, usually in about 2–3 months.</p></div>
    </div>
  </div>

  <!-- JOURNEY -->
  <div class="hiw-label"><span class="hiw-eyebrow"><span class="ln"></span>Your journey</span></div>
  <h2 class="hiw-sec-h" style="margin-bottom:16px">Five steps, inside your dashboard</h2>
  <div class="hiw-jrny">
    <div class="hiw-jstep"><div class="n">1</div><div>
      <?php if ($kycOn): ?>
        <h4><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l8 4v6c0 5-3.5 8-8 10-4.5-2-8-5-8-10V6z"/><path d="M9 12l2 2 4-4"/></svg>Verify your identity</h4>
        <p>A quick one-time KYC check keeps every account secure and unlocks investing. Takes about two minutes.</p>
      <?php else: ?>
        <h4><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></svg>Complete your profile</h4>
        <p>Add your details on the Profile page so your certificates and records are accurate.</p>
      <?php endif; ?>
    </div></div>
    <div class="hiw-jstep"><div class="n">2</div><div><h4><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="6" width="20" height="13" rx="2.5"/><path d="M16 12h.01M2 10h20"/></svg>Fund your wallet</h4><p>Add funds by <?= htmlspecialchars($methodStr) ?>. Your balance is what you invest with.</p><span class="tag">From <?= $minStr ?> minimum</span></div></div>
    <div class="hiw-jstep"><div class="n">3</div><div><h4><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="M7 14l4-4 3 3 5-6"/></svg>Choose an opportunity</h4><p>Browse open properties, each showing its total return, duration and payout schedule. Pick one and confirm.</p></div></div>
    <div class="hiw-jstep"><div class="n">4</div><div><h4><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1v22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>Earn on schedule</h4><p>Returns land in your wallet on the plan's schedule — daily, monthly or at maturity — and show in Transactions.</p></div></div>
    <div class="hiw-jstep" style="grid-column:1/-1"><div class="n">5</div><div><h4><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v12"/><path d="M8 11l4 4 4-4"/><path d="M5 21h14"/></svg>Withdraw any time</h4><p>Your capital returns at maturity and sits in your wallet with your earnings. Withdraw whenever you like to your chosen method (minimum <?= $fmt($minWithdraw) ?>) — requests are reviewed before release.</p></div></div>
  </div>

  <!-- RETURNS -->
  <div class="hiw-label"><span class="hiw-eyebrow"><span class="ln"></span>Understand your returns</span></div>
  <div class="hiw-ret">
    <div>
      <h2 class="hiw-sec-h">The % you see is the <em>total</em>, not yearly.</h2>
      <p class="hiw-sec-p">If a plan shows <?= $roiStr ?>% over its term, that's what you earn across the whole period — the payout frequency only changes how often it reaches you, never the total.</p>
      <div class="hiw-callout"><b>Your capital comes back too.</b> At maturity your original amount returns to your wallet, on top of the returns already paid.</div>
    </div>
    <div class="hiw-calc">
      <div class="hiw-bars">
        <div class="hiw-bar inv grow" style="height:<?= $invBarH ?>%;animation-delay:.1s"><span><?= $fmt($base) ?></span><small>You invest</small></div>
        <div class="hiw-bar ret grow" style="height:96%;animation-delay:.35s"><span><?= $fmt($tot) ?></span><small>Back to you</small></div>
      </div>
      <div class="hiw-calc-rows">
        <div class="row"><span>You invest</span><b><?= $fmt($base) ?></b></div>
        <div class="row"><span>Total return (<?= $roiStr ?>%)</span><b>+<?= $fmt($ret) ?></b></div>
        <div class="row"><span>Capital returned at maturity</span><b><?= $fmt($base) ?></b></div>
        <div class="row tot"><span>Total back in your wallet</span><b><?= $fmt($tot) ?></b></div>
      </div>
    </div>
  </div>

  <!-- TRUST -->
  <div class="hiw-label"><span class="hiw-eyebrow"><span class="ln"></span>Built on trust</span></div>
  <h2 class="hiw-sec-h" style="margin-bottom:16px">Why investors feel safe here</h2>
  <div class="hiw-trust">
    <div class="hiw-tcard"><div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h4"/></svg></div><h4>A certificate for every position</h4><p>Each investment issues a verifiable certificate with a unique reference anyone can check independently.</p></div>
    <div class="hiw-tcard"><div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v4l3 2"/></svg></div><h4>Fixed, transparent terms</h4><p>Return, duration and payout schedule are locked the moment you invest. No moving targets, no surprises.</p></div>
    <div class="hiw-tcard"><div class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg></div><h4>Bank-grade security</h4><p>Encrypted logins, optional two-factor authentication and identity checks on every account.</p></div>
  </div>

  <!-- FAQ -->
  <div class="hiw-label"><span class="hiw-eyebrow"><span class="ln"></span>Common questions</span></div>
  <h2 class="hiw-sec-h" style="margin-bottom:16px">Good to know</h2>
  <div class="hiw-faq">
    <div class="hiw-fitem open"><button class="hiw-fq" type="button">How much do I need to start? <span class="pm"></span></button><div class="hiw-fa"><p>You can begin with as little as <?= $minStr ?>. There's no need for a large lump sum — you can add more or spread across several opportunities over time.</p></div></div>
    <div class="hiw-fitem"><button class="hiw-fq" type="button">How long until I get paid back? <span class="pm"></span></button><div class="hiw-fa"><p>Most opportunities run about 2 to 3 months. Returns are paid on the schedule shown for each plan, and your capital is returned at maturity.</p></div></div>
    <div class="hiw-fitem"><button class="hiw-fq" type="button">How do I take my money out? <span class="pm"></span></button><div class="hiw-fa"><p>Returns and returned capital sit in your wallet. Withdraw any available balance from Wallet &rarr; Withdraw to your chosen method; requests are reviewed before release.</p></div></div>
  </div>

  <div class="hiw-risk">
    <div class="hiw-risk-h"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4M12 17h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg>Risk disclosure</div>
    <p>Investing carries risk, including the loss of capital. Stated returns are the targets set out in each product's terms and are not guaranteed. Values can fall as well as rise, and past performance does not indicate future results. Always read the full brief before investing, and seek independent advice if you're unsure.</p>
  </div>

</div>

<script>
  document.querySelectorAll('.hiw-fq').forEach(function(b){
    b.addEventListener('click', function(){ b.parentElement.classList.toggle('open'); });
  });
</script>
