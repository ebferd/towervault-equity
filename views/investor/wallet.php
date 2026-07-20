<?php
$sym     = platform_setting('platform_symbol', '$');
$balance = (float)($user['wallet_balance'] ?? 0);
$txTypeLabels = [
    'deposit' => 'Deposit', 'withdrawal' => 'Withdrawal', 'investment' => 'Investment',
    'return' => 'Return credited', 'referral_commission' => 'Referral commission', 'adjustment' => 'Adjustment',
];
?>
<div class="page-header">
  <div>
    <h1 class="greet">Wallet</h1>
    <p class="greet-sub">Manage your balance, deposits, and withdrawals.</p>
  </div>
</div>

<div class="balance-hero">
  <div class="balance-hero-top">
    <div class="balance-hero-glow"></div>
    <div class="balance-lbl">Available balance</div>
    <div class="balance-val"><?= fmt_currency($balance) ?></div>
    <div class="balance-sub">Last updated: just now &middot; <?= htmlspecialchars(platform_setting('platform_currency','USD')) ?></div>
  </div>
  <div class="balance-actions">
    <button class="balance-action-btn" onclick="document.getElementById('deposit-modal').classList.add('show')">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.7)" stroke-width="1.8"><?= nv_icon('deposit') ?></svg>
      Deposit
    </button>
    <button class="balance-action-btn" onclick="document.getElementById('withdraw-modal').classList.add('show')">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.7)" stroke-width="1.8"><?= nv_icon('withdraw') ?></svg>
      Withdraw
    </button>
    <button class="balance-action-btn" onclick="openTransferModal()">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.7)" stroke-width="1.8"><path d="M7 16l-4-4 4-4M17 8l4 4-4 4M14 5l-4 14"/></svg>
      Transfer
    </button>
  </div>
</div>

<div class="stat-grid stat-grid-3">
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--blue-50)"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="var(--blue-600)" stroke-width="1.8"><?= nv_icon('deposit') ?></svg></div>
    <div class="stat-lbl">Total deposited</div>
    <div class="stat-val"><?= fmt_currency((float)($total_deposited ?? 0)) ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--em-50)"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="var(--em-600)" stroke-width="1.8"><?= nv_icon('return') ?></svg></div>
    <div class="stat-lbl">Total returns</div>
    <div class="stat-val"><?= fmt_currency((float)($total_returns ?? 0)) ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--amber-100)"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="var(--amber-700)" stroke-width="1.8"><?= nv_icon('withdraw') ?></svg></div>
    <div class="stat-lbl">Total withdrawn</div>
    <div class="stat-val"><?= fmt_currency((float)($total_withdrawn ?? 0)) ?></div>
  </div>
</div>

<div class="card">
  <div class="card-head" style="border-bottom:none;padding-bottom:0">
    <span class="card-head-title">Transaction history</span>
  </div>
  <div class="tabs" style="margin:0 1.25rem 1rem;overflow-x:auto;white-space:nowrap;-webkit-overflow-scrolling:touch">
    <?php foreach ([['all','All'],['deposit','Deposits'],['withdrawal','Withdrawals'],['return','Returns'],['investment','Investments']] as [$f,$l]): ?>
      <a href="/investor/wallet?filter=<?= $f ?>" class="tab<?= ($filter ?? 'all') === $f ? ' active' : '' ?>"><?= $l ?></a>
    <?php endforeach; ?>
  </div>

  <style>
  @media(max-width:640px){.wtx-table{display:none!important}.wtx-cards{display:flex!important}}
  .wtx-cards{display:none;flex-direction:column;gap:.6rem;padding:.5rem 1.25rem 1.25rem}
  .wtx-card{background:var(--mist-50);border:1px solid var(--border);border-radius:12px;padding:.85rem 1rem;display:flex;align-items:center;gap:.85rem}
  .wtx-info{flex:1;min-width:0}
  .wtx-desc{font-size:13px;font-weight:500;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .wtx-ref{font-size:10.5px;color:var(--mist-400);font-family:monospace;margin-top:1px}
  .wtx-meta{font-size:11px;color:var(--mist-400);margin-top:3px}
  .wtx-amt{font-size:14px;font-weight:700;white-space:nowrap}
  </style>
  <?php if (empty($data)): ?>
    <div class="empty">
      <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--mist-300)" stroke-width="1.6"><?= nv_icon('inbox') ?></svg>
      <p>No transactions found.</p>
    </div>
  <?php else: ?>
    <!-- Desktop table -->
    <div class="tbl-wrap wtx-table">
      <table>
        <thead><tr><th>Transaction</th><th>Date</th><th>Method</th><th>Amount</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach ($data as $tx): [$icoName, $icoColor, $icoBg] = nv_tx_icon($tx['type']);
            $credit = in_array($tx['type'], ['return','deposit','referral_commission','adjustment'], true);
          ?>
            <tr>
              <td>
                <div style="display:flex;align-items:center;gap:10px">
                  <div class="tx-icon" style="background:<?= $icoBg ?>"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="<?= $icoColor ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><?= nv_icon($icoName) ?></svg></div>
                  <div><div style="font-size:13px;font-weight:500"><?= htmlspecialchars($tx['description'] ?? ($txTypeLabels[$tx['type']] ?? ucfirst($tx['type']))) ?></div><div style="font-size:10.5px;color:var(--mist-400);font-family:monospace"><?= htmlspecialchars($tx['reference']) ?></div></div>
                </div>
              </td>
              <td style="color:var(--mist-600);font-size:12px"><?= fmt_date($tx['created_at']) ?></td>
              <td style="color:var(--mist-600);font-size:12px"><?= htmlspecialchars($tx['method'] ?? '—') ?></td>
              <td style="font-weight:700;color:<?= $credit ? 'var(--em-600)' : 'var(--mist-900)' ?>"><?= $credit ? '+' : '-' ?><?= fmt_currency((float)$tx['amount']) ?></td>
              <td><span class="badge <?= htmlspecialchars($tx['status']) ?>"><?= ucfirst($tx['status']) ?></span></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <!-- Mobile cards -->
    <div class="wtx-cards">
      <?php foreach ($data as $tx): [$icoName, $icoColor, $icoBg] = nv_tx_icon($tx['type']);
        $credit = in_array($tx['type'], ['return','deposit','referral_commission','adjustment'], true);
      ?>
        <div class="wtx-card">
          <div class="tx-icon" style="background:<?= $icoBg ?>;flex-shrink:0"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="<?= $icoColor ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><?= nv_icon($icoName) ?></svg></div>
          <div class="wtx-info">
            <div class="wtx-desc"><?= htmlspecialchars($tx['description'] ?? ($txTypeLabels[$tx['type']] ?? ucfirst($tx['type']))) ?></div>
            <div class="wtx-ref"><?= htmlspecialchars($tx['reference']) ?></div>
            <div class="wtx-meta"><?= fmt_date($tx['created_at']) ?> <?= $tx['method'] ? '· '.htmlspecialchars($tx['method']) : '' ?></div>
          </div>
          <div style="text-align:right;flex-shrink:0">
            <div class="wtx-amt" style="color:<?= $credit ? 'var(--em-600)' : 'var(--mist-900)' ?>"><?= $credit ? '+' : '-' ?><?= fmt_currency((float)$tx['amount']) ?></div>
            <span class="badge <?= htmlspecialchars($tx['status']) ?>" style="margin-top:4px;display:inline-flex"><?= ucfirst($tx['status']) ?></span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <?php renderPagination($page, $pages, '/investor/wallet?filter=' . ($filter ?? 'all') . '&'); ?>
  <?php endif; ?>
