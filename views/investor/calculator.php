<?php /* views/investor/calculator.php — $investments */ ?>
<?php $sym = htmlspecialchars(platform_setting('platform_symbol', '$')); ?>

<div class="page-header">
  <div>
    <h1 class="greet">Earnings Calculator</h1>
    <p class="greet-sub">Project your returns before you invest. Each plan's ROI is the total return over its full duration.</p>
  </div>
</div>

<style>
/* ── Layout ──────────────────────────────────────────────────── */
.clc-wrap { display: flex; flex-direction: column; gap: 10px; }

.clc-top-bar {
  display: grid;
  grid-template-columns: 1fr 160px 160px;
  gap: 10px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--r);
  padding: 14px 16px;
  align-items: end;
}

.clc-field { display: flex; flex-direction: column; gap: 5px; }
.clc-field label { font-size: 11px; font-weight: 600; color: var(--text3); letter-spacing: .05em; text-transform: uppercase; }
.clc-field select,
.clc-field input[type=number] {
  font-size: 13px; padding: 8px 10px;
  border: 1px solid var(--border); border-radius: var(--r);
  background: var(--surface2); color: var(--text); width: 100%; outline: none;
}
.clc-field select:focus,
.clc-field input[type=number]:focus { border-color: var(--accent); }

/* ── Hero result ─────────────────────────────────────────────── */
.clc-hero {
  background: var(--surface); border: 1px solid var(--border);
  border-radius: var(--r); padding: 22px 24px; text-align: center;
}
.clc-hero-eyebrow {
  font-size: 10.5px; font-weight: 700; color: var(--text3);
  letter-spacing: .1em; text-transform: uppercase; margin-bottom: 8px;
}
.clc-hero-val {
  font-size: clamp(2rem, 6vw, 3rem); font-weight: 800;
  color: var(--text); letter-spacing: -.5px; line-height: 1;
  margin-bottom: 5px; display: flex; align-items: center; justify-content: center; gap: 10px; flex-wrap: wrap;
}
.clc-hero-gain {
  font-size: clamp(.9rem, 2.5vw, 1.1rem); font-weight: 700;
  color: #0d9488; background: #f0fdfa; border: 1px solid #99f6e4;
  border-radius: 100px; padding: .2rem .75rem;
}
.clc-hero-desc { font-size: 12.5px; color: var(--text3); margin-bottom: 18px; }

