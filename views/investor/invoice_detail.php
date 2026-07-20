<?php /* views/investor/invoice_detail.php */ ?>
<?php
$sym         = platform_setting('platform_symbol', '$');
$isPaid      = $invoice['status'] === 'paid';
$isCancelled = $invoice['status'] === 'cancelled';
$isPending   = $invoice['status'] === 'pending';
$daysLeft    = (int) ceil((strtotime($invoice['due_date']) - time()) / 86400);
$overdue     = $isPending && $daysLeft < 0;
$hasBal      = $walletBal >= (float)$invoice['amount'];

// Build list of methods allowed by the invoice setting
$invoiceAllowed = $invoice['payment_method'] === 'any'
    ? ['wallet','crypto','paypal','wire']
    : ($invoice['payment_method'] === 'crypto'
        ? ['wallet','crypto']
        : ['wallet', $invoice['payment_method']]);

// Intersect with globally enabled payment methods
$enabledByAdmin = [];
if (platform_setting('invoice_wallet_payment','1') === '1') $enabledByAdmin[] = 'wallet';
if (platform_setting('payment_crypto','1') === '1') $enabledByAdmin[] = 'crypto';
if (platform_setting('payment_paypal','1') === '1') $enabledByAdmin[] = 'paypal';
if (platform_setting('payment_wire','1')   === '1') $enabledByAdmin[] = 'wire';
$allowedMethods = array_values(array_intersect($invoiceAllowed, $enabledByAdmin));

$hasAnyCrypto   = in_array('crypto', $allowedMethods);
$cryptoReady    = !empty(array_filter($cryptoAddrs));
$paypalReady    = !empty($paypalEmail);
?>
<style>
.inv-pg{max-width:880px;margin:0 auto}
.inv-back{display:inline-flex;align-items:center;gap:6px;font-size:12.5px;font-weight:600;color:var(--em-600);text-decoration:none;margin-bottom:1.25rem}
.inv-layout{display:grid;grid-template-columns:1fr 300px;gap:1.25rem;align-items:start}
@media(max-width:720px){.inv-layout{grid-template-columns:1fr}}