</div>

<?php
$cryptoEnabled = platform_setting('payment_crypto','1') === '1';
$paypalEnabled = platform_setting('payment_paypal','1') === '1';
$wireEnabled   = platform_setting('payment_wire','1') === '1';
$minDeposit    = (float) platform_setting('min_deposit', '100');
$minWithdraw   = (float) platform_setting('min_withdrawal', '50');
?>
<!-- Deposit Modal -->
<div id="deposit-modal" class="modal-overlay">
  <div class="modal">
    <div class="modal-head"><h3 class="modal-title">Deposit funds</h3><button class="modal-close" onclick="document.getElementById('deposit-modal').classList.remove('show')">&times;</button></div>
    <div class="modal-body">
      <div id="dep-step1">
        <div id="dep-alert1"></div>
        <div class="fg">
          <label class="fl">Deposit amount</label>
          <div class="fi-prefix"><span class="fi-sym"><?= htmlspecialchars($sym) ?></span><input class="fi" type="number" id="dep-amount" placeholder="0.00" min="<?= $minDeposit ?>" step="1"/></div>
          <div class="fhelp">Minimum deposit: <?= fmt_currency($minDeposit) ?></div>
        </div>
        <label class="fl" style="margin-bottom:.5rem">Payment method</label>
        <?php if ($cryptoEnabled): ?>
          <div onclick="selectDep('crypto')" id="dm-crypto" class="pmethod-row"><div><div class="pmethod-name">Cryptocurrency</div><div class="pmethod-sub">BTC &middot; ETH &middot; USDT &middot; USDC</div></div><div id="dr-crypto" class="pmethod-radio"></div></div>
          <div id="dep-coin-select" style="display:none;gap:.5rem;flex-wrap:wrap;padding:.25rem 0 .75rem">
            <?php
            $coins = [
              'btc'  => ['label'=>'Bitcoin','network'=>'BTC','addr'=>platform_setting('crypto_btc_address','')],
              'eth'  => ['label'=>'Ethereum','network'=>'ERC20','addr'=>platform_setting('crypto_eth_address','')],
              'usdt' => ['label'=>'USDT','network'=>'TRC20','addr'=>platform_setting('crypto_usdt_address','')],
              'usdc' => ['label'=>'USDC','network'=>'ERC20','addr'=>platform_setting('crypto_usdc_address','')],
            ];
            foreach ($coins as $coinKey => $coinInfo): if (empty($coinInfo['addr'])) continue; ?>
              <div onclick="selectCoin('<?= $coinKey ?>')" id="coin-<?= $coinKey ?>" class="coin-chip"><?= htmlspecialchars($coinInfo['label']) ?> <span style="font-size:10px;color:var(--mist-400)"><?= $coinInfo['network'] ?></span></div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
        <?php if ($paypalEnabled): ?><div onclick="selectDep('paypal')" id="dm-paypal" class="pmethod-row"><div><div class="pmethod-name">PayPal</div><div class="pmethod-sub">Instant transfer</div></div><div id="dr-paypal" class="pmethod-radio"></div></div><?php endif; ?>
        <?php if ($wireEnabled): ?><div onclick="selectDep('wire')" id="dm-wire" class="pmethod-row"><div><div class="pmethod-name">Wire Transfer</div><div class="pmethod-sub">3&ndash;5 business days</div></div><div id="dr-wire" class="pmethod-radio"></div></div><?php endif; ?>
        <div id="dep-wire-note" class="alert-banner" style="background:var(--amber-50);border:1px solid var(--amber-100);color:var(--amber-700);display:none">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
          <span>Wire transfer details will be shown on the next screen.</span>
        </div>
        <button class="qbtn primary" style="width:100%;height:44px;margin-top:.5rem" onclick="startDeposit()" id="dep-btn1"><span>Continue</span></button>
      </div>

      <div id="dep-step2" style="display:none">
        <div class="amt-confirm"><span>Deposit amount</span><span id="dep-confirm-amount"></span></div>
        <div class="timer-box">
          <div class="timer-lbl"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="var(--mist-400)" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>Invoice expires in</div>
          <div class="timer-val" id="dep-timer">30:00</div>
          <div class="timer-sub" id="dep-timer-sub">Complete your payment before the timer expires</div>
          <div class="timer-bar-track"><div id="dep-timer-bar" class="timer-bar-fill" style="width:100%"></div></div>
        </div>
        <div id="dep-payment-details"></div>
        <div style="display:flex;gap:.65rem">
          <button class="qbtn outline" style="flex:0 0 auto;padding:0 18px" type="button" onclick="document.getElementById('dep-step1').style.display='';document.getElementById('dep-step2').style.display='none'">Back</button>
          <button class="qbtn primary" style="flex:1;height:44px" type="button" id="dep-btn2" onclick="confirmDeposit()"><span>I have sent payment</span></button>
        </div>
        <div id="dep-alert2" style="margin-top:.75rem"></div>
      </div>

      <div id="dep-step3" style="display:none" class="success-box">
        <div class="success-icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--em-600)" stroke-width="2.2"><?= nv_icon('check') ?></svg></div>
        <h3 style="margin-bottom:.35rem;font-size:16px;font-weight:600">Payment submitted</h3>
        <p style="font-size:13px;color:var(--mist-500);margin-bottom:1.5rem">Your deposit is being reviewed. You'll receive an email once it's confirmed.</p>
        <button class="qbtn outline" style="width:100%" onclick="document.getElementById('deposit-modal').classList.remove('show');location.reload()">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Withdraw Modal -->
