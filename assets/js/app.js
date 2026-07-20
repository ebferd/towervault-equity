/* ============================================================
   NexVest — Enhanced Global JavaScript v3.0
   ============================================================ */

'use strict';

// ── CSRF helper ───────────────────────────────────────────────
const CSRF = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

// ── Copy to clipboard (with iOS/old-browser fallback) ─────────
function copyText(text, btn) {
  const showCopied = () => {
    if (!btn) return;
    const orig = btn.innerHTML;
    btn.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Copied!';
    setTimeout(() => btn.innerHTML = orig, 2000);
  };
  if (navigator.clipboard && window.isSecureContext) {
    navigator.clipboard.writeText(text.trim()).then(showCopied).catch(() => fallbackCopy(text, showCopied));
  } else {
    fallbackCopy(text, showCopied);
  }
}
function fallbackCopy(text, cb) {
  const ta = document.createElement('textarea');
  ta.value = text; ta.style.cssText = 'position:fixed;opacity:0;top:0;left:0';
  document.body.appendChild(ta); ta.focus(); ta.select();
  try { document.execCommand('copy'); cb?.(); } catch(e) {}
  document.body.removeChild(ta);
}

// ── Ajax POST helper ──────────────────────────────────────────
async function post(url, data = {}, isFormData = false) {
  const opts = {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': CSRF(), 'X-Requested-With': 'XMLHttpRequest' },
  };
  if (isFormData) {
    if (data && typeof data.append === 'function') data.append('_token', CSRF());
    opts.body = data;
  } else {
    opts.headers['Content-Type'] = 'application/json';
    opts.body = JSON.stringify({ ...data, _token: CSRF() });
  }
  try {
    const res = await fetch(url, opts);
    const text = await res.text();
    try {
      return JSON.parse(text);
    } catch {
      // Server returned non-JSON (PHP error page) — surface a clean error
      console.error('Non-JSON response from', url, ':', text.slice(0, 300));
      return { success: false, error: 'A server error occurred. Please refresh and try again.' };
    }
  } catch (networkErr) {
    console.error('Network error:', networkErr);
    return { success: false, error: 'Network error. Please check your connection and try again.' };
  }
}

// ── Enhanced Flash message ────────────────────────────────────
const flashIcons = {
  ok:   `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>`,
  err:  `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>`,
  warn: `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>`,
  info: `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`,
};

function showFlash(msg, type = 'ok') {
  const el = document.createElement('div');
  el.className = `alert alert-${type}`;
  el.style.cssText = `
    position:fixed;top:74px;right:1.5rem;z-index:9999;
    max-width:380px;min-width:260px;
    box-shadow:0 8px 32px rgba(0,0,0,.14),0 2px 8px rgba(0,0,0,.08);
    animation:slideIn .3s cubic-bezier(.34,1.56,.64,1) both;
    display:flex;align-items:flex-start;gap:10px;
    border-radius:10px;padding:11px 14px;font-size:13px;cursor:pointer;
  `;
  el.innerHTML = `${flashIcons[type] || flashIcons.info}<span style="flex:1">${msg}</span>`;
  el.addEventListener('click', () => el.remove());
  document.body.appendChild(el);
  setTimeout(() => {
    el.style.animation = 'slideIn .25s cubic-bezier(.4,0,.2,1) reverse both';
    setTimeout(() => el.remove(), 250);
  }, 4500);
}

// ── Loading state on buttons ──────────────────────────────────
function setLoading(btn, loading, label = null) {
  if (loading) {
    btn.dataset.originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="animation:spin .7s linear infinite;flex-shrink:0"><path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0" stroke-dasharray="56" stroke-dashoffset="56"/></svg>&nbsp;${label || 'Processing…'}`;
  } else {
    btn.disabled = false;
    btn.innerHTML = btn.dataset.originalText;
  }
}

// ── Animated number counter ───────────────────────────────────
function animateValue(el, start, end, duration = 900, formatter = null) {
  const startTime = performance.now();
  const easeOut = t => 1 - Math.pow(1 - t, 3);
  const update = (now) => {
    const elapsed = now - startTime;
    const progress = Math.min(elapsed / duration, 1);
    const current = start + (end - start) * easeOut(progress);
    el.textContent = formatter ? formatter(current) : Math.round(current).toLocaleString();
    if (progress < 1) requestAnimationFrame(update);
    else el.textContent = formatter ? formatter(end) : end.toLocaleString();
  };
  requestAnimationFrame(update);
}

