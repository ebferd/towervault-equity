<?php $pName = platform_setting('platform_name', 'NexVest'); $pInit = platform_setting('platform_initials', 'TV'); ?>
<section class="ia">
  <div class="ia-glow"></div>
  <div class="ia-hero">
    <!-- LEFT -->
    <div class="ia-left">
      <span class="ia-eyebrow"><span class="ia-pulse"></span>Mobile app</span>
      <h1 class="ia-h1">Carry your portfolio in your <span class="ia-g">pocket.</span></h1>
      <p class="ia-lead">Install <?= htmlspecialchars($pName) ?> on your phone in seconds — full-screen, lightning-fast, and using the same secure account. No app store required.</p>

      <div class="ia-seg" role="tablist">
        <button class="ia-seg-btn on" id="ia-tab-android" type="button" onclick="iaPick('android')">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.6 9.48l1.84-3.18a.4.4 0 0 0-.7-.4l-1.86 3.23a11.5 11.5 0 0 0-9.76 0L5.26 5.9a.4.4 0 0 0-.7.4L6.4 9.48A10.8 10.8 0 0 0 1 18h22a10.8 10.8 0 0 0-5.4-8.52zM7 15.25a1 1 0 1 1 0-2 1 1 0 0 1 0 2zm10 0a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/></svg>
          Android
        </button>
        <button class="ia-seg-btn" id="ia-tab-ios" type="button" onclick="iaPick('ios')">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M16.365 12.86c-.024-2.51 2.05-3.71 2.14-3.77-1.17-1.71-2.99-1.95-3.63-1.98-1.55-.16-3.02.9-3.8.9-.78 0-1.99-.88-3.27-.86-1.68.03-3.23.98-4.1 2.48-1.75 3.03-.45 7.51 1.25 9.97.83 1.2 1.82 2.55 3.12 2.5 1.25-.05 1.72-.81 3.23-.81 1.51 0 1.93.81 3.25.78 1.34-.02 2.19-1.22 3.01-2.43.95-1.39 1.34-2.74 1.36-2.81-.03-.01-2.61-1-2.64-3.97zM13.9 5.5c.69-.83 1.15-1.99 1.02-3.14-.99.04-2.19.66-2.9 1.49-.64.73-1.2 1.9-1.05 3.02 1.1.09 2.24-.56 2.93-1.37z"/></svg>
          iPhone
        </button>
      </div>

      <!-- Android panel -->
      <div class="ia-panel" id="ia-panel-android">
        <button class="ia-btn android" id="ia-install-btn" type="button">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M12 3v12"/><path d="M8 11l4 4 4-4"/><path d="M5 21h14"/></svg>Install app
        </button>
        <div class="ia-hint" id="ia-hint" hidden></div>
        <div class="ia-steps">
          <div class="ia-step"><span class="ia-n">1</span><span class="ia-t">Tap <b>Install app</b> — a confirmation appears.</span></div>
          <div class="ia-step"><span class="ia-n">2</span><span class="ia-t">Tap <b>Install</b> in the pop-up.</span></div>
          <div class="ia-step"><span class="ia-n">3</span><span class="ia-t">The icon lands on your home screen. Open it anytime.</span></div>
        </div>
        <p class="ia-fine">No pop-up? Open this page in <b>Chrome</b> → tap the <b>⋮</b> menu → <b>Add to Home screen</b>.</p>
      </div>

      <!-- iOS panel -->
      <div class="ia-panel" id="ia-panel-ios" hidden>
        <div class="ia-ioshead">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 12v7a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-7"/><path d="M16 6l-4-4-4 4"/><path d="M12 2v13"/></svg>
          Add to your home screen in 3 taps
        </div>
        <div class="ia-steps">
          <div class="ia-step"><span class="ia-n">1</span><span class="ia-t">Tap the <span class="ia-chip"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 12v7a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-7"/><path d="M16 6l-4-4-4 4"/><path d="M12 2v13"/></svg>Share</span> icon at the bottom of Safari.</span></div>
          <div class="ia-step"><span class="ia-n">2</span><span class="ia-t">Scroll down and tap <b>Add to Home Screen</b>.</span></div>
          <div class="ia-step"><span class="ia-n">3</span><span class="ia-t">Tap <b>Add</b> — you're done.</span></div>
        </div>
        <p class="ia-fine">Opened from WhatsApp or Instagram? Tap <b>⋯</b> → <b>Open in Safari</b> first, then follow the steps.</p>
      </div>

      <!-- already installed -->
      <div class="ia-panel ia-done" id="ia-panel-done" hidden>
        <div class="ia-done-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M20 6L9 17l-5-5"/></svg></div>
        <div><div class="ia-done-h">You're all set</div><div class="ia-done-s">The app is installed on this device — open it from your home screen anytime.</div></div>
      </div>
    </div>

    <!-- RIGHT -->
    <div class="ia-stage">
      <div class="ia-phone"><div class="ia-screen">
        <div class="ia-s-top"><div class="ia-s-mark"><?= htmlspecialchars(mb_substr($pInit,0,2)) ?></div><div class="ia-s-mark-t"><?= htmlspecialchars($pName) ?></div></div>
        <div class="ia-s-bal">
          <div class="ia-s-l">Portfolio value</div>
          <div class="ia-s-v">$48,250.00</div>
          <div class="ia-s-d">&#9650; $1,240 &nbsp;·&nbsp; this month</div>
        </div>
        <div class="ia-s-rows">
          <div class="ia-s-row"><span class="ia-s-i"></span><span class="ia-s-b"><i style="width:72%"></i><i style="width:42%"></i></span></div>
          <div class="ia-s-row"><span class="ia-s-i"></span><span class="ia-s-b"><i style="width:58%"></i><i style="width:34%"></i></span></div>
          <div class="ia-s-row"><span class="ia-s-i"></span><span class="ia-s-b"><i style="width:66%"></i><i style="width:30%"></i></span></div>
        </div>
        <div class="ia-s-nav"><span class="on"></span><span></span><span></span><span></span></div>
      </div></div>

      <div class="ia-qr">
        <div class="ia-code" id="ia-qrcode"></div>
        <div class="ia-qr-txt">
          <div class="ia-qr-l">On a computer?</div>
          <div class="ia-qr-s">Scan this with your phone's camera to open the app there.</div>
        </div>
      </div>
    </div>
  </div>