<div id="withdraw-modal" class="modal-overlay">
  <div class="modal">
    <div class="modal-head"><h3 class="modal-title">Withdraw funds</h3><button class="modal-close" onclick="document.getElementById('withdraw-modal').classList.remove('show')">&times;</button></div>
    <div class="modal-body">
      <div id="wd-result"></div>
      <div class="mini-balance">
        <div><div class="mini-balance-lbl">Available balance</div><div class="mini-balance-val"><?= fmt_currency($balance) ?></div></div>
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--mist-400)" stroke-width="1.8"><?= nv_icon('wallet') ?></svg>
      </div>
      <form id="wd-form">
        <input type="hidden" name="_token" value="<?= csrf_token() ?>"/>
        <div class="fg">
          <label class="fl">Withdrawal amount</label>
          <div class="fi-prefix"><span class="fi-sym"><?= htmlspecialchars($sym) ?></span><input class="fi" type="number" name="amount" id="wd-amount" min="<?= $minWithdraw ?>" max="<?= $balance ?>" step="1" placeholder="0.00"/></div>
          <div class="fhelp">Minimum: <?= fmt_currency($minWithdraw) ?></div>
        </div>
        <label class="fl" style="margin-bottom:.5rem">Withdrawal method</label>
        <?php if ($wireEnabled): ?><div onclick="selectWd('wire')" id="wm-wire" class="pmethod-row"><div><div class="pmethod-name">Wire Transfer</div><div class="pmethod-sub">3&ndash;5 business days</div></div><div id="wr-wire" class="pmethod-radio"></div></div><?php endif; ?>
        <?php if ($cryptoEnabled): ?><div onclick="selectWd('crypto')" id="wm-crypto" class="pmethod-row"><div><div class="pmethod-name">Cryptocurrency</div><div class="pmethod-sub">BTC &middot; ETH &middot; USDT &middot; USDC</div></div><div id="wr-crypto" class="pmethod-radio"></div></div><?php endif; ?>
        <?php if ($paypalEnabled): ?><div onclick="selectWd('paypal')" id="wm-paypal" class="pmethod-row"><div><div class="pmethod-name">PayPal</div><div class="pmethod-sub">Instant</div></div><div id="wr-paypal" class="pmethod-radio"></div></div><?php endif; ?>
        <div id="wd-fields"></div>
        <button type="submit" class="qbtn primary" style="width:100%;height:44px;margin-top:.5rem" id="wd-btn"><span>Submit withdrawal</span></button>
      </form>
    </div>
  </div>
</div>