// ── Ripple effect on buttons ──────────────────────────────────
function addRipple(e) {
  const btn = e.currentTarget;
  const ripple = document.createElement('span');
  const rect = btn.getBoundingClientRect();
  const size = Math.max(rect.width, rect.height);
  const x = e.clientX - rect.left - size / 2;
  const y = e.clientY - rect.top - size / 2;
  ripple.style.cssText = `
    position:absolute;width:${size}px;height:${size}px;
    left:${x}px;top:${y}px;border-radius:50%;
    background:rgba(255,255,255,.25);transform:scale(0);
    animation:rippleAnim .5s linear;pointer-events:none;
  `;
  btn.appendChild(ripple);
  setTimeout(() => ripple.remove(), 520);
}

// ── Sidebar toggle ────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sb-overlay');
  const hamburger = document.getElementById('hamburger');

  if (hamburger && sidebar) {
    hamburger.addEventListener('click', () => {
      sidebar.classList.toggle('open');
      overlay?.classList.toggle('open');
    });
    overlay?.addEventListener('click', () => {
      sidebar.classList.remove('open');
      overlay.classList.remove('open');
    });
  }

  // ── Live clock ────────────────────────────────────────────
  const clockEl = document.getElementById('topbar-clock');
  if (clockEl) {
    const updateClock = () => {
      const now = new Date();
      clockEl.textContent = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    };
    updateClock();
    setInterval(updateClock, 1000);
  }

  // ── Ripple on buttons ─────────────────────────────────────
  document.querySelectorAll('.btn-primary, .btn-danger, .btn-success').forEach(btn => {
    btn.addEventListener('click', addRipple);
  });

  // ── Password show/hide ─────────────────────────────────────
  document.querySelectorAll('[data-toggle-password]').forEach(btn => {
    btn.addEventListener('click', () => {
      const input = document.getElementById(btn.dataset.togglePassword);
      if (!input) return;
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.innerHTML = show ? eyeOffIcon() : eyeIcon();
    });
  });

  // ── OTP inputs ────────────────────────────────────────────
  const otpCells = document.querySelectorAll('.otp-cell');
  otpCells.forEach((cell, i) => {
    cell.addEventListener('input', e => {
      const v = e.target.value.replace(/\D/g, '').slice(-1);
      e.target.value = v;
      if (v && i < otpCells.length - 1) otpCells[i + 1].focus();
    });
    cell.addEventListener('keydown', e => {
      if (e.key === 'Backspace' && !cell.value && i > 0) otpCells[i - 1].focus();
    });
    cell.addEventListener('paste', e => {
      e.preventDefault();
      const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, otpCells.length);
      [...pasted].forEach((ch, j) => { if (otpCells[j]) otpCells[j].value = ch; });
      const focusIdx = Math.min(pasted.length, otpCells.length - 1);
      if (otpCells[focusIdx]) otpCells[focusIdx].focus();
    });
  });

  // ── Copy buttons ───────────────────────────────────────────
  document.querySelectorAll('[data-copy]').forEach(btn => {
    btn.addEventListener('click', () => copyText(btn.dataset.copy || btn.nextElementSibling?.textContent || '', btn));
  });

  // ── Confirm dialogs ────────────────────────────────────────
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', e => {
      if (!confirm(el.dataset.confirm)) e.preventDefault();
    });
  });

  // ── Notification mark read on click ────────────────────────
  document.querySelectorAll('.notif-row[data-id]').forEach(row => {
    row.addEventListener('click', () => {
      const id = row.dataset.id;
      post('/investor/notifications/read', { id })
        .then(() => { row.classList.remove('unread'); row.style.background = ''; })
        .catch(() => {});
    });
  });

  // ── Animate stat values on load ────────────────────────────
  document.querySelectorAll('[data-animate-value]').forEach(el => {
    const target = parseFloat(el.dataset.animateValue) || 0;
    const prefix = el.dataset.prefix || '';
    const suffix = el.dataset.suffix || '';
    const decimals = parseInt(el.dataset.decimals || '0');
    animateValue(el, 0, target, 1000, v => prefix + v.toFixed(decimals) + suffix);
  });

  // ── Animate progress bars ──────────────────────────────────
  document.querySelectorAll('.prog-fill[data-pct]').forEach(fill => {
    const pct = parseFloat(fill.dataset.pct) || 0;
    requestAnimationFrame(() => {
      setTimeout(() => { fill.style.width = pct + '%'; }, 100);
    });
  });

  // ── Animate cards in on scroll ─────────────────────────────
  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.animation = 'slideUp .35s cubic-bezier(.4,0,.2,1) both';
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1 });
    document.querySelectorAll('.kpi-card, .inv-card, .stat-card').forEach(el => io.observe(el));
  }

  // ── Auto-dismiss flash messages ────────────────────────────
  document.querySelectorAll('.alert[data-dismiss]').forEach(el => {
    setTimeout(() => el.remove(), parseInt(el.dataset.dismiss) || 5000);
  });

  // ── Modal close on overlay click ───────────────────────────
  document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => {
      if (e.target === overlay) {
        overlay.style.animation = 'overlayIn .2s ease reverse both';
        setTimeout(() => { overlay.style.display = 'none'; overlay.style.animation = ''; }, 200);
      }
    });
  });

  // ── Modal close on Escape ──────────────────────────────────
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      document.querySelectorAll('.modal-overlay[style*="flex"]').forEach(overlay => {
        overlay.style.display = 'none';
      });
    }
  });

});