</section>

<style>
  .ia{position:relative;overflow:hidden;border-radius:22px;background:#070B14;color:#EDF3F1;
    padding:40px 40px 46px;margin:2px 0 8px;
    background-image:radial-gradient(70% 55% at 85% -10%,rgba(16,185,129,.22),transparent 60%),radial-gradient(50% 40% at -5% 105%,rgba(16,185,129,.10),transparent 60%)}
  .ia *{box-sizing:border-box}
  .ia-hero{position:relative;z-index:1;display:grid;grid-template-columns:1fr .9fr;gap:46px;align-items:center}
  @media(max-width:900px){.ia{padding:30px 22px 34px}.ia-hero{grid-template-columns:1fr;gap:34px}}
  .ia-eyebrow{display:inline-flex;align-items:center;gap:8px;font-size:11px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:#34D399}
  .ia-pulse{width:7px;height:7px;border-radius:50%;background:#34D399;box-shadow:0 0 0 0 rgba(52,211,153,.6);animation:ia-pulse 2.4s infinite}
  @keyframes ia-pulse{0%{box-shadow:0 0 0 0 rgba(52,211,153,.5)}70%{box-shadow:0 0 0 9px rgba(52,211,153,0)}100%{box-shadow:0 0 0 0 rgba(52,211,153,0)}}
  @media(prefers-reduced-motion:reduce){.ia-pulse{animation:none}}
  .ia-h1{font-family:'Inter',system-ui,sans-serif;font-size:clamp(30px,4.4vw,46px);font-weight:800;line-height:1.03;letter-spacing:-1.4px;margin:18px 0 0}
  .ia-g{background:linear-gradient(120deg,#34D399,#7af0c4);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent}
  .ia-lead{font-size:15.5px;color:#8B98A6;max-width:44ch;margin:16px 0 26px;line-height:1.65}
  .ia-seg{display:inline-flex;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);border-radius:13px;padding:4px;gap:4px;margin-bottom:20px}
  .ia-seg-btn{appearance:none;border:none;background:none;cursor:pointer;font-family:inherit;color:#8B98A6;font-size:13.5px;font-weight:600;padding:9px 18px;border-radius:9px;display:inline-flex;align-items:center;gap:8px;transition:.18s}
  .ia-seg-btn svg{width:16px;height:16px}
  .ia-seg-btn.on{background:#fff;color:#0B1424}
  .ia-panel{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);border-radius:18px;padding:22px 22px 20px;max-width:440px;backdrop-filter:blur(6px)}
  .ia-btn{display:inline-flex;align-items:center;justify-content:center;gap:10px;width:100%;height:52px;border:none;border-radius:13px;font-family:inherit;font-size:15px;font-weight:700;cursor:pointer;letter-spacing:.1px}
  .ia-btn svg{width:19px;height:19px}
  .ia-btn.android{background:#10B981;color:#fff;box-shadow:0 14px 30px -12px rgba(16,185,129,.7)}
  .ia-btn.android:disabled{opacity:.55;cursor:default;box-shadow:none}
  .ia-hint{font-size:12.5px;color:#c9d3cf;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.09);border-radius:10px;padding:10px 12px;margin-top:12px;line-height:1.5}
  .ia-ioshead{display:flex;align-items:center;gap:10px;font-size:14px;font-weight:600;color:#EDF3F1;padding:2px 0 4px}
  .ia-ioshead svg{width:20px;height:20px;color:#34D399}
  .ia-steps{margin:18px 0 0;display:flex;flex-direction:column;gap:13px}
  .ia-step{display:flex;gap:12px;align-items:flex-start}
  .ia-n{width:23px;height:23px;border-radius:8px;background:rgba(16,185,129,.16);color:#34D399;font-size:12px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-variant-numeric:tabular-nums}
  .ia-t{font-size:13.5px;color:#dfe7e3;line-height:1.55}
  .ia-t b{font-weight:600;color:#fff}
  .ia-chip{display:inline-flex;align-items:center;gap:5px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.09);border-radius:6px;padding:1px 7px;vertical-align:-2px;font-weight:600}
  .ia-chip svg{width:12px;height:12px;color:#8B98A6}
  .ia-fine{font-size:11.5px;color:#5E6C7A;margin:15px 0 0;line-height:1.5}
  .ia-fine b{color:#8B98A6}
  .ia-done{display:flex;align-items:center;gap:14px}
  .ia-done-ic{width:42px;height:42px;border-radius:12px;background:rgba(16,185,129,.16);color:#34D399;display:flex;align-items:center;justify-content:center;flex-shrink:0}
  .ia-done-ic svg{width:22px;height:22px}
  .ia-done-h{font-size:15px;font-weight:700}
  .ia-done-s{font-size:12.5px;color:#8B98A6;margin-top:3px;line-height:1.5}

  .ia-stage{display:flex;flex-direction:column;align-items:center;gap:20px}
  .ia-phone{width:224px;height:452px;border-radius:38px;background:#05080f;padding:12px;position:relative;box-shadow:0 40px 80px -30px rgba(16,185,129,.55),0 0 0 1px rgba(255,255,255,.14)}
  .ia-phone::before{content:"";position:absolute;top:22px;left:50%;transform:translateX(-50%);width:60px;height:6px;border-radius:99px;background:rgba(255,255,255,.14);z-index:3}
  .ia-screen{width:100%;height:100%;border-radius:28px;overflow:hidden;position:relative;display:flex;flex-direction:column;background:linear-gradient(180deg,#0B1120,#0C2A22 55%,#0a3a2b)}
  .ia-s-top{padding:32px 17px 0;display:flex;align-items:center;gap:9px}
  .ia-s-mark{width:23px;height:23px;border-radius:7px;background:linear-gradient(135deg,#34D399,#059669);display:flex;align-items:center;justify-content:center;color:#04140d;font-weight:800;font-size:9px}
  .ia-s-mark-t{font-size:11.5px;font-weight:600;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:120px}
  .ia-s-bal{margin:17px 15px 0;background:linear-gradient(135deg,rgba(16,185,129,.95),rgba(4,120,87,.92));border-radius:15px;padding:15px 16px;color:#fff}
  .ia-s-l{font-size:8.5px;letter-spacing:.09em;text-transform:uppercase;color:rgba(255,255,255,.72)}
  .ia-s-v{font-size:25px;font-weight:800;letter-spacing:-.6px;margin-top:3px;font-variant-numeric:tabular-nums}
  .ia-s-d{font-size:10px;color:#c7f9e5;margin-top:4px}
  .ia-s-rows{padding:14px 15px 0;display:flex;flex-direction:column;gap:9px}
  .ia-s-row{background:rgba(255,255,255,.06);border-radius:11px;height:40px;display:flex;align-items:center;gap:10px;padding:0 12px}
  .ia-s-i{width:23px;height:23px;border-radius:7px;background:rgba(16,185,129,.22);flex-shrink:0}
  .ia-s-b{flex:1;display:flex;flex-direction:column;gap:5px}
  .ia-s-b i{height:6px;border-radius:4px;background:rgba(255,255,255,.2)}
  .ia-s-nav{margin-top:auto;height:48px;background:rgba(0,0,0,.4);display:flex;align-items:center;justify-content:space-around;padding:0 12px}
  .ia-s-nav span{width:21px;height:21px;border-radius:7px;background:rgba(255,255,255,.13)}
  .ia-s-nav span.on{background:#34D399}

  .ia-qr{display:flex;align-items:center;gap:14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);border-radius:15px;padding:12px 16px 12px 12px}
  .ia-code{width:72px;height:72px;background:#fff;border-radius:9px;padding:6px;flex-shrink:0;display:flex;align-items:center;justify-content:center}
  .ia-code canvas,.ia-code img{width:100%!important;height:100%!important;display:block}
  .ia-qr-l{font-size:13px;font-weight:600}
  .ia-qr-s{font-size:11.5px;color:#8B98A6;margin-top:3px;max-width:22ch;line-height:1.45}
  @media(max-width:900px){.ia-qr{display:none}}
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
(function(){
  var tabA = document.getElementById('ia-tab-android'), tabI = document.getElementById('ia-tab-ios');
  var panA = document.getElementById('ia-panel-android'), panI = document.getElementById('ia-panel-ios'), panDone = document.getElementById('ia-panel-done');
  var installBtn = document.getElementById('ia-install-btn'), hint = document.getElementById('ia-hint');
  var isIOS = /iphone|ipad|ipod/i.test(navigator.userAgent);
  var standalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;

  window.iaPick = function(which){
    var a = which === 'android';
    tabA.classList.toggle('on', a); tabI.classList.toggle('on', !a);
    panA.hidden = !a || standalone; panI.hidden = a || standalone;
  };

  if (standalone) {
    panA.hidden = true; panI.hidden = true; panDone.hidden = false;
  } else {
    iaPick(isIOS ? 'ios' : 'android');
  }

  function refreshAndroidBtn(){
    if (window._pwaPrompt) { installBtn.disabled = false; hint.hidden = true; }
    else { installBtn.disabled = true; hint.hidden = false;
      hint.innerHTML = 'Your browser will show the button as soon as it’s ready. If it stays greyed out, open this page in <b>Chrome</b> and use the ⋮ menu → <b>Add to Home screen</b>.'; }
  }
  if (!standalone && !isIOS) refreshAndroidBtn();
  window.addEventListener('pwa-available', refreshAndroidBtn);

  installBtn.addEventListener('click', async function(){
    if (!window._pwaPrompt) { refreshAndroidBtn(); return; }
    window._pwaPrompt.prompt();
    try { await window._pwaPrompt.userChoice; } catch(e){}
    window._pwaPrompt = null;
  });
  window.addEventListener('pwa-installed', function(){ panA.hidden = true; panI.hidden = true; panDone.hidden = false; });

  // scan-to-install QR — points at this page on the current domain
  try {
    new QRCode(document.getElementById('ia-qrcode'), {
      text: window.location.origin + '/investor/install',
      width: 120, height: 120, colorDark: '#0B1120', colorLight: '#ffffff', correctLevel: QRCode.CorrectLevel.M
    });
  } catch(e){}
})();
</script>