<!-- Transfer Modal -->
<div id="transfer-modal" class="modal-overlay">
  <div class="modal" style="max-width:420px">

    <!-- Step 1: Recipient lookup -->
    <div id="tf-step1">
      <div class="modal-head">
        <h3 class="modal-title">Send transfer</h3>
        <button class="modal-close" onclick="closeTransferModal()">&times;</button>
      </div>
      <div class="modal-body">
        <div id="tf-result1"></div>
        <div class="mini-balance">
          <div><div class="mini-balance-lbl">Available balance</div><div class="mini-balance-val" id="tf-balance-disp"><?= fmt_currency($balance) ?></div></div>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--mist-400)" stroke-width="1.8"><?= nv_icon('wallet') ?></svg>
        </div>
        <div class="fg" style="margin-top:1rem">
          <label class="fl">Recipient email address</label>
          <input class="fi" type="email" id="tf-email" placeholder="investor@example.com" oninput="tfResetRecipient()"/>
          <div class="fhelp">Enter the email address of the investor you want to send funds to.</div>
        </div>
        <button class="qbtn outline" style="width:100%;height:42px;margin-top:.25rem" id="tf-lookup-btn" onclick="tfLookup()"><span>Look up recipient</span></button>

        <!-- Recipient confirmed card (hidden until lookup succeeds) -->
        <div id="tf-recipient-card" style="display:none;margin-top:1rem;background:var(--mist-50);border:1px solid var(--border);border-radius:12px;padding:1rem 1.125rem;display:none;align-items:center;gap:.875rem">
          <div id="tf-avatar" style="width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,#3b82f6,#6366f1);display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;color:#fff;flex-shrink:0"></div>
          <div style="flex:1;min-width:0">
            <div id="tf-rname" style="font-size:14px;font-weight:600;color:var(--text)"></div>
            <div style="font-size:12px;color:var(--green);font-weight:500;margin-top:2px">✓ Verified investor</div>
          </div>
        </div>

        <div id="tf-amount-section" style="display:none;margin-top:1rem">
          <div class="fg">
            <label class="fl">Amount to send</label>
            <div class="fi-prefix"><span class="fi-sym"><?= htmlspecialchars($sym) ?></span><input class="fi" type="number" id="tf-amount" min="1" step="1" placeholder="0.00"/></div>
          </div>
          <div class="fg">
            <label class="fl">Note <span style="font-weight:400;color:var(--text3)">(optional)</span></label>
            <input class="fi" type="text" id="tf-note" maxlength="120" placeholder="Add a message…"/>
          </div>
          <button class="qbtn primary" style="width:100%;height:44px" id="tf-next-btn" onclick="tfGoConfirm()"><span>Review transfer</span></button>
        </div>
      </div>
    </div>

    <!-- Step 2: Confirm -->
    <div id="tf-step2" style="display:none">
      <div class="modal-head">
        <h3 class="modal-title">Confirm transfer</h3>
        <button class="modal-close" onclick="closeTransferModal()">&times;</button>
      </div>
      <div class="modal-body">
        <div id="tf-result2"></div>
        <!-- Avatar flow -->
        <div style="display:flex;align-items:center;justify-content:center;gap:1.25rem;margin:1rem 0 1.5rem">
          <div style="text-align:center">
            <div style="width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,#1e293b,#334155);display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:700;color:#fff;margin:0 auto"><?= strtoupper(substr($firstName??'?',0,1)) ?></div>
            <div style="font-size:11px;color:var(--text3);margin-top:4px">You</div>
          </div>
          <div style="display:flex;flex-direction:column;align-items:center;gap:4px">
            <div style="width:60px;height:2px;background:linear-gradient(to right,var(--border),var(--accent),var(--border));border-radius:2px"></div>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
          </div>
          <div style="text-align:center">
            <div id="tf-confirm-avatar" style="width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,#3b82f6,#6366f1);display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:700;color:#fff;margin:0 auto"></div>
            <div id="tf-confirm-rname-short" style="font-size:11px;color:var(--text3);margin-top:4px"></div>
          </div>
        </div>
        <!-- Amount display -->
        <div style="text-align:center;margin-bottom:1.5rem">
          <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:var(--text3);margin-bottom:6px">Transfer amount</div>
          <div id="tf-confirm-amount" style="font-size:32px;font-weight:700;color:var(--text);line-height:1"></div>
        </div>
        <!-- Details grid -->
        <div style="background:var(--mist-50);border:1px solid var(--border);border-radius:12px;padding:.875rem 1rem;font-size:13px;display:grid;gap:.625rem">
          <div style="display:flex;justify-content:space-between"><span style="color:var(--text3)">Recipient</span><span id="tf-confirm-rname" style="font-weight:600;color:var(--text)"></span></div>
          <div style="display:flex;justify-content:space-between"><span style="color:var(--text3)">Note</span><span id="tf-confirm-note" style="font-weight:500;color:var(--text2)"></span></div>
          <div style="display:flex;justify-content:space-between"><span style="color:var(--text3)">Fee</span><span style="font-weight:600;color:var(--green)">Free</span></div>
          <div style="display:flex;justify-content:space-between"><span style="color:var(--text3)">Balance after</span><span id="tf-confirm-after" style="font-weight:600;color:var(--text)"></span></div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-top:1rem">
          <button class="qbtn outline" style="height:44px" onclick="tfBack()">Back</button>
          <button class="qbtn primary" style="height:44px" id="tf-send-btn" onclick="tfSend()"><span>Send transfer</span></button>
        </div>
      </div>
    </div>

    <!-- Step 3: Success card -->
    <div id="tf-step3" style="display:none">
      <div class="modal-body" style="padding:2rem 1.5rem;text-align:center">
        <!-- Green check header -->
        <div style="width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,#10b981,#059669);display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <div style="font-size:20px;font-weight:700;color:var(--text);margin-bottom:4px">Transfer sent!</div>
        <div id="tf-success-amount" style="font-size:32px;font-weight:800;color:var(--green);margin:8px 0 6px;line-height:1"></div>
        <div id="tf-success-to" style="font-size:13px;color:var(--text2);margin-bottom:1.5rem"></div>

        <!-- Avatar flow -->
        <div style="display:flex;align-items:center;justify-content:center;gap:1rem;margin-bottom:1.5rem">
          <div style="text-align:center">
            <div style="width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,#1e293b,#334155);display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;color:#fff;margin:0 auto"><?= strtoupper(substr($firstName??'?',0,1)) ?></div>
            <div style="font-size:10px;color:var(--text3);margin-top:3px">You</div>
          </div>
          <div style="width:40px;height:2px;background:linear-gradient(to right,var(--green),#a7f3d0);border-radius:2px;position:relative">
            <div style="position:absolute;right:-6px;top:-6px;color:var(--green)">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </div>
          </div>
          <div style="text-align:center">
            <div id="tf-success-avatar" style="width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,#3b82f6,#6366f1);display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;color:#fff;margin:0 auto"></div>
            <div id="tf-success-rname-short" style="font-size:10px;color:var(--text3);margin-top:3px"></div>
          </div>
        </div>

        <!-- Details -->
        <div style="background:var(--mist-50);border:1px solid var(--border);border-radius:12px;padding:.875rem 1rem;font-size:12.5px;text-align:left;display:grid;gap:.55rem">
          <div style="display:flex;justify-content:space-between"><span style="color:var(--text3)">Reference</span><span id="tf-success-ref" style="font-family:monospace;font-size:12px;color:var(--text2)"></span></div>
          <div style="display:flex;justify-content:space-between"><span style="color:var(--text3)">Date &amp; time</span><span id="tf-success-time" style="color:var(--text2)"></span></div>
          <div style="display:flex;justify-content:space-between"><span style="color:var(--text3)">New balance</span><span id="tf-success-balance" style="font-weight:700;color:var(--text)"></span></div>
          <div style="display:flex;justify-content:space-between"><span style="color:var(--text3)">Status</span><span style="color:var(--green);font-weight:600">Completed</span></div>
        </div>
        <button class="qbtn primary" style="width:100%;height:44px;margin-top:1.25rem" onclick="closeTransferModal();location.reload()">Done</button>
      </div>
    </div>

  </div>