/* ── Document card ── */
.inv-doc{background:#fff;border:1px solid var(--mist-100);border-radius:16px;overflow:hidden}
.inv-doc-top{padding:1.4rem 1.5rem 1.2rem;border-bottom:1px solid var(--mist-50)}
.inv-doc-toprow{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:1rem}
.inv-from-lbl{font-size:10px;font-weight:700;color:var(--mist-400);text-transform:uppercase;letter-spacing:.07em;margin-bottom:3px}
.inv-from-name{font-size:14px;font-weight:800;color:var(--mist-900)}
.inv-from-sub{font-size:11px;color:var(--mist-400);margin-top:2px}
.inv-ref{font-size:10px;color:var(--mist-400);font-family:monospace;margin-bottom:5px;text-align:right}
.inv-status-pill{display:inline-flex;align-items:center;gap:5px;padding:4px 11px;border-radius:20px;font-size:11px;font-weight:700}
.inv-status-pill.pending{background:#FEF3C7;color:#92400E}
.inv-status-pill.pending::before,.inv-status-pill.paid::before,.inv-status-pill.cancelled::before{content:'';width:6px;height:6px;border-radius:50%;display:inline-block}
.inv-status-pill.pending::before{background:#F59E0B}
.inv-status-pill.paid{background:#ECFDF5;color:#065F46}
.inv-status-pill.paid::before{background:#10B981}
.inv-status-pill.cancelled{background:var(--mist-100);color:var(--mist-500)}
.inv-status-pill.cancelled::before{background:var(--mist-400)}
.inv-title-lbl{font-size:10px;font-weight:700;color:var(--mist-400);text-transform:uppercase;letter-spacing:.07em;margin-bottom:3px}
.inv-title-val{font-size:1.05rem;font-weight:800;color:var(--mist-900)}
.inv-amt-band{background:var(--mist-50);padding:1.1rem 1.5rem;display:flex;align-items:center;justify-content:space-between;border-top:1px solid var(--mist-100);border-bottom:1px solid var(--mist-100)}
.inv-amt-lbl{font-size:10.5px;font-weight:600;color:var(--mist-400);text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px}
.inv-amt-val{font-size:2rem;font-weight:900;color:var(--mist-900);letter-spacing:-.6px;line-height:1}
.inv-due-chip{display:inline-flex;align-items:center;gap:5px;padding:5px 11px;border-radius:8px;font-size:11.5px;font-weight:600}
.inv-due-chip.warn{background:#FFFBEB;border:1px solid #FDE68A;color:#92400E}
.inv-due-chip.overdue{background:#FEF2F2;border:1px solid #FECACA;color:#991B1B}
.inv-due-chip.ok{background:#F0FDF4;border:1px solid #BBF7D0;color:#166534}
.inv-due-chip.neutral{background:var(--mist-100);border:1px solid var(--mist-200);color:var(--mist-600)}
.inv-detail-grid{display:grid;grid-template-columns:1fr 1fr;border-bottom:1px solid var(--mist-50)}
.inv-detail-cell{padding:.75rem 1.5rem;border-bottom:1px solid var(--mist-50)}
.inv-detail-cell:nth-last-child(-n+2){border-bottom:none}
.inv-detail-lbl{font-size:10px;font-weight:600;color:var(--mist-400);text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px}
.inv-detail-val{font-size:12.5px;font-weight:600;color:var(--mist-900)}
.inv-detail-val.mono{font-family:monospace;font-size:11.5px;color:var(--em-600)}
.inv-desc{padding:1rem 1.5rem}
.inv-desc-lbl{font-size:10px;font-weight:700;color:var(--mist-400);text-transform:uppercase;letter-spacing:.07em;margin-bottom:.45rem}
.inv-desc-text{font-size:13px;color:var(--mist-700);line-height:1.7}

/* ── Payment panel ── */
.pay-panel{background:#fff;border:1px solid var(--mist-100);border-radius:16px;overflow:hidden;position:sticky;top:1rem}
.pay-panel-head{padding:.9rem 1.1rem .8rem;border-bottom:1px solid var(--mist-50)}
.pay-panel-title{font-size:13px;font-weight:800;color:var(--mist-900);margin-bottom:1px}
.pay-panel-sub{font-size:11px;color:var(--mist-400)}
.pay-panel-body{padding:.85rem 1.1rem}
.pm-lbl{font-size:10px;font-weight:700;color:var(--mist-400);text-transform:uppercase;letter-spacing:.07em;margin-bottom:.5rem}

/* Method options */
.pm-opt{display:flex;align-items:center;gap:.6rem;padding:.55rem .7rem;border:1.5px solid var(--mist-100);border-radius:9px;margin-bottom:.35rem;cursor:pointer;transition:border-color .15s,background .15s}
.pm-opt:hover:not(.pm-opt-disabled){border-color:var(--em-400)}
.pm-opt-disabled{opacity:.5;cursor:not-allowed}
.pm-opt.active{border-color:var(--em-500);background:#EBF3FB}
.pm-ic{width:28px;height:28px;border-radius:7px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.pm-ic.wallet{background:#F0FDF4;color:#15803D}
.pm-ic.crypto{background:#F3F0FF;color:#7C3AED}
.pm-ic.paypal{background:#EFF6FF;color:#1D4ED8}
.pm-ic.wire{background:#FFF7ED;color:#C2410C}
.pm-name{font-size:12px;font-weight:600;color:var(--mist-900)}
.pm-hint{font-size:10px;color:var(--mist-400)}
.pm-check{width:14px;height:14px;border-radius:50%;border:2px solid var(--mist-200);margin-left:auto;flex-shrink:0;transition:all .15s;display:flex;align-items:center;justify-content:center}
.pm-opt.active .pm-check{border-color:var(--em-500);background:var(--em-500)}
.pm-opt.active .pm-check::after{content:'';width:5px;height:5px;border-radius:50%;background:#fff}
.pm-bal-badge{font-size:10.5px;font-weight:700;padding:2px 7px;border-radius:20px;margin-left:auto}
.pm-bal-badge.ok{background:#ECFDF5;color:#065F46}
.pm-bal-badge.low{background:#FEF2F2;color:#991B1B}

/* Coin selector */
.coin-grid{display:grid;grid-template-columns:1fr 1fr;gap:.35rem;margin-top:.5rem;margin-bottom:.5rem}
.coin-btn{padding:.4rem .5rem;border:1.5px solid var(--mist-100);border-radius:6px;font-size:11px;font-weight:600;color:var(--mist-600);cursor:pointer;text-align:center;transition:all .15s;background:#fff}
.coin-btn.active{border-color:var(--em-500);color:var(--em-600);background:#EBF3FB}

/* Payment details (step 2) */
.pay-detail-box{background:var(--mist-50);border:1px solid var(--mist-100);border-radius:9px;padding:.75rem;margin-bottom:.65rem}
.pay-detail-lbl{font-size:10px;font-weight:700;color:var(--mist-400);text-transform:uppercase;letter-spacing:.05em;margin-bottom:.3rem}
.pay-detail-val{font-size:12px;font-weight:600;color:var(--mist-900);word-break:break-all;line-height:1.5}
.pay-copy-btn{display:inline-flex;align-items:center;gap:4px;font-size:10.5px;font-weight:600;color:var(--em-600);background:none;border:none;cursor:pointer;padding:4px 0;margin-top:3px}
.wire-row{display:flex;justify-content:space-between;font-size:11.5px;padding:.3rem 0;border-bottom:1px solid var(--mist-100)}
.wire-row:last-child{border:none}
.wire-row span:first-child{color:var(--mist-400)}
.wire-row span:last-child{font-weight:600;color:var(--mist-900)}
.pay-timer{display:flex;align-items:center;gap:.5rem;font-size:11.5px;color:var(--mist-500);margin-bottom:.65rem;padding:.5rem .65rem;background:var(--mist-50);border-radius:7px}
.pay-timer strong{color:var(--mist-900);font-variant-numeric:tabular-nums}

/* Totals */
.pay-sep{border:none;border-top:1px solid var(--mist-100);margin:.75rem 0}
.pay-total-row{display:flex;justify-content:space-between;align-items:center;background:var(--mist-50);border-radius:8px;padding:.6rem .75rem;margin-bottom:.75rem}
.pay-total-lbl{font-size:12px;font-weight:700;color:var(--mist-900)}
.pay-total-val{font-size:1.05rem;font-weight:900;color:var(--mist-900)}
.pay-cta{width:100%;padding:11px;background:var(--mist-900);color:#fff;font-size:13px;font-weight:700;border:none;border-radius:10px;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;transition:background .15s;margin-bottom:.5rem}
.pay-cta:hover:not(:disabled){background:var(--em-600)}
.pay-cta:disabled{opacity:.45;cursor:not-allowed}
.pay-cta.green{background:#059669}
.pay-cta.green:hover:not(:disabled){background:#047857}
.pay-secure{display:flex;align-items:center;justify-content:center;gap:4px;font-size:10.5px;color:var(--mist-400)}
.pay-back-btn{background:none;border:none;font-size:11.5px;font-weight:600;color:var(--em-600);cursor:pointer;padding:0;margin-bottom:.65rem;display:flex;align-items:center;gap:4px}

/* Status notices */
.paid-notice,.cancelled-notice{border-radius:10px;padding:1.25rem;text-align:center}
.paid-notice{background:#ECFDF5;border:1px solid #A7F3D0}
.paid-notice h4{font-size:14px;font-weight:700;color:#065F46;margin:.5rem 0 .25rem}
.paid-notice p{font-size:12px;color:#047857}
.cancelled-notice{background:var(--mist-50);border:1px solid var(--mist-100)}
.cancelled-notice p{font-size:12.5px;color:var(--mist-500)}
#pay-result{margin-bottom:.65rem}
</style>

<div class="inv-pg">
  <a href="/investor/dashboard" class="inv-back">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
    Back to dashboard
  </a>

  <div class="inv-layout">

    <!-- Invoice document -->
    <div class="inv-doc">
      <div class="inv-doc-top">
        <div class="inv-doc-toprow">
          <div>
            <div class="inv-from-lbl">Issued by</div>
            <div class="inv-from-name"><?= htmlspecialchars($invoice['platform_name'] ?? platform_setting('platform_name','NexVest')) ?></div>
            <div class="inv-from-sub"><?= htmlspecialchars(platform_setting('platform_email','')) ?></div>
          </div>
          <div style="text-align:right">
            <div class="inv-ref"><?= htmlspecialchars($invoice['reference']) ?></div>
            <div class="inv-status-pill <?= htmlspecialchars($invoice['status']) ?>"><?= ucfirst($invoice['status']) ?></div>
          </div>
        </div>
        <div>
          <div class="inv-title-lbl">Invoice for</div>
          <div class="inv-title-val"><?= htmlspecialchars($invoice['title']) ?></div>
        </div>
      </div>

      <div class="inv-amt-band">
        <div>
          <div class="inv-amt-lbl">Total amount due</div>
          <div class="inv-amt-val"><?= htmlspecialchars($sym) ?><?= number_format((float)$invoice['amount'], 2) ?></div>
        </div>
        <?php if ($isPending): ?>
          <div class="inv-due-chip <?= $overdue ? 'overdue' : ($daysLeft <= 3 ? 'warn' : 'ok') ?>">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 15.5 15.5"/></svg>
            <?php if ($overdue): ?>Overdue by <?= abs($daysLeft) ?> day<?= abs($daysLeft)!==1?'s':'' ?>
            <?php elseif ($daysLeft === 0): ?>Due today
            <?php else: ?>Due in <?= $daysLeft ?> day<?= $daysLeft!==1?'s':'' ?><?php endif; ?>
          </div>
        <?php elseif ($isPaid): ?>
          <div class="inv-due-chip neutral">Paid <?= date('d M Y', strtotime($invoice['paid_at'] ?? $invoice['created_at'])) ?></div>
        <?php else: ?>
          <div class="inv-due-chip neutral">Cancelled</div>
        <?php endif; ?>
      </div>

      <div class="inv-detail-grid">
        <div class="inv-detail-cell">
          <div class="inv-detail-lbl">Issue date</div>
          <div class="inv-detail-val"><?= date('d M Y', strtotime($invoice['created_at'])) ?></div>
        </div>
        <div class="inv-detail-cell">
          <div class="inv-detail-lbl">Due date</div>
          <div class="inv-detail-val" <?= ($isPending && ($overdue || $daysLeft<=3))?'style="color:#D97706"':'' ?>><?= date('d M Y', strtotime($invoice['due_date'])) ?></div>
        </div>
        <div class="inv-detail-cell">
          <div class="inv-detail-lbl">Billed to</div>
          <div class="inv-detail-val"><?= htmlspecialchars($user['first_name'].' '.$user['last_name']) ?></div>
        </div>
        <div class="inv-detail-cell">
          <div class="inv-detail-lbl">Reference</div>
          <div class="inv-detail-val mono"><?= htmlspecialchars($invoice['reference']) ?></div>
        </div>
      </div>

      <?php if (!empty($invoice['description'])): ?>
      <div class="inv-desc">
        <div class="inv-desc-lbl">Description</div>
        <div class="inv-desc-text"><?= nl2br(htmlspecialchars($invoice['description'])) ?></div>
      </div>
      <?php endif; ?>
    </div>

    <!-- Payment panel -->
    <div class="pay-panel">
      <div class="pay-panel-head">
        <div class="pay-panel-title">
          <?php if ($isPaid): ?>Invoice settled<?php elseif ($isCancelled): ?>Invoice cancelled<?php else: ?>Pay this invoice<?php endif; ?>
        </div>
        <div class="pay-panel-sub">
          <?php if ($isPending): ?>Select a payment method below<?php elseif ($isPaid): ?>No further action needed<?php else: ?>This invoice is no longer active<?php endif; ?>
        </div>
      </div>
      <div class="pay-panel-body">

        <?php if ($isPaid): ?>
        <div class="paid-notice">
          <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg>
          <h4>Payment complete</h4>
          <p>This invoice was settled.<br>No further action needed.</p>
        </div>

        <?php elseif ($isCancelled): ?>
        <div class="cancelled-notice">
          <p>This invoice has been cancelled and is no longer payable.</p>
        </div>

        <?php else: ?>

        <!-- STEP 1: Method selection -->
        <div id="pay-step1">
          <div id="pay-result"></div>
          <div class="pm-lbl">Payment method</div>

          <!-- Wallet balance -->
          <?php if (in_array('wallet', $allowedMethods, true)): ?>
          <div class="pm-opt" id="pm-wallet" onclick="selectMethod('wallet',this)">
            <div class="pm-ic wallet">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 7H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/><circle cx="17" cy="13" r="1" fill="currentColor" stroke="none"/></svg>
            </div>
            <div>
              <div class="pm-name">Wallet balance</div>
              <div class="pm-hint"><?= fmt_currency($walletBal) ?> available</div>
            </div>
            <span class="pm-bal-badge <?= $hasBal?'ok':'low' ?>"><?= $hasBal?'Sufficient':'Low balance' ?></span>
            <div class="pm-check"></div>
          </div>
          <?php endif; ?>

          <!-- Crypto -->
          <?php if ($hasAnyCrypto): ?>
          <div class="pm-opt<?= !$cryptoReady?' pm-opt-disabled':'' ?>" id="pm-crypto" onclick="<?= $cryptoReady?"selectMethod('crypto',this)":'void(0)' ?>">
            <div class="pm-ic crypto">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.5 9a3 3 0 0 1 5 1c0 2-3 3-3 3"/><circle cx="12" cy="17" r=".5" fill="currentColor"/></svg>
            </div>
            <div>
              <div class="pm-name">Cryptocurrency</div>
              <div class="pm-hint"><?= $cryptoReady ? 'BTC · ETH · USDT · USDC' : 'Contact support for wallet address' ?></div>
            </div>
            <div class="pm-check"></div>
          </div>
          <?php if ($cryptoReady): ?>
          <div id="coin-picker" style="display:none;padding:0 .1rem">
            <div class="coin-grid">
              <?php foreach (['btc'=>'Bitcoin','eth'=>'Ethereum','usdt'=>'Tether (USDT)','usdc'=>'USD Coin'] as $c=>$n): ?>
                <?php if (!empty($cryptoAddrs[$c])): ?>
                  <div class="coin-btn" id="coin-<?= $c ?>" onclick="selectCoin('<?= $c ?>',this)"><?= $n ?></div>
                <?php endif; ?>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>
          <?php endif; ?>

          <!-- PayPal -->
          <?php if (in_array('paypal', $allowedMethods, true)): ?>
          <div class="pm-opt<?= !$paypalReady?' pm-opt-disabled':'' ?>" id="pm-paypal" onclick="<?= $paypalReady?"selectMethod('paypal',this)":'void(0)' ?>">
            <div class="pm-ic paypal">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 11C7 6.03 10.36 2 14.5 2S22 6.03 22 11s-3.36 9-7.5 9"/><path d="M2 13c0 4.42 3.36 8 7.5 8S17 17.42 17 13s-3.36-8-7.5-8"/></svg>
            </div>
            <div>
              <div class="pm-name">PayPal</div>
              <div class="pm-hint"><?= $paypalReady ? 'Send to ' . htmlspecialchars($paypalEmail) : 'Contact support for PayPal details' ?></div>
            </div>
            <div class="pm-check"></div>
          </div>
          <?php endif; ?>

          <!-- Wire -->
          <?php if (in_array('wire', $allowedMethods, true)): ?>
          <div class="pm-opt" id="pm-wire" onclick="selectMethod('wire',this)">
            <div class="pm-ic wire">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 3H8"/><line x1="2" y1="11" x2="22" y2="11"/></svg>
            </div>
            <div><div class="pm-name">Wire transfer</div><div class="pm-hint">Bank-to-bank · 3–5 days</div></div>
            <div class="pm-check"></div>
          </div>
          <?php endif; ?>

          <hr class="pay-sep">
          <div class="pay-total-row">
            <span class="pay-total-lbl">Total due</span>
            <span class="pay-total-val"><?= htmlspecialchars($sym) ?><?= number_format((float)$invoice['amount'],2) ?></span>
          </div>
          <button class="pay-cta" id="pay-btn" disabled onclick="handlePay()">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            Proceed to payment
          </button>
          <div class="pay-secure">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            Secured by <?= htmlspecialchars(platform_setting('platform_name','NexVest')) ?>
          </div>
        </div>

        <!-- STEP 2: Payment details (crypto/paypal/wire) -->
        <div id="pay-step2" style="display:none">
          <button class="pay-back-btn" onclick="goBack()">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
            Change method
          </button>
          <div id="pay-details-content"></div>
          <div class="pay-timer" id="pay-timer-box" style="display:none">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            Invoice expires in <strong id="pay-timer-val">30:00</strong>
          </div>
          <div id="pay-step2-result"></div>
          <button class="pay-cta" id="pay-confirm-btn" onclick="confirmPayment()">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg>
            <span id="pay-confirm-label">I have sent the payment</span>
          </button>
          <div class="pay-secure" style="margin-top:.5rem">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            Admin will confirm within 24h
          </div>
        </div>

        <!-- STEP 3: Success -->
        <div id="pay-step3" style="display:none;text-align:center;padding:1rem 0">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="1.5" style="margin-bottom:.75rem"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg>
          <h4 id="pay-success-title" style="font-size:15px;font-weight:700;color:var(--mist-900);margin-bottom:.35rem">Payment submitted</h4>
          <p id="pay-success-body" style="font-size:12.5px;color:var(--mist-500);line-height:1.6"></p>
          <a href="/investor/dashboard" class="pay-cta" style="margin-top:1rem;text-decoration:none">Back to dashboard</a>
        </div>

        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
const INV_REF    = <?= json_encode($invoice['reference']) ?>;
const INV_AMOUNT = <?= (float)$invoice['amount'] ?>;
const SYM        = <?= json_encode($sym) ?>;
const TIMEOUT    = <?= (int)$depositTimeout ?>;
const PAYPAL_EMAIL = <?= json_encode($paypalEmail) ?>;
const PAYPAL_ME    = <?= json_encode($paypalMe) ?>;
const WALLET_BAL   = <?= (float)$walletBal ?>;
const WIRE = <?= json_encode($wireDetails) ?>;
const CRYPTO_ADDRS = <?= json_encode($cryptoAddrs) ?>;

let currentMethod = '';
let currentCoin   = '';
let depositRef    = '';
let timerInterval = null;

function selectMethod(method, el) {
  document.querySelectorAll('.pm-opt').forEach(o => o.classList.remove('active'));
  el.classList.add('active');
  currentMethod = method;
  currentCoin = '';
  // Toggle coin picker
  const cp = document.getElementById('coin-picker');
  if (cp) cp.style.display = method === 'crypto' ? 'block' : 'none';
  // Reset coin buttons
  document.querySelectorAll('.coin-btn').forEach(b => b.classList.remove('active'));
  updateBtn();
}

function selectCoin(coin, el) {
  document.querySelectorAll('.coin-btn').forEach(b => b.classList.remove('active'));
  el.classList.add('active');
  currentCoin = coin;
  updateBtn();
}

function updateBtn() {
  const btn = document.getElementById('pay-btn');
  let enabled = !!currentMethod;
  if (currentMethod === 'crypto') enabled = !!currentCoin;
  if (currentMethod === 'wallet' && WALLET_BAL < INV_AMOUNT) {
    btn.textContent = 'Insufficient balance';
    btn.disabled = true;
    return;
  }
  btn.disabled = !enabled;
  btn.innerHTML = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg> ${currentMethod === 'wallet' ? 'Pay with wallet balance' : 'Proceed to payment'}`;
}

async function handlePay() {
  if (currentMethod === 'wallet') {
    await payWithBalance();
  } else {
    await initiateExternalPay();
  }
}

async function payWithBalance() {
  const btn = document.getElementById('pay-btn');
  setLoading(btn, true, 'Processing…');
  const fd = new FormData();
  const data = await post('/investor/invoices/' + INV_REF + '/pay-balance', fd, true);
  setLoading(btn, false);
  if (data.success) {
    showSuccess('Payment complete!',
      'Your invoice has been paid using your wallet balance. Your new balance is ' + SYM + parseFloat(data.new_balance).toFixed(2) + '.');
  } else {
    document.getElementById('pay-result').innerHTML = '<div class="alert-banner err" style="margin-bottom:.65rem">' + (data.error || 'Payment failed.') + '</div>';
  }
}

async function initiateExternalPay() {
  const btn = document.getElementById('pay-btn');
  setLoading(btn, true, 'Creating invoice…');
  const fd = new FormData();
  fd.append('method', currentMethod);
  if (currentCoin) fd.append('coin', currentCoin);
  const data = await post('/investor/invoices/' + INV_REF + '/pay', fd, true);
  setLoading(btn, false);
  if (!data.success) {
    document.getElementById('pay-result').innerHTML = '<div class="alert-banner err" style="margin-bottom:.65rem">' + (data.error || 'Failed.') + '</div>';
    return;
  }

  depositRef = data.reference;
  document.getElementById('pay-step1').style.display = 'none';
  document.getElementById('pay-step2').style.display = '';
  buildPaymentDetails(data);
  startTimer(data.expires_at);
}

function buildPaymentDetails(data) {
  let html = '';
  if (currentMethod === 'crypto') {
    const addr  = data.wallet_address;
    const label = {btc:'Bitcoin (BTC)',eth:'Ethereum (ETH)',usdt:'Tether (USDT)',usdc:'USD Coin (USDC)'}[currentCoin] || currentCoin.toUpperCase();
    html = `<div class="pay-detail-box">
      <div class="pay-detail-lbl">${label} address — send exactly ${SYM}${INV_AMOUNT.toFixed(2)}</div>
      <div class="pay-detail-val" style="font-family:monospace;font-size:11px">${addr}</div>
      <button class="pay-copy-btn" onclick="copyText('${addr}',this)">
        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg> Copy address
      </button>
    </div>
    <div class="alert-banner" style="background:#FFFBEB;border:1px solid #FDE68A;color:#92400E;margin-bottom:.65rem">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      Send <strong>only ${currentCoin.toUpperCase()}</strong> to this address. Wrong asset = permanent loss.
    </div>`;
  } else if (currentMethod === 'paypal') {
    html = `<div class="pay-detail-box">
      <div class="pay-detail-lbl">Send PayPal payment to</div>
      <div class="pay-detail-val">${PAYPAL_EMAIL}</div>
      <button class="pay-copy-btn" onclick="copyText('${PAYPAL_EMAIL}',this)">
        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg> Copy email
      </button>
      ${PAYPAL_ME ? `<a href="${PAYPAL_ME}" target="_blank" style="display:inline-flex;align-items:center;gap:4px;font-size:10.5px;font-weight:600;color:var(--em-600);margin-top:4px;text-decoration:none">Open PayPal.me →</a>` : ''}
    </div>
    <div class="pay-detail-box">
      <div class="pay-detail-lbl">Include this reference in the payment note</div>
      <div class="pay-detail-val" style="font-family:monospace">${depositRef}</div>
      <button class="pay-copy-btn" onclick="copyText('${depositRef}',this)">
        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg> Copy reference
      </button>
    </div>`;
  } else if (currentMethod === 'wire') {
    const rows = Object.entries({...WIRE, 'Reference': depositRef})
      .map(([k,v]) => `<div class="wire-row"><span>${k}</span><span>${v||'—'}</span></div>`).join('');
    html = `<div class="pay-detail-box">${rows}</div>
    <div class="alert-banner" style="background:#FFFBEB;border:1px solid #FDE68A;color:#92400E;margin-bottom:.65rem">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      Include the reference in your wire transfer description. Allow 3–5 business days.
    </div>`;
  }
  document.getElementById('pay-details-content').innerHTML = html;
}

function startTimer(expiresAt) {
  const box = document.getElementById('pay-timer-box');
  const val = document.getElementById('pay-timer-val');
  box.style.display = 'flex';
  const end = new Date(expiresAt.replace(' ','T') + 'Z').getTime();
  clearInterval(timerInterval);
  timerInterval = setInterval(() => {
    const diff = Math.max(0, Math.floor((end - Date.now()) / 1000));
    const m = String(Math.floor(diff/60)).padStart(2,'0');
    const s = String(diff % 60).padStart(2,'0');
    val.textContent = m + ':' + s;
    if (diff === 0) {
      clearInterval(timerInterval);
      document.getElementById('pay-confirm-btn').disabled = true;
      document.getElementById('pay-step2-result').innerHTML = '<div class="alert-banner err" style="margin-bottom:.65rem">Invoice expired. Please go back and start again.</div>';
    }
  }, 1000);
}

async function confirmPayment() {
  const btn = document.getElementById('pay-confirm-btn');
  setLoading(btn, true, 'Submitting…');
  const fd = new FormData();
  fd.append('reference', depositRef);
  const data = await post('/investor/deposit/confirm', fd, true);
  setLoading(btn, false);
  if (data.success) {
    clearInterval(timerInterval);
    showSuccess('Payment submitted!', 'Your payment is under review. You\'ll receive an email once it\'s confirmed by our team (usually within 24 hours).');
  } else {
    document.getElementById('pay-step2-result').innerHTML = '<div class="alert-banner err" style="margin-bottom:.65rem">' + (data.error || 'Failed. Please try again.') + '</div>';
  }
}

function showSuccess(title, body) {
  document.getElementById('pay-step1').style.display = 'none';
  document.getElementById('pay-step2').style.display = 'none';
  document.getElementById('pay-step3').style.display = '';
  document.getElementById('pay-success-title').textContent = title;
  document.getElementById('pay-success-body').textContent = body;
}

function goBack() {
  clearInterval(timerInterval);
  depositRef = '';
  document.getElementById('pay-step2').style.display = 'none';
  document.getElementById('pay-step1').style.display = '';
  document.getElementById('pay-step2-result').innerHTML = '';
}
</script>