// ── Portfolio donut chart ─────────────────────────────────────
function initPortfolioChart(canvas, data) {
  const colors = ['#1A6DB5','#17915A','#B7791F','#6B46C1','#C0392B','#0891B2','#BE185D'];
  const ctx = canvas.getContext('2d');
  new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: data.map(d => d.name),
      datasets: [{
        data: data.map(d => d.amount),
        backgroundColor: colors.slice(0, data.length),
        borderWidth: 3,
        borderColor: '#fff',
        hoverOffset: 6,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      cutout: '72%',
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: ctx => ` ${ctx.label}: $${Number(ctx.raw).toLocaleString('en-US', {minimumFractionDigits:2})}`,
          },
          backgroundColor: '#0A1628',
          titleColor: 'rgba(255,255,255,.7)',
          bodyColor: '#fff',
          padding: 10,
          cornerRadius: 8,
        }
      },
      animation: {
        animateRotate: true,
        duration: 900,
        easing: 'easeOutQuart',
      }
    }
  });
}

// ── Activity line chart ───────────────────────────────────────
function initActivityChart(canvas, data) {
  const ctx = canvas.getContext('2d');
  const gradient = ctx.createLinearGradient(0, 0, 0, canvas.height);
  gradient.addColorStop(0, 'rgba(26,109,181,.18)');
  gradient.addColorStop(1, 'rgba(26,109,181,0)');

  new Chart(ctx, {
    type: 'line',
    data: {
      labels: data.map(d => d.label),
      datasets: [{
        data: data.map(d => d.value),
        borderColor: '#1A6DB5',
        borderWidth: 2.5,
        backgroundColor: gradient,
        fill: true,
        tension: .42,
        pointRadius: 3,
        pointHoverRadius: 5,
        pointBackgroundColor: '#1A6DB5',
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: ctx => ` $${Number(ctx.raw).toLocaleString('en-US', {minimumFractionDigits:2})}`,
          },
          backgroundColor: '#0A1628',
          bodyColor: '#fff',
          padding: 10,
          cornerRadius: 8,
        }
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: { color: '#8898B0', font: { size: 11 } },
          border: { display: false }
        },
        y: {
          grid: { color: 'rgba(219,228,239,.6)', drawTicks: false },
          ticks: {
            color: '#8898B0',
            font: { size: 11 },
            callback: v => '$' + Number(v).toLocaleString(),
            padding: 8,
          },
          border: { display: false }
        }
      },
      animation: { duration: 900, easing: 'easeOutQuart' }
    }
  });
}

// ── Form submission helper ────────────────────────────────────
function submitForm(formId, url, {
  btn = null,
  onSuccess = null,
  onError = null,
  redirect = true,
} = {}) {
  const form = typeof formId === 'string' ? document.getElementById(formId) : formId;
  if (!form) return;

  form.addEventListener('submit', async e => {
    e.preventDefault();
    const submitBtn = btn || form.querySelector('[type="submit"]');
    if (submitBtn) setLoading(submitBtn, true);
    clearErrors(form);

    try {
      const fd   = new FormData(form);
      const data = await post(url, fd, true);

      if (data.success) {
        if (onSuccess) return onSuccess(data);
        if (data.redirect) window.location.href = data.redirect;
        else if (redirect) window.location.reload();
        else showFlash(data.message || 'Saved successfully.', 'ok');
      } else {
        if (onError) return onError(data);
        if (data.errors) showFieldErrors(form, data.errors);
        else showFlash(data.error || 'An error occurred.', 'err');
      }
    } catch (err) {
      showFlash('Network error. Please try again.', 'err');
    } finally {
      if (submitBtn) setLoading(submitBtn, false);
    }
  });
}

function clearErrors(form) {
  form.querySelectorAll('.field-error').forEach(el => el.remove());
  form.querySelectorAll('.fi--error').forEach(el => el.classList.remove('fi--error'));
}
function showFieldErrors(form, errors) {
  errors.forEach(msg => showFlash(msg, 'err'));
}