</div>

<!-- Wire Country Request Modal -->
<div id="wire-req-modal" class="modal-overlay">
  <div class="modal">
    <div class="modal-head">
      <h3 class="modal-title">Request Country Wire Details</h3>
      <button class="modal-close" onclick="document.getElementById('wire-req-modal').classList.remove('show')">&times;</button>
    </div>
    <div class="modal-body">
      <p style="font-size:13px;color:var(--mist-500);margin-bottom:1.25rem">Our team will send you wire transfer details specific to your country within 1 business day.</p>
      <div id="wire-req-result"></div>
      <form id="wire-req-form">
        <input type="hidden" name="_token" value="<?= csrf_token() ?>"/>
        <div class="fg">
          <label class="fl">Your Country</label>
          <input class="fi" name="country" required placeholder="e.g. Germany, Nigeria, Canada" value="<?= htmlspecialchars($user['country'] ?? '') ?>"/>
        </div>
        <div class="fg">
          <label class="fl">Additional Notes <span style="font-weight:400;color:var(--mist-400)">(optional)</span></label>
          <textarea class="fta" name="note" placeholder="e.g. preferred bank, currency, any specific requirements…" style="min-height:80px"></textarea>
        </div>
        <button type="submit" class="qbtn primary" style="width:100%;height:44px" id="wire-req-btn"><span>Send Request</span></button>
      </form>
    </div>
  </div>
</div>

<script>
// ── Deposit flow ───────────────────────────────────────────────
let depMethod = null, depCoin = null, depRef = null, depExpiry = null;

const coinLabels = {
  btc: 'Bitcoin (BTC) — Native SegWit', eth: 'Ethereum (ETH) — ERC20',
  usdt: 'Tether (USDT) — TRC20', usdc: 'USD Coin (USDC) — ERC20',
};

function selectDep(m) {
  depMethod = m; depCoin = null;
  ['crypto','paypal','wire'].forEach(id => {
    const el = document.getElementById('dm-'+id);
    const r  = document.getElementById('dr-'+id);
    if (!el) return;
    el.classList.toggle('sel', id === m);
    r.classList.toggle('sel', id === m);
  });
  document.getElementById('dep-wire-note').style.display = m === 'wire' ? 'flex' : 'none';
  const cs = document.getElementById('dep-coin-select');
  if (cs) cs.style.display = m === 'crypto' ? 'flex' : 'none';
}

function selectCoin(coin) {
  depCoin = coin;
  document.querySelectorAll('[id^="coin-"]').forEach(el => {
    el.classList.toggle('sel', el.id === 'coin-'+coin);
  });
}

