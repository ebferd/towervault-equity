<?php
/**
 * Professional language switcher (Google Translate under the hood, fully
 * custom UI). Include with an optional $langVariant of 'light' (default) or
 * 'dark' (for dark backgrounds like the admin topbar).
 *
 *   <?php $langVariant='dark'; include ROOT.'/views/components/lang_switcher.php'; ?>
 *
 * The language list is read from Google's own combo at runtime, so every
 * language Google supports appears automatically — no hardcoded list.
 */
$langVariant = ($langVariant ?? 'light') === 'dark' ? 'dark' : 'light';
?>
<div class="nvlang<?= $langVariant === 'dark' ? ' nvlang--dark' : '' ?>" id="nvlang">
  <button type="button" class="nvlang-btn" id="nvlang-btn" aria-haspopup="listbox" aria-expanded="false" aria-label="Change language">
    <svg class="nvlang-globe" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3a15 15 0 0 1 0 18a15 15 0 0 1 0-18"/></svg>
    <span class="nvlang-current" id="nvlang-current">English</span>
    <svg class="nvlang-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
  </button>

  <div class="nvlang-menu" id="nvlang-menu" role="listbox" aria-label="Languages">
    <div class="nvlang-search">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
      <input type="text" id="nvlang-search" placeholder="Search language…" autocomplete="off" spellcheck="false"/>
    </div>
    <div class="nvlang-list" id="nvlang-list"><div class="nvlang-empty">Loading languages…</div></div>
    <div class="nvlang-foot">Translations by Google</div>
  </div>

  <!-- Google mounts its (hidden) combo here -->
  <div id="google_translate_element" aria-hidden="true"></div>
</div>

<style>
/* Hide Google's own chrome (banner, tooltip, highlight, gadget) */
.goog-te-banner-frame,.skiptranslate>iframe,.goog-te-gadget-icon{display:none!important}
body{top:0!important;position:static!important}
.goog-tooltip,.goog-tooltip:hover{display:none!important}
.goog-text-highlight{background:none!important;box-shadow:none!important;color:inherit!important}
#google_translate_element{position:absolute!important;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0);opacity:0;pointer-events:none;left:-9999px}

.nvlang{position:relative;display:inline-block;font-family:'Inter',-apple-system,system-ui,sans-serif;line-height:1}
.nvlang-btn{display:inline-flex;align-items:center;gap:7px;height:36px;padding:0 11px;border-radius:9px;
  border:1px solid #E2E6EE;background:#fff;color:#0B1120;font-size:13px;font-weight:500;cursor:pointer;
  transition:border-color .15s,box-shadow .15s,background .15s}