// ── SVG helpers ───────────────────────────────────────────────
function eyeIcon()    { return `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>`; }
function eyeOffIcon() { return `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>`; }

function icon(name, size = 16, color = 'currentColor') {
  const icons = {
    overview:  `<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>`,
    building:  `<path d="M3 21h18M5 21V7l7-4 7 4v14"/><path d="M9 21v-6h6v6"/>`,
    chart:     `<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>`,
    briefcase: `<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>`,
    wallet:    `<path d="M20 7H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/><circle cx="17" cy="13" r="1" fill="${color}" stroke="none"/>`,
    bell:      `<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>`,
    headset:   `<path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3z"/><path d="M3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/>`,
    user:      `<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>`,
    gift:      `<polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5"/><line x1="12" y1="22" x2="12" y2="7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/>`,
    doc:       `<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>`,
    calc:      `<rect x="4" y="2" width="16" height="20" rx="2"/>`,
    arrowLR:   `<path d="M7 16V4m0 0L3 8m4-4 4 4"/><path d="M17 8v12m0 0 4-4m-4 4-4-4"/>`,
    logout:    `<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>`,
    shield:    `<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>`,
    users:     `<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>`,
    settings:  `<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>`,
    ticket:    `<path d="M21 5H3a2 2 0 0 0-2 2v3a2 2 0 0 1 0 4v3a2 2 0 0 0 2 2h18a2 2 0 0 0 2-2v-3a2 2 0 0 1 0-4V7a2 2 0 0 0-2-2z"/>`,
    log:       `<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>`,
    menu:      `<line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>`,
    check:     `<polyline points="20 6 9 17 4 12"/>`,
    x:         `<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>`,
    trendUp:   `<polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>`,
    arrowUp:   `<line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/>`,
    arrowDown: `<line x1="12" y1="5" x2="12" y2="19"/><polyline points="19 12 12 19 5 12"/>`,
    pin:       `<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>`,
    download:  `<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>`,
    send:      `<line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>`,
    copy:      `<rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>`,
    eye:       `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`,
    edit:      `<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>`,
    trash:     `<polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>`,
    login:     `<path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/>`,
    info:      `<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>`,
    filter:    `<polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>`,
    star:      `<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>`,
    bank:      `<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>`,
    qr:        `<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>`,
    clock:     `<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>`,
    refresh:   `<polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>`,
    share:     `<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>`,
    sparkline: `<polyline points="2 14 6 10 10 12 14 6 18 8 22 4"/>`,
  };
  const paths = icons[name] || '';
  return `<svg class="icon" width="${size}" height="${size}" viewBox="0 0 24 24" fill="none" stroke="${color}" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">${paths}</svg>`;
}

// ── Countdown timer ───────────────────────────────────────────
function startCountdown(expiresAt, displayEl, barEl, onExpire) {
  // Server returns UTC; append 'Z' so browser parses as UTC regardless of local timezone
  const normalized = (typeof expiresAt === 'string' ? expiresAt.replace(' ', 'T') : expiresAt.toString()) + 'Z';
  const total = new Date(normalized) - new Date();
  if (total <= 0) { onExpire?.(); return; }

  const interval = setInterval(() => {
    const left = Math.max(0, new Date(normalized) - new Date());
    const mm   = Math.floor(left / 60000);
    const ss   = Math.floor((left % 60000) / 1000);
    const pct  = (left / total) * 100;

    if (displayEl) {
      displayEl.textContent = `${String(mm).padStart(2,'0')}:${String(ss).padStart(2,'0')}`;
      displayEl.classList.toggle('urgent', left < 300000);
    }
    if (barEl) {
      barEl.style.width = pct + '%';
      barEl.style.background = left < 300000
        ? 'linear-gradient(90deg,var(--red),#e05c50)'
        : pct > 50
          ? 'linear-gradient(90deg,var(--accent),#4DA6F5)'
          : 'linear-gradient(90deg,var(--warn),#F6AD55)';
    }

    if (left <= 0) { clearInterval(interval); onExpire?.(); }
  }, 1000);
}

// ── CSS animations ────────────────────────────────────────────
const _style = document.createElement('style');
_style.textContent = `
  @keyframes spin        { to { transform:rotate(360deg); } }
  @keyframes rippleAnim  { to { transform:scale(2.5); opacity:0; } }
  .fi--error { border-color:var(--red)!important;box-shadow:0 0 0 3px rgba(192,57,43,.12)!important; }
  .urgent    { color:var(--red)!important; }
`;
document.head.appendChild(_style);