async function startDeposit() {
  const amt = parseFloat(document.getElementById('dep-amount').value);
  const minDep = <?= $minDeposit ?>;
  if (!amt || amt < minDep) { document.getElementById('dep-alert1').innerHTML='<div class="alert-banner err">Minimum deposit is <?= htmlspecialchars($sym) ?><?= $minDeposit ?>.</div>'; return; }
  if (!depMethod) { document.getElementById('dep-alert1').innerHTML='<div class="alert-banner err">Please select a payment method.</div>'; return; }
  if (depMethod === 'crypto' && !depCoin) { document.getElementById('dep-alert1').innerHTML='<div class="alert-banner err">Please select a cryptocurrency.</div>'; return; }

  const btn = document.getElementById('dep-btn1');
  btn.disabled = true;
  btn.querySelector('span').innerHTML = '<span class="spinner" style="border-color:rgba(255,255,255,.4);border-top-color:#fff"></span> Processing…';
  const fd2 = new FormData();
  fd2.append('amount', amt);
  fd2.append('method', depMethod);
  fd2.append('coin', depCoin || '');
  const data = await post('/investor/deposit', fd2, true);
  btn.disabled = false;
  btn.querySelector('span').textContent = 'Continue';
  if (!data.success) { document.getElementById('dep-alert1').innerHTML='<div class="alert-banner err">'+(data.error||'Failed.')+'</div>'; return; }

  depRef = data.reference;
  depExpiry = data.expires_at;
  document.getElementById('dep-confirm-amount').textContent = '<?= htmlspecialchars($sym) ?>' + parseFloat(amt).toLocaleString('en-US',{minimumFractionDigits:2});
  document.getElementById('dep-step1').style.display = 'none';
  document.getElementById('dep-step2').style.display = '';
  document.getElementById('dep-timer-sub').textContent = 'Complete your payment before the timer expires';
  document.getElementById('dep-btn2').disabled = false;
  startCountdown(depExpiry, document.getElementById('dep-timer'), document.getElementById('dep-timer-bar'), () => {
    document.getElementById('dep-timer-sub').textContent = 'Invoice expired. Please start again.';
    document.getElementById('dep-btn2').disabled = true;
  });

  let details = '';
  if (depMethod === 'crypto' && data.wallet_address) {
    const label = coinLabels[depCoin] || depCoin.toUpperCase();
    details = `<div class="detail-box">
      <div class="detail-box-lbl">${label} Address</div>
      <div class="detail-box-val">${data.wallet_address}</div>
      <button class="copy-btn" data-copy="${data.wallet_address}"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg> Copy address</button>
    </div>
    <div class="alert-banner" style="background:var(--amber-50);border:1px solid var(--amber-100);color:var(--amber-700)">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
      <span>Send <strong>only ${depCoin.toUpperCase()}</strong> to this address. Sending the wrong asset results in permanent loss.</span>
    </div>`;
  } else if (depMethod === 'paypal') {
    <?php $ppEmail = platform_setting('paypal_email', platform_setting('platform_email','')); $ppMe = platform_setting('paypal_me_link',''); ?>
    const ppEmail = <?= json_encode($ppEmail) ?>;
    const ppMe    = <?= json_encode($ppMe) ?>;
    details = `<div class="detail-box">
      <div class="detail-box-lbl">Send PayPal payment to</div>
      <div class="detail-box-big">${ppEmail}</div>
      <button class="copy-btn" data-copy="${ppEmail}"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg> Copy email</button>
      ${ppMe ? `<a href="${ppMe}" target="_blank" class="qbtn primary" style="display:inline-flex;height:32px;margin-left:.5rem">Pay via PayPal.me</a>` : ''}
    </div>
    <div class="detail-box">
      <div class="detail-box-lbl">Reference code — include in payment note</div>
      <div class="detail-box-big" style="font-family:monospace;letter-spacing:1px">${depRef}</div>
      <button class="copy-btn" data-copy="${depRef}"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg> Copy code</button>
    </div>
    <div class="alert-banner" style="background:var(--blue-50);border:1px solid #BFDBFE;color:var(--blue-600)">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <span>Include the reference code in the PayPal payment note so we can match your payment.</span>
    </div>`;
  } else if (depMethod === 'wire') {
    const wire = <?= json_encode([
      'Bank Name' => platform_setting('wire_bank_name',''), 'Account Name' => platform_setting('wire_account_name',''),
      'Account Number' => platform_setting('wire_account_number',''), 'Routing Number' => platform_setting('wire_routing',''),
      'SWIFT / BIC' => platform_setting('wire_swift',''),
    ]) ?>;
    details = `<div style="margin-bottom:1rem">
      ${Object.entries({...wire,'Reference':depRef}).map(([k,v])=>`<div class="wire-row"><span>${k}</span><span>${v||'—'}</span></div>`).join('')}
    </div><div class="alert-banner" style="background:var(--amber-50);border:1px solid var(--amber-100);color:var(--amber-700)">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
      <span>Include the reference code in the wire transfer description. Allow 3&ndash;5 business days.</span>
    </div>
    <div style="margin-top:.85rem;padding-top:.85rem;border-top:1px solid var(--border)">
      <button type="button" class="qbtn outline" style="width:100%;height:40px;font-size:13px" onclick="document.getElementById('wire-req-modal').classList.add('show')">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:6px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        Need wire details for a different country?
      </button>
    </div>`;
  }

  document.getElementById('dep-payment-details').innerHTML = details;
  document.querySelectorAll('[data-copy]').forEach(b => {
    b.addEventListener('click', () => copyText(b.dataset.copy, b));
  });
}