.nvlang-btn:hover{border-color:#C7CEDB}
.nvlang-btn:focus-visible{outline:none;box-shadow:0 0 0 3px rgba(16,185,129,.2)}
.nvlang-globe{width:16px;height:16px;color:#64748B;flex-shrink:0}
.nvlang-caret{width:13px;height:13px;color:#94A3B8;transition:transform .2s;flex-shrink:0}
.nvlang.open .nvlang-caret{transform:rotate(180deg)}
.nvlang-current{max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}

.nvlang-menu{position:absolute;top:calc(100% + 8px);right:0;width:280px;background:#fff;border:1px solid #E6E9F0;
  border-radius:13px;box-shadow:0 14px 40px rgba(11,17,32,.16);z-index:99999;overflow:hidden;
  opacity:0;visibility:hidden;transform:translateY(-6px);transition:opacity .16s ease,transform .16s ease,visibility .16s}
.nvlang.open .nvlang-menu{opacity:1;visibility:visible;transform:translateY(0)}
.nvlang-search{display:flex;align-items:center;gap:8px;padding:11px 13px;border-bottom:1px solid #EEF1F6}
.nvlang-search svg{width:15px;height:15px;color:#9AA6B8;flex-shrink:0}
.nvlang-search input{flex:1;border:none;outline:none;font-size:13px;color:#0B1120;background:none;padding:0}
.nvlang-search input::placeholder{color:#AEB7C6}
.nvlang-list{max-height:290px;overflow-y:auto;padding:6px;scrollbar-width:thin}
.nvlang-list::-webkit-scrollbar{width:8px}
.nvlang-list::-webkit-scrollbar-thumb{background:#DDE2EC;border-radius:8px;border:2px solid #fff}
.nvlang-item{display:flex;align-items:center;justify-content:space-between;gap:8px;padding:8px 11px;border-radius:8px;
  font-size:13px;color:#1E293B;cursor:pointer;transition:background .12s}
.nvlang-item:hover{background:#F4F6FA}
.nvlang-item.active{background:#ECFDF3;color:#047857;font-weight:600}
.nvlang-item .chk{width:15px;height:15px;flex-shrink:0;opacity:0}
.nvlang-item.active .chk{opacity:1}
.nvlang-empty{padding:18px 12px;text-align:center;font-size:12.5px;color:#9AA6B8}
.nvlang-foot{padding:9px 13px;border-top:1px solid #EEF1F6;font-size:10.5px;color:#9AA6B8;text-align:center;letter-spacing:.2px}

/* Dark variant — for dark headers (admin) */
.nvlang--dark .nvlang-btn{background:rgba(255,255,255,.07);border-color:rgba(255,255,255,.16);color:rgba(255,255,255,.92)}
.nvlang--dark .nvlang-btn:hover{background:rgba(255,255,255,.13);border-color:rgba(255,255,255,.3)}
.nvlang--dark .nvlang-globe{color:rgba(255,255,255,.62)}
.nvlang--dark .nvlang-caret{color:rgba(255,255,255,.5)}

@media(max-width:520px){.nvlang-menu{width:min(280px,calc(100vw - 32px))}}
</style>

<script>
(function(){
  if (window.__nvlangInit) return; window.__nvlangInit = true;

  // Load Google Translate once, with autoDisplay off (no banner)
  window.googleTranslateElementInit = function(){
    new google.translate.TranslateElement({pageLanguage:'en', autoDisplay:false}, 'google_translate_element');
  };
  if(!document.getElementById('nvlang-gt-script')){
    var s=document.createElement('script'); s.id='nvlang-gt-script';
    s.src='//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
    document.head.appendChild(s);
  }

  var wrap=document.getElementById('nvlang'),
      btn=document.getElementById('nvlang-btn'),
      list=document.getElementById('nvlang-list'),
      search=document.getElementById('nvlang-search'),
      current=document.getElementById('nvlang-current');
  var langs=[];

  function cookieLang(){
    var m=document.cookie.match(/googtrans=\/[a-zA-Z-]+\/([a-zA-Z-]+)/);
    return m ? m[1] : 'en';
  }
  var CHK='<svg class="chk" viewBox="0 0 24 24" fill="none" stroke="#047857" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';

  function render(filter){
    var cur=cookieLang(), f=(filter||'').trim().toLowerCase(), html='';
    langs.forEach(function(l){
      if(f && l.name.toLowerCase().indexOf(f)===-1) return;
      html+='<div class="nvlang-item'+(l.code===cur?' active':'')+'" data-code="'+l.code+'" role="option"><span>'+l.name+'</span>'+CHK+'</div>';
    });
    list.innerHTML = html || '<div class="nvlang-empty">No language found</div>';
    var found=langs.filter(function(l){return l.code===cur;})[0];
    current.textContent = found ? found.name : 'English';
  }

  // Every domain scope a googtrans cookie could live on: host-only, host with a
  // leading dot, and every parent domain (e.g. equity.example.com AND .example.com).
  // A cookie left on ANY of these keeps Google translating, so we must cover them all.
  function cookieScopes(){
    var parts=location.hostname.split('.'), scopes=[''];
    for(var i=0;i<parts.length-1;i++){
      var d=parts.slice(i).join('.');
      scopes.push('; domain='+d, '; domain=.'+d);
    }
    return scopes;
  }

  function setLang(code){
    // Drive Google Translate via the googtrans cookie, then reload. Persists the
    // choice across pages instead of resetting on every navigation.
    var scopes=cookieScopes(), dead='; expires=Thu, 01 Jan 1970 00:00:00 GMT';
    // 1) wipe any existing googtrans cookie on every scope (this is what makes
    //    switching back to English actually work)
    scopes.forEach(function(s){ document.cookie='googtrans='+dead+'; path=/'+s; });
    // 2) for a non-English language, set the cookie so Google translates on reload
    if(code!=='en'){
      var v='googtrans=/en/'+code+'; path=/';
      scopes.forEach(function(s){ document.cookie=v+s; });
    }
    close();
    location.reload();
  }

  function populate(tries){
    var combo=document.querySelector('.goog-te-combo');
    if(!combo || combo.options.length<2){ if((tries||0)<40) return setTimeout(function(){populate((tries||0)+1);},250); return; }
    langs=[{code:'en',name:'English'}];
    Array.prototype.forEach.call(combo.options,function(o){ if(o.value) langs.push({code:o.value,name:o.text}); });
    var seen={}; langs=langs.filter(function(l){ if(seen[l.code])return false; seen[l.code]=true; return true; });
    render('');
  }
  populate(0);

  function open(){ wrap.classList.add('open'); btn.setAttribute('aria-expanded','true'); search.value=''; render(''); setTimeout(function(){search.focus();},60); }
  function close(){ wrap.classList.remove('open'); btn.setAttribute('aria-expanded','false'); }

  btn.addEventListener('click',function(e){ e.stopPropagation(); wrap.classList.contains('open')?close():open(); });
  list.addEventListener('click',function(e){ var it=e.target.closest('.nvlang-item'); if(it) setLang(it.dataset.code); });
  search.addEventListener('input',function(){ render(this.value); });
  document.addEventListener('click',function(e){ if(!wrap.contains(e.target)) close(); });
  document.addEventListener('keydown',function(e){ if(e.key==='Escape') close(); });
})();
</script>