.clc-stats {
  display: grid; grid-template-columns: repeat(4, 1fr);
  gap: 1px; background: var(--border); border-radius: var(--r);
  overflow: hidden; border: 1px solid var(--border);
}
.clc-stat { background: var(--surface2); padding: 11px 14px; }
.clc-stat-lbl { font-size: 10px; font-weight: 700; color: var(--text3); text-transform: uppercase; letter-spacing: .06em; margin-bottom: 4px; }
.clc-stat-val { font-size: 15px; font-weight: 700; color: var(--text); }
.clc-stat-val.em { color: #0d9488; }

/* ── Body: inputs + chart ────────────────────────────────────── */
.clc-body {
  display: grid;
  grid-template-columns: 230px minmax(0, 1fr);
  gap: 10px;
  align-items: start;
}

.clc-input-card {
  background: var(--surface); border: 1px solid var(--border);
  border-radius: var(--r); overflow: hidden;
}
.clc-card-head {
  padding: 11px 14px; border-bottom: 1px solid var(--border);
  font-size: 12px; font-weight: 700; color: var(--text2);
}
.clc-card-body { padding: 14px; }

.clc-inp-group { margin-bottom: 14px; }
.clc-inp-group:last-child { margin-bottom: 0; }
.clc-inp-lbl {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 5px;
  font-size: 11px; color: var(--text3); font-weight: 600; text-transform: uppercase; letter-spacing: .05em;
}
.clc-inp-lbl strong { font-size: 13px; font-weight: 700; color: var(--text); text-transform: none; letter-spacing: 0; }
input[type=range].clc-range {
  width: 100%; cursor: pointer; accent-color: #0d9488;
  margin-top: 4px;
}
.clc-range-labels { display: flex; justify-content: space-between; font-size: 10.5px; color: var(--text3); margin-top: 3px; }

.clc-compound-row {
  display: flex; align-items: center; justify-content: space-between;
  padding: 10px 12px; background: var(--surface2); border-radius: var(--r);
  cursor: pointer; border: 1px solid var(--border);
}
.clc-compound-lbl { font-size: 12.5px; font-weight: 600; color: var(--text); }
.clc-compound-sub { font-size: 11px; color: var(--text3); margin-top: 1px; }
.clc-tog { position: relative; width: 34px; height: 19px; flex-shrink: 0; }
.clc-tog input { opacity: 0; width: 0; height: 0; position: absolute; }
.clc-tog-track { position: absolute; inset: 0; background: var(--border); border-radius: 10px; cursor: pointer; transition: background .2s; }
.clc-tog input:checked + .clc-tog-track { background: #0d9488; }
.clc-tog-dot { position: absolute; top: 3px; left: 3px; width: 13px; height: 13px; background: #fff; border-radius: 50%; pointer-events: none; transition: transform .2s; }
.clc-tog input:checked ~ .clc-tog-dot { transform: translateX(15px); }

.clc-mini-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; margin-top: 8px; }
.clc-mini { padding: 9px 10px; background: var(--surface2); border: 1px solid var(--border); border-radius: var(--r); transition: background .2s, border-color .2s; }
.clc-mini.on { background: #f0fdfa; border-color: #99f6e4; }
.clc-mini-lbl { font-size: 10px; font-weight: 700; color: var(--text3); text-transform: uppercase; letter-spacing: .05em; margin-bottom: 3px; }
.clc-mini-val { font-size: 12.5px; font-weight: 700; color: var(--text); }

/* ── Chart card ──────────────────────────────────────────────── */
.clc-chart-card {
  background: var(--surface); border: 1px solid var(--border);
  border-radius: var(--r); overflow: hidden;
}
.clc-chart-head {
  display: flex; align-items: center; justify-content: space-between;
  padding: 11px 14px; border-bottom: 1px solid var(--border);
}
.clc-chart-title { font-size: 12px; font-weight: 700; color: var(--text2); }
.clc-vbtns { display: flex; gap: 4px; }
.clc-vbtn {
  font-size: 11px; padding: 4px 10px; cursor: pointer;
  border: 1px solid var(--border); border-radius: var(--r);
  background: transparent; color: var(--text3); transition: all .15s;
}
.clc-vbtn.on { background: var(--accent); color: #fff; border-color: var(--accent); }

/* ── Breakdown table ─────────────────────────────────────────── */
.clc-tbl-card {
  background: var(--surface); border: 1px solid var(--border);
  border-radius: var(--r); overflow: hidden;
}
.clc-tbl-head {
  display: flex; align-items: center; justify-content: space-between;
  padding: 11px 14px; border-bottom: 1px solid var(--border);
}
.clc-tbl-head-title { font-size: 12px; font-weight: 700; color: var(--text2); }
.clc-tbl-scroll { max-height: 220px; overflow-y: auto; overflow-x: auto; }
.clc-tbl-scroll table { width: 100%; border-collapse: collapse; font-size: 12.5px; min-width: 420px; }
.clc-tbl-scroll th {
  padding: 8px 14px; text-align: left; font-size: 10px; font-weight: 700;
  color: var(--text3); background: var(--surface2); text-transform: uppercase;
  letter-spacing: .05em; position: sticky; top: 0; white-space: nowrap;
}
.clc-tbl-scroll td { padding: 8px 14px; border-top: 1px solid var(--border); color: var(--text); }
.clc-teal { color: #0d9488; font-weight: 700; }
.clc-note {
  font-size: 11.5px; color: var(--text3);
  padding: 9px 14px; border-top: 1px solid var(--border);
  display: flex; align-items: center; gap: 6px;
}

/* ── Responsive ──────────────────────────────────────────────── */
@media (max-width: 900px) {
  .clc-top-bar { grid-template-columns: 1fr 1fr; }
  .clc-top-bar .clc-field:first-child { grid-column: 1 / -1; }
}
@media (max-width: 700px) {
  .clc-body { grid-template-columns: 1fr; }
  .clc-stats { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 480px) {
  .clc-top-bar { grid-template-columns: 1fr; }
  .clc-top-bar .clc-field:first-child { grid-column: auto; }
  .clc-hero { padding: 16px; }
  .clc-hero-val { font-size: 1.85rem; }
  .clc-stats { grid-template-columns: 1fr 1fr; }
  .clc-mini-grid { grid-template-columns: 1fr 1fr; }
}
</style>

<div class="clc-wrap">

  <!-- Top bar: product + custom fields -->
  <div class="clc-top-bar">
    <div class="clc-field">
      <label>Investment product</label>
      <select id="c-prod" onchange="clcOnProd()">
        <option value="custom">— Custom investment —</option>
        <?php foreach ($investments as $inv): ?>
          <option value="<?= (int)$inv['id'] ?>"
            data-roi="<?= (float)$inv['roi'] ?>"
            data-dur="<?= (int)$inv['duration_value'] ?>"
            data-unit="<?= htmlspecialchars($inv['duration_unit']) ?>"
            data-min="<?= (float)$inv['min_investment'] ?>"
            data-name="<?= htmlspecialchars($inv['name']) ?>">
            <?= htmlspecialchars($inv['name']) ?> &mdash; <?= $inv['roi'] ?>% total
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="clc-field" id="clc-roi-field">
      <label>Total ROI (%)</label>
      <input type="number" id="c-roi" value="12" min="0.1" max="100" step="0.1" oninput="clcGo()"/>
    </div>
    <div class="clc-field" id="clc-dur-field">
      <label>Duration (months)</label>
      <input type="number" id="c-months" value="12" min="1" max="360" oninput="clcGo()"/>
    </div>
  </div>

  <!-- Hero result -->
  <div class="clc-hero">
    <div class="clc-hero-eyebrow">Projected portfolio value at maturity</div>
    <div class="clc-hero-val">
      <span id="r-total">—</span>
      <span class="clc-hero-gain" id="r-gain" style="display:none"></span>
    </div>
    <div class="clc-hero-desc" id="r-desc">Select a product or enter custom parameters above</div>
    <div class="clc-stats">
      <div class="clc-stat">
        <div class="clc-stat-lbl">Total return</div>
        <div class="clc-stat-val em" id="r-return">—</div>
      </div>
      <div class="clc-stat">
        <div class="clc-stat-lbl">Monthly income</div>
        <div class="clc-stat-val em" id="r-monthly">—</div>
      </div>
      <div class="clc-stat">
        <div class="clc-stat-lbl">Effective yield</div>
        <div class="clc-stat-val em" id="r-yield">—</div>
      </div>
      <div class="clc-stat">
        <div class="clc-stat-lbl">Total return</div>
        <div class="clc-stat-val" id="r-annual">—</div>
      </div>
    </div>
  </div>

  <!-- Body: inputs + chart -->
  <div class="clc-body">

    <!-- Input panel -->
    <div class="clc-input-card">
      <div class="clc-card-head">Investment amount</div>
      <div class="clc-card-body">

        <div class="clc-inp-group">
          <div class="clc-inp-lbl">
            <span>Amount</span>
            <strong id="amt-lbl"><?= $sym ?>10,000</strong>
          </div>
          <input type="range" class="clc-range" id="c-slider" min="100" max="500000" step="100" value="10000" oninput="clcOnSlide()"/>
          <div class="clc-range-labels"><span><?= $sym ?>100</span><span><?= $sym ?>500k</span></div>
        </div>

        <!-- Compound toggle removed: ROI is a fixed total return over the plan duration -->
        <input type="checkbox" id="c-comp" style="display:none"/>

        <div style="font-size:10.5px;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px">Returns breakdown</div>
        <div class="clc-mini-grid">
          <div class="clc-mini" id="mc-simple">
            <div class="clc-mini-lbl">At maturity</div>
            <div class="clc-mini-val" id="r-simple">—</div>
          </div>
          <div class="clc-mini" id="mc-comp">
            <div class="clc-mini-lbl">Monthly</div>
            <div class="clc-mini-val" id="r-compound">—</div>
          </div>
          <div class="clc-mini">
            <div class="clc-mini-lbl">Quarterly</div>
            <div class="clc-mini-val" id="r-quarterly">—</div>
          </div>
          <div class="clc-mini">
            <div class="clc-mini-lbl">Bi-annual</div>
            <div class="clc-mini-val" id="r-biannual">—</div>
          </div>
        </div>

      </div>
    </div>

    <!-- Chart -->
    <div class="clc-chart-card">
      <div class="clc-chart-head">
        <span class="clc-chart-title">Portfolio balance over time</span>
        <div class="clc-vbtns">
          <button class="clc-vbtn on" id="vb-m" onclick="clcSetView('monthly')">Monthly</button>
          <button class="clc-vbtn" id="vb-q" onclick="clcSetView('quarterly')">Quarterly</button>
        </div>
      </div>
      <canvas id="c-chart" style="display:block;width:100%;height:200px"></canvas>
    </div>

  </div>

  <!-- Breakdown table -->
  <div class="clc-tbl-card">
    <div class="clc-tbl-head">
      <span class="clc-tbl-head-title">Period breakdown</span>
      <span style="font-size:11.5px;color:var(--text3)" id="tbl-info"></span>
    </div>
    <div class="clc-tbl-scroll">
      <table>
        <thead>
          <tr>
            <th>Period</th>
            <th>Interest earned</th>
            <th>Cumulative interest</th>
            <th>Running balance</th>
            <th>Growth</th>
          </tr>
        </thead>
        <tbody id="c-tbody"></tbody>
      </table>
    </div>
    <div class="clc-note">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
      Projections are indicative only and do not guarantee future returns. Past performance is not a reliable indicator of future results.
    </div>
  </div>

</div>

<script>
const CLC_SYM = '<?= platform_setting('platform_symbol', '$') ?>';
let clcViewMode = 'monthly';

function clcFmt(n) {
  return CLC_SYM + parseFloat(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function clcDurToMonths(v, u) {
  const n = parseInt(v) || 0;
  if (u === 'years')  return n * 12;
  if (u === 'days')   return Math.round(n / 30.44);
  if (u === 'weeks')  return Math.round(n / 4.33);
  return n;
}

function clcOnProd() {
  const sel = document.getElementById('c-prod');
  const opt = sel.options[sel.selectedIndex];
  const custom = sel.value === 'custom';
  document.getElementById('clc-roi-field').style.display = custom ? '' : 'none';
  document.getElementById('clc-dur-field').style.display = custom ? '' : 'none';
  if (!custom) {
    const min = parseFloat(opt.dataset.min) || 1000;
    document.getElementById('c-slider').value = Math.min(min, 500000);
    document.getElementById('amt-lbl').textContent = CLC_SYM + Math.min(min, 500000).toLocaleString();
  }
  clcGo();
}

function clcOnSlide() {
  const v = parseInt(document.getElementById('c-slider').value) || 0;
  document.getElementById('amt-lbl').textContent = CLC_SYM + v.toLocaleString();
  clcGo();
}

function clcSetView(mode) {
  clcViewMode = mode;
  document.getElementById('vb-m').className = 'clc-vbtn' + (mode === 'monthly'   ? ' on' : '');
  document.getElementById('vb-q').className = 'clc-vbtn' + (mode === 'quarterly' ? ' on' : '');
  clcGo();
}

function clcGo() {
  const sel    = document.getElementById('c-prod');
  const opt    = sel.options[sel.selectedIndex];
  const custom = sel.value === 'custom';
  const roi    = custom ? (parseFloat(document.getElementById('c-roi').value) || 0) : (parseFloat(opt.dataset.roi) || 0);
  const months = custom ? (parseInt(document.getElementById('c-months').value) || 0) : clcDurToMonths(opt.dataset.dur, opt.dataset.unit || 'months');
  const amount = parseInt(document.getElementById('c-slider').value) || 0;
  const comp   = document.getElementById('c-comp').checked;

  if (!roi || !months || !amount) return;

  // ROI is the TOTAL return over the whole duration; spread evenly across the months.
  const interest = amount * roi / 100;
  const total    = amount + interest;
  const perMonth = months > 0 ? interest / months : interest;

  document.getElementById('r-total').textContent  = clcFmt(total);
  const gainEl = document.getElementById('r-gain');
  gainEl.textContent = '+' + roi.toFixed(1) + '%';
  gainEl.style.display = '';

  document.getElementById('r-desc').textContent =
    'From ' + clcFmt(amount) + ' over ' + months + ' months — ' + roi + '% total return';

  document.getElementById('r-return').textContent    = '+' + clcFmt(interest);
  document.getElementById('r-monthly').textContent   = clcFmt(perMonth);
  document.getElementById('r-yield').textContent     = roi.toFixed(2) + '%';
  document.getElementById('r-annual').textContent    = '+' + clcFmt(interest);
  document.getElementById('r-simple').textContent    = clcFmt(total);
  document.getElementById('r-compound').textContent  = clcFmt(perMonth);
  document.getElementById('r-quarterly').textContent = clcFmt(perMonth * 3);
  document.getElementById('r-biannual').textContent  = clcFmt(perMonth * 6);

  const periods = clcViewMode === 'monthly'
    ? Array.from({ length: Math.min(months, 36) }, (_, i) => i + 1)
    : Array.from({ length: Math.ceil(months / 3) }, (_, i) => (i + 1) * 3);

  const getBal = p => amount + perMonth * Math.min(p, months);

  document.getElementById('tbl-info').textContent = periods.length + ' periods';
  document.getElementById('c-tbody').innerHTML = periods.map((p, i) => {
    const prev  = i > 0 ? periods[i - 1] : 0;
    const bal   = getBal(p);
    const prevB = getBal(prev);
    const pi    = bal - prevB;
    const ti    = bal - amount;
    const gr    = ((ti / amount) * 100).toFixed(1);
    const label = clcViewMode === 'monthly' ? 'Month ' + p : 'Q' + Math.ceil(p / 3) + ' (M' + p + ')';
    return '<tr>'
      + '<td style="color:var(--text3)">' + label + '</td>'
      + '<td class="clc-teal">+' + clcFmt(pi) + '</td>'
      + '<td class="clc-teal">+' + clcFmt(ti) + '</td>'
      + '<td style="font-weight:700">' + clcFmt(bal) + '</td>'
      + '<td style="font-size:11.5px;color:var(--text3)">' + gr + '%</td>'
      + '</tr>';
  }).join('');

  clcDrawChart(periods, getBal, amount);
}

function clcDrawChart(periods, getBal, principal) {
  const canvas = document.getElementById('c-chart');
  if (!canvas) return;
  const dpr  = window.devicePixelRatio || 1;
  const W    = canvas.parentElement.clientWidth || 500;
  const H    = 200;
  canvas.width  = W * dpr;
  canvas.height = H * dpr;
  canvas.style.width  = W + 'px';
  canvas.style.height = H + 'px';
  const ctx = canvas.getContext('2d');
  ctx.scale(dpr, dpr);
  ctx.clearRect(0, 0, W, H);

  const dark = window.matchMedia('(prefers-color-scheme: dark)').matches;
  const teal      = '#0d9488';
  const tealFill  = dark ? 'rgba(13,148,136,0.18)' : 'rgba(13,148,136,0.10)';
  const princLine = dark ? 'rgba(255,255,255,0.12)' : 'rgba(0,0,0,0.10)';
  const gridC     = dark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
  const txtC      = dark ? 'rgba(255,255,255,0.35)' : 'rgba(0,0,0,0.38)';

  const pad = { t: 12, r: 16, b: 28, l: 58 };
  const cW  = W - pad.l - pad.r;
  const cH  = H - pad.t - pad.b;

  const vals = periods.map(p => getBal(p));
  const maxV = Math.max(...vals, principal * 1.005);
  const minV = principal * 0.998;
  const range = maxV - minV || 1;

  const toX = i => pad.l + (i / (periods.length - 1 || 1)) * cW;
  const toY = v => pad.t + (1 - (v - minV) / range) * cH;

  // Grid lines
  ctx.strokeStyle = gridC;
  ctx.lineWidth = 0.5;
  [0, 0.25, 0.5, 0.75, 1].forEach(r => {
    const y = pad.t + cH * r;
    ctx.beginPath(); ctx.moveTo(pad.l, y); ctx.lineTo(pad.l + cW, y); ctx.stroke();
  });

  // Y-axis labels
  ctx.fillStyle = txtC;
  ctx.font = '10px system-ui, sans-serif';
  ctx.textAlign = 'right';
  ctx.textBaseline = 'middle';
  [0, 0.25, 0.5, 0.75, 1].forEach(r => {
    const v = maxV - range * r;
    const y = pad.t + cH * r;
    const label = v >= 1000000
      ? CLC_SYM + (v / 1000000).toFixed(1) + 'M'
      : v >= 1000
      ? CLC_SYM + (v / 1000).toFixed(0) + 'k'
      : CLC_SYM + v.toFixed(0);
    ctx.fillText(label, pad.l - 6, y);
  });

  // Principal baseline
  ctx.strokeStyle = princLine;
  ctx.lineWidth = 1;
  ctx.setLineDash([4, 4]);
  const py = toY(principal);
  ctx.beginPath(); ctx.moveTo(pad.l, py); ctx.lineTo(pad.l + cW, py); ctx.stroke();
  ctx.setLineDash([]);
  ctx.fillStyle = txtC;
  ctx.font = '9px system-ui, sans-serif';
  ctx.textAlign = 'left';
  ctx.textBaseline = 'bottom';
  ctx.fillText('Principal', pad.l + 5, py - 3);

  // Filled area
  ctx.beginPath();
  periods.forEach((p, i) => {
    const x = toX(i); const y = toY(vals[i]);
    i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
  });
  ctx.lineTo(toX(periods.length - 1), pad.t + cH);
  ctx.lineTo(pad.l, pad.t + cH);
  ctx.closePath();
  ctx.fillStyle = tealFill;
  ctx.fill();

  // Line
  ctx.beginPath();
  periods.forEach((p, i) => {
    const x = toX(i); const y = toY(vals[i]);
    i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
  });
  ctx.strokeStyle = teal;
  ctx.lineWidth = 2;
  ctx.lineJoin = 'round';
  ctx.stroke();

  // X-axis labels
  ctx.fillStyle = txtC;
  ctx.font = '10px system-ui, sans-serif';
  ctx.textAlign = 'center';
  ctx.textBaseline = 'top';
  const step = Math.max(1, Math.floor(periods.length / 6));
  periods.forEach((p, i) => {
    if (i === 0 || i === periods.length - 1 || i % step === 0) {
      const label = clcViewMode === 'monthly' ? 'M' + p : 'Q' + Math.ceil(p / 3);
      ctx.fillText(label, toX(i), pad.t + cH + 6);
    }
  });

  // Last point dot
  const lx = toX(periods.length - 1);
  const ly = toY(vals[periods.length - 1]);
  ctx.beginPath(); ctx.arc(lx, ly, 4.5, 0, Math.PI * 2);
  ctx.fillStyle = teal; ctx.fill();
  ctx.beginPath(); ctx.arc(lx, ly, 4.5, 0, Math.PI * 2);
  ctx.strokeStyle = '#fff'; ctx.lineWidth = 2; ctx.stroke();
}

// Redraw chart on resize
let clcResizeTimer;
window.addEventListener('resize', () => {
  clearTimeout(clcResizeTimer);
  clcResizeTimer = setTimeout(clcGo, 120);
});

// Pre-select from URL ?inv=ID
document.addEventListener('DOMContentLoaded', () => {
  const invId = new URLSearchParams(window.location.search).get('inv');
  if (invId) {
    const sel = document.getElementById('c-prod');
    for (let i = 0; i < sel.options.length; i++) {
      if (sel.options[i].value === invId) { sel.selectedIndex = i; break; }
    }
  }
  clcOnProd();
});
</script>