async function confirmDeposit() {
  const btn = document.getElementById('dep-btn2');
  btn.disabled = true;
  btn.querySelector('span').innerHTML = '<span class="spinner" style="border-color:rgba(255,255,255,.4);border-top-color:#fff"></span> Submitting…';
  const fd = new FormData();
  fd.append('reference', depRef);
  const data = await post('/investor/deposit/confirm', fd, true);
  btn.disabled = false;
  btn.querySelector('span').textContent = 'I have sent payment';
  if (data.success) {
    document.getElementById('dep-step2').style.display = 'none';
    document.getElementById('dep-step3').style.display = '';
  } else {
    document.getElementById('dep-alert2').innerHTML = '<div class="alert-banner err">' + (data.error || 'Failed to submit.') + '</div>';
  }
}

// ── Withdrawal flow ────────────────────────────────────────────
let wdMethod = null;
const wdFields = {
  wire:   '<div class="fg"><label class="fl">Bank name</label><input class="fi" name="bank_name" required placeholder="e.g. Bank of America"/></div><div class="fg"><label class="fl">Account holder name</label><input class="fi" name="account_name" required placeholder="Full legal name"/></div><div class="fg"><label class="fl">Account number / IBAN</label><input class="fi" name="account_number" required placeholder="Enter account number"/></div><div class="fg"><label class="fl">Routing / SWIFT / BIC</label><input class="fi" name="routing" required placeholder="e.g. 021000089 or BOFAUS3N"/></div><div class="fg"><label class="fl">Bank address</label><input class="fi" name="bank_address" required placeholder="Bank branch address"/></div>',
  crypto: '<div class="fg"><label class="fl">Coin / network</label><input class="fi" name="coin" required placeholder="e.g. Bitcoin (BTC), USDT TRC20"/></div><div class="fg"><label class="fl">Wallet address</label><input class="fi" name="wallet_address" required placeholder="Enter your wallet address"/></div><div class="fg"><label class="fl">Memo / tag <span style="font-weight:400;color:var(--mist-400)">(if required)</span></label><input class="fi" name="memo" placeholder="Leave blank if not required"/></div>',
  paypal: '<div class="fg"><label class="fl">PayPal email address</label><input class="fi" type="email" name="paypal_email" required placeholder="paypal@email.com"/></div>',
};

function selectWd(m) {
  wdMethod = m;
  ['wire','crypto','paypal'].forEach(id => {
    const el = document.getElementById('wm-'+id);
    const r  = document.getElementById('wr-'+id);
    if (!el) return;
    el.classList.toggle('sel', id === m);
    r.classList.toggle('sel', id === m);
  });
  document.getElementById('wd-fields').innerHTML = wdFields[m] || '';
}

// ── Wire country request ───────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('wire-req-form');
  if (form) form.addEventListener('submit', async e => {
    e.preventDefault();
    const btn = document.getElementById('wire-req-btn');
    btn.disabled = true;
    btn.querySelector('span').innerHTML = '<span class="spinner" style="border-color:rgba(255,255,255,.4);border-top-color:#fff"></span> Sending…';
    const fd = new FormData(e.target);
    const data = await post('/investor/wire-request', fd, true);
    btn.disabled = false;
    btn.querySelector('span').textContent = 'Send Request';
    const res = document.getElementById('wire-req-result');
    if (data.success) {
      res.innerHTML = '<div class="alert-banner ok"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg><span>Request sent! Our team will contact you with wire details. Ref: <strong>' + data.reference + '</strong></span></div>';
      form.reset();
      setTimeout(() => document.getElementById('wire-req-modal').classList.remove('show'), 3000);
    } else {
      res.innerHTML = '<div class="alert-banner err">' + (data.error || 'Failed to send request.') + '</div>';
    }
  });
});

document.getElementById('wd-form').addEventListener('submit', async e => {
  e.preventDefault();
  if (!wdMethod) { document.getElementById('wd-result').innerHTML='<div class="alert-banner err">Please select a withdrawal method.</div>'; return; }
  const btn = document.getElementById('wd-btn');
  btn.disabled = true;
  btn.querySelector('span').innerHTML = '<span class="spinner" style="border-color:rgba(255,255,255,.4);border-top-color:#fff"></span> Submitting…';
  const fd = new FormData(e.target);
  fd.append('method', wdMethod);
  const data = await post('/investor/withdraw', fd, true);
  btn.disabled = false;
  btn.querySelector('span').textContent = 'Submit withdrawal';
  if (data.success) {
    document.getElementById('wd-result').innerHTML = '<div class="alert-banner ok"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg><span>Withdrawal submitted. Reference: <strong>' + data.reference + '</strong>. You will be notified once processed.</span></div>';
    e.target.reset(); wdMethod = null;
    ['wire','crypto','paypal'].forEach(id => { const el = document.getElementById('wm-'+id); if (el) el.classList.remove('sel'); });
    setTimeout(() => { document.getElementById('withdraw-modal').classList.remove('show'); location.reload(); }, 3000);
  } else {
    document.getElementById('wd-result').innerHTML = '<div class="alert-banner err">' + (data.error || 'Failed.') + '</div>';
  }
});

// ── Wallet Transfer ──────────────────────────────────────────
let tfRecipientId = null, tfRecipientInitials = '', tfRecipientName = '', tfAmountVal = 0, tfNoteVal = '';
const tfSym = '<?= addslashes($sym) ?>';
const tfBalance = <?= (float)$balance ?>;

function openTransferModal() {
  tfReset();
  document.getElementById('transfer-modal').classList.add('show');
}
function closeTransferModal() {
  document.getElementById('transfer-modal').classList.remove('show');
  tfReset();
}
function tfReset() {
  tfRecipientId = null; tfRecipientInitials = ''; tfRecipientName = ''; tfAmountVal = 0; tfNoteVal = '';
  document.getElementById('tf-email').value = '';
  document.getElementById('tf-amount').value = '';
  document.getElementById('tf-note').value = '';
  document.getElementById('tf-result1').innerHTML = '';
  document.getElementById('tf-result2').innerHTML = '';
  document.getElementById('tf-recipient-card').style.display = 'none';
  document.getElementById('tf-amount-section').style.display = 'none';
  document.getElementById('tf-step1').style.display = '';
  document.getElementById('tf-step2').style.display = 'none';
  document.getElementById('tf-step3').style.display = 'none';
}
function tfResetRecipient() {
  tfRecipientId = null;
  document.getElementById('tf-recipient-card').style.display = 'none';
  document.getElementById('tf-amount-section').style.display = 'none';
  document.getElementById('tf-result1').innerHTML = '';
}
async function tfLookup() {
  const email = document.getElementById('tf-email').value.trim();
  if (!email) { document.getElementById('tf-result1').innerHTML = '<div class="alert-banner err">Enter an email address.</div>'; return; }
  const btn = document.getElementById('tf-lookup-btn');
  setLoading(btn, true, 'Looking up…');
  const res = await fetch('/investor/transfer/lookup?email=' + encodeURIComponent(email), {headers:{'X-Requested-With':'XMLHttpRequest'}});
  const data = await res.json();
  setLoading(btn, false, 'Look up recipient');
  if (data.success) {
    tfRecipientId = data.id; tfRecipientInitials = data.initials; tfRecipientName = data.name;
    document.getElementById('tf-result1').innerHTML = '';
    document.getElementById('tf-avatar').textContent = data.initials;
    document.getElementById('tf-rname').textContent = data.name;
    const card = document.getElementById('tf-recipient-card');
    card.style.display = 'flex';
    document.getElementById('tf-amount-section').style.display = '';
    document.getElementById('tf-amount').focus();
  } else {
    document.getElementById('tf-result1').innerHTML = '<div class="alert-banner err">' + (data.error || 'Not found.') + '</div>';
  }
}
function tfGoConfirm() {
  const amt = parseFloat(document.getElementById('tf-amount').value);
  const note = document.getElementById('tf-note').value.trim();
  if (!tfRecipientId) { document.getElementById('tf-result1').innerHTML = '<div class="alert-banner err">Look up a recipient first.</div>'; return; }
  if (!amt || amt <= 0) { document.getElementById('tf-result1').innerHTML = '<div class="alert-banner err">Enter a valid amount.</div>'; return; }
  if (amt > tfBalance) { document.getElementById('tf-result1').innerHTML = '<div class="alert-banner err">Insufficient balance.</div>'; return; }
  tfAmountVal = amt; tfNoteVal = note;
  document.getElementById('tf-confirm-avatar').textContent = tfRecipientInitials;
  document.getElementById('tf-confirm-rname-short').textContent = tfRecipientName.split(' ')[0];
  document.getElementById('tf-confirm-rname').textContent = tfRecipientName;
  document.getElementById('tf-confirm-note').textContent = note || '—';
  document.getElementById('tf-confirm-amount').textContent = tfSym + amt.toFixed(2);
  document.getElementById('tf-confirm-after').textContent = tfSym + (tfBalance - amt).toFixed(2);
  document.getElementById('tf-step1').style.display = 'none';
  document.getElementById('tf-step2').style.display = '';
}
function tfBack() {
  document.getElementById('tf-step2').style.display = 'none';
  document.getElementById('tf-step1').style.display = '';
}
async function tfSend() {
  const btn = document.getElementById('tf-send-btn');
  setLoading(btn, true, 'Sending…');
  document.getElementById('tf-result2').innerHTML = '';
  const fd = new FormData();
  fd.append('receiver_id', tfRecipientId);
  fd.append('amount', tfAmountVal);
  fd.append('note', tfNoteVal);
  const data = await post('/investor/transfer', fd, true);
  setLoading(btn, false, 'Send transfer');
  if (data.success) {
    document.getElementById('tf-success-amount').textContent = tfSym + parseFloat(data.amount).toFixed(2);
    document.getElementById('tf-success-to').textContent = 'to ' + data.receiver_name;
    document.getElementById('tf-success-avatar').textContent = data.receiver_initials;
    document.getElementById('tf-success-rname-short').textContent = data.receiver_name.split(' ')[0];
    document.getElementById('tf-success-ref').textContent = data.reference;
    document.getElementById('tf-success-time').textContent = data.timestamp;
    document.getElementById('tf-success-balance').textContent = tfSym + parseFloat(data.new_balance).toFixed(2);
    document.getElementById('tf-step2').style.display = 'none';
    document.getElementById('tf-step3').style.display = '';
  } else {
    document.getElementById('tf-result2').innerHTML = '<div class="alert-banner err">' + (data.error || 'Transfer failed.') + '</div>';
  }
}
</script>
