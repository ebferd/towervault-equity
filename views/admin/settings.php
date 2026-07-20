<?php /* views/admin/settings.php */ ?>
<div class="page-header"><h1 class="page-title">Platform Settings</h1><p class="page-sub">Control branding, payment methods, and platform features.</p></div>

<div id="settings-alert"></div>

<form id="settings-form" enctype="multipart/form-data">
  <input type="hidden" name="_token" value="<?= csrf_token() ?>"/>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem">

    <!-- BRANDING -->
    <div class="section">
      <div class="section-head"><span class="section-title">Branding</span></div>
      <div class="section-body">
        <div class="frow">
          <div class="fg" style="flex:2"><label class="fl">Platform Name</label><input class="fi" name="platform_name" value="<?= htmlspecialchars($branding['platform_name']??'') ?>" placeholder="e.g. NexVest"/></div>
          <div class="fg" style="flex:1"><label class="fl">Initials <span class="fl-opt">(sidebar logo)</span></label><input class="fi" name="platform_initials" value="<?= htmlspecialchars($branding['platform_initials']??'NV') ?>" placeholder="NV" maxlength="3"/></div>
        </div>
        <div class="fg"><label class="fl">Tagline</label><input class="fi" name="platform_tagline" value="<?= htmlspecialchars($branding['platform_tagline']??'') ?>" placeholder="e.g. Capital Group"/></div>
        <div class="frow">
          <div class="fg"><label class="fl">Admin Email</label><input class="fi" type="email" name="platform_email" value="<?= htmlspecialchars($branding['platform_email']??'') ?>" placeholder="admin@yourdomain.com"/></div>
          <div class="fg"><label class="fl">Support Email <span class="fl-opt">(shown to investors)</span></label><input class="fi" type="email" name="platform_support_email" value="<?= htmlspecialchars($branding['platform_support_email']??'') ?>" placeholder="support@yourdomain.com"/></div>
        </div>
        <div class="fg"><label class="fl">Phone</label><input class="fi" name="platform_phone" value="<?= htmlspecialchars($branding['platform_phone']??'') ?>"/></div>
        <div class="fg"><label class="fl">Address</label><input class="fi" name="platform_address" value="<?= htmlspecialchars($branding['platform_address']??'') ?>"/></div>
        <div class="fg"><label class="fl">Website URL</label><input class="fi" type="url" name="platform_website" value="<?= htmlspecialchars($branding['platform_website']??'') ?>"/></div>
        <div class="fg">
          <label class="fl">Post-Logout Redirect URL <span class="fl-opt">(optional — leave blank to redirect to /login)</span></label>
          <input class="fi" type="url" name="logout_redirect_url" value="<?= htmlspecialchars(platform_setting('logout_redirect_url','')) ?>" placeholder="https://yourfrontend.com"/>
          <div style="font-size:11px;color:var(--text3);margin-top:4px">When set, users are sent here after signing out — useful when using a custom frontend.</div>
        </div>
        <div class="fg">
          <label class="fl">Platform Logo <span class="fl-opt">(PNG, JPG — max 2MB)</span></label>
          <?php if (!empty($branding['platform_logo'])): ?><div style="margin-bottom:.65rem"><img src="<?= file_url($branding['platform_logo']) ?>" style="height:40px;border:1px solid var(--border);border-radius:var(--r);padding:4px" alt="Logo"/></div><?php endif; ?>
          <input type="file" class="fi" name="platform_logo" accept=".jpg,.jpeg,.png,.webp" style="padding:7px 12px"/>
        </div>
      </div>
    </div>

    <!-- RIGHT COLUMN -->
    <div style="display:flex;flex-direction:column;gap:1.5rem">

      <!-- CURRENCY -->
      <div class="section">
        <div class="section-head"><span class="section-title">Currency</span></div>
        <div class="section-body">
          <div class="frow">
            <div class="fg"><label class="fl">Currency Code</label><input class="fi" name="platform_currency" value="<?= htmlspecialchars($finance['platform_currency']??'USD') ?>" placeholder="USD"/></div>
            <div class="fg"><label class="fl">Symbol</label><input class="fi" name="platform_symbol" value="<?= htmlspecialchars($finance['platform_symbol']??'$') ?>" placeholder="$"/></div>
          </div>
        </div>
      </div>

      <!-- FEATURES -->
      <div class="section">
        <div class="section-head"><span class="section-title">Feature Toggles</span></div>
        <div style="padding:0 1.5rem">
          <?php
          $toggles = [
            ['kyc_enabled',                 'KYC Required',         'Investors must submit identity documents before investing'],
            ['two_fa_enabled',               '2FA Available',        'Allow investors to enable two-factor authentication'],
            ['registration_open',            'Registrations Open',   'Allow new investors to create accounts'],
            ['email_verification_enabled',   'Email Verification',   'Require investors to verify their email with a code before logging in. Turn this off if outbound email is not configured or not working — accounts will be auto-verified instantly.'],
            ['maintenance_mode',              'Maintenance Mode',     'Display maintenance notice to all visitors'],
          ];
          foreach ($toggles as [$key, $label, $sub]):
            $checked = ($features[$key]??'0') === '1';
          ?>
            <div class="toggle-row">
              <div><div class="tr-label"><?= $label ?></div><div class="tr-sub"><?= $sub ?></div></div>
              <label class="toggle"><input type="hidden" name="<?= $key ?>" value="0"/><input type="checkbox" name="<?= $key ?>" value="1" <?= $checked?'checked':'' ?> onchange="this.previousElementSibling.value=this.checked?'1':'0'"/><div class="t-track"></div><div class="t-thumb"></div></label>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- REFERRALS -->
      <div class="section">
        <div class="section-head"><span class="section-title">Referral Settings</span></div>
        <div class="section-body">
          <div class="fg"><label class="fl">Commission Rate (%)</label><input class="fi" type="number" name="referral_commission" value="<?= htmlspecialchars($referrals['referral_commission']??'5') ?>" min="0" max="100" step="0.1"/><div style="font-size:11px;color:var(--text3);margin-top:4px">Percentage of the referred investor's first investment.</div></div>
        </div>
      </div>
    </div>
  </div>

  <!-- LOGIN PAGE MARKETING COPY -->
  <div class="section" style="margin-top:1.5rem">
    <div class="section-head"><span class="section-title">Login Page Copy</span><span class="section-meta">Headline, description and stats shown on the sign-in / register sidebar</span></div>
    <div class="section-body">
      <div class="fg">
        <label class="fl">Headline <span class="fl-opt">(use a new line for a manual line break)</span></label>
        <textarea class="fi" name="auth_headline" rows="2" style="resize:vertical"><?= htmlspecialchars($branding['auth_headline'] ?? 'Capital that compounds quietly.') ?></textarea>
      </div>
      <div class="fg">
        <label class="fl">Sub-text</label>
        <textarea class="fi" name="auth_subtext" rows="3" style="resize:vertical"><?= htmlspecialchars($branding['auth_subtext'] ?? 'Real estate and index-fund investments, managed transparently — with the reporting and controls institutional investors expect.') ?></textarea>
      </div>
      <label class="fl" style="margin-top:.35rem">Statistics <span class="fl-opt">(leave a pair blank to hide it)</span></label>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;margin-top:.4rem">
        <?php
        $statDefaults = [['$420M+','Capital deployed'],['12,400+','Active investors'],['4.9/5','Investor rating']];
        for ($i = 1; $i <= 3; $i++): [$dv,$dl] = $statDefaults[$i-1]; ?>
          <div style="border:1px solid var(--border);border-radius:var(--r);padding:.9rem">
            <div class="fg" style="margin-bottom:.6rem"><label class="fl">Stat <?= $i ?> value</label><input class="fi" name="auth_stat<?= $i ?>_value" value="<?= htmlspecialchars($branding['auth_stat'.$i.'_value'] ?? $dv) ?>"/></div>
            <div class="fg" style="margin-bottom:0"><label class="fl">Stat <?= $i ?> label</label><input class="fi" name="auth_stat<?= $i ?>_label" value="<?= htmlspecialchars($branding['auth_stat'.$i.'_label'] ?? $dl) ?>"/></div>
          </div>
        <?php endfor; ?>
      </div>
    </div>
  </div>

  <!-- CERTIFICATE SIGNATORIES -->
  <div class="section" style="margin-top:1.5rem">
    <div class="section-head"><span class="section-title">Certificate Signatories</span><span class="section-meta">Names &amp; titles printed on investment certificates (leave name blank for a signing line only)</span></div>
    <div class="section-body">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
        <div class="fg"><label class="fl">Signatory 1 — Name</label><input class="fi" name="cert_signatory1_name" value="<?= htmlspecialchars($branding['cert_signatory1_name'] ?? '') ?>" placeholder="e.g. A. Rossiter"/></div>
        <div class="fg"><label class="fl">Signatory 1 — Title</label><input class="fi" name="cert_signatory1_title" value="<?= htmlspecialchars($branding['cert_signatory1_title'] ?? 'Chief Investment Officer') ?>" placeholder="Chief Investment Officer"/></div>
        <div class="fg"><label class="fl">Signatory 2 — Name</label><input class="fi" name="cert_signatory2_name" value="<?= htmlspecialchars($branding['cert_signatory2_name'] ?? '') ?>" placeholder="e.g. M. Adeyemi"/></div>
        <div class="fg"><label class="fl">Signatory 2 — Title</label><input class="fi" name="cert_signatory2_title" value="<?= htmlspecialchars($branding['cert_signatory2_title'] ?? 'Head of Compliance') ?>" placeholder="Head of Compliance"/></div>
      </div>
    </div>
  </div>

  <!-- PAYMENT METHODS TOGGLE -->
  <div class="section" style="margin-top:1.5rem">
    <div class="section-head"><span class="section-title">Payment Methods</span><span class="section-meta">Enable or disable deposit and withdrawal channels</span></div>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1.5rem;padding:1.5rem">
      <?php foreach ([['payment_crypto','Cryptocurrency','BTC · ETH · USDT · USDC'],['payment_paypal','PayPal','Instant transfers'],['payment_wire','Wire Transfer','Bank transfers']] as [$key,$label,$sub]):
        $enabled = ($payments[$key]??'0') === '1';
      ?>
        <div style="border:1px solid var(--border);border-radius:var(--r);padding:1.1rem;display:flex;align-items:center;justify-content:space-between;gap:1rem">
          <div><div style="font-size:13px;font-weight:600;margin-bottom:2px"><?= $label ?></div><div style="font-size:11.5px;color:var(--text3)"><?= $sub ?></div></div>
          <label class="toggle"><input type="hidden" name="<?= $key ?>" value="0"/><input type="checkbox" name="<?= $key ?>" value="1" <?= $enabled?'checked':'' ?> onchange="this.previousElementSibling.value=this.checked?'1':'0'"/><div class="t-track"></div><div class="t-thumb"></div></label>
        </div>
      <?php endforeach; ?>
    </div>
    <!-- Invoice wallet payment toggle -->
    <div style="border-top:1px solid var(--border);padding:1rem 1.5rem;display:flex;align-items:center;justify-content:space-between;gap:1rem">
      <div>
        <div style="font-size:13px;font-weight:600;margin-bottom:2px">Invoice Payment with Wallet Balance</div>
        <div style="font-size:11.5px;color:var(--text3)">Allow investors to pay admin invoices directly from their wallet balance</div>
      </div>
      <?php $iwEnabled = ($payments['invoice_wallet_payment']??'1') === '1'; ?>
      <label class="toggle"><input type="hidden" name="invoice_wallet_payment" value="0"/><input type="checkbox" name="invoice_wallet_payment" value="1" <?= $iwEnabled?'checked':'' ?> onchange="this.previousElementSibling.value=this.checked?'1':'0'"/><div class="t-track"></div><div class="t-thumb"></div></label>
    </div>
  </div>

  <!-- CRYPTO WALLET ADDRESSES -->
  <div class="section" style="margin-top:1.5rem">
    <div class="section-head"><span class="section-title">Cryptocurrency Wallet Addresses</span><span class="section-meta">Your receiving addresses — shown to investors during crypto deposits</span></div>
    <div class="section-body">
      <div style="background:var(--yellow-bg,#fffbea);border:1px solid var(--yellow-b,#f0d060);border-radius:var(--r);padding:.85rem 1rem;margin-bottom:1.25rem;font-size:12.5px;color:var(--text2)">
        ⚠️ <strong>Double-check every address.</strong> Funds sent to an incorrect address cannot be recovered.
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
        <div class="fg"><label class="fl">Bitcoin (BTC) — Native SegWit</label><input class="fi" name="crypto_btc_address" value="<?= htmlspecialchars($payments['crypto_btc_address']??'') ?>" placeholder="bc1q…" style="font-family:monospace;font-size:12px"/></div>
        <div class="fg"><label class="fl">Ethereum (ETH) — ERC20</label><input class="fi" name="crypto_eth_address" value="<?= htmlspecialchars($payments['crypto_eth_address']??'') ?>" placeholder="0x…" style="font-family:monospace;font-size:12px"/></div>
        <div class="fg"><label class="fl">Tether (USDT) — TRC20</label><input class="fi" name="crypto_usdt_address" value="<?= htmlspecialchars($payments['crypto_usdt_address']??'') ?>" placeholder="T…" style="font-family:monospace;font-size:12px"/></div>
        <div class="fg"><label class="fl">USD Coin (USDC) — ERC20</label><input class="fi" name="crypto_usdc_address" value="<?= htmlspecialchars($payments['crypto_usdc_address']??'') ?>" placeholder="0x…" style="font-family:monospace;font-size:12px"/></div>
      </div>
    </div>
  </div>

  <!-- PAYPAL DETAILS -->
  <div class="section" style="margin-top:1.5rem">
    <div class="section-head"><span class="section-title">PayPal Details</span><span class="section-meta">Shown to investors making PayPal deposits</span></div>
    <div class="section-body">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
        <div class="fg"><label class="fl">PayPal Receiving Email</label><input class="fi" type="email" name="paypal_email" value="<?= htmlspecialchars($payments['paypal_email']??'') ?>" placeholder="payments@yourdomain.com"/><div style="font-size:11px;color:var(--text3);margin-top:4px">The email investors send PayPal payments to.</div></div>
        <div class="fg"><label class="fl">PayPal.me Link <span class="fl-opt">(optional)</span></label><input class="fi" name="paypal_me_link" value="<?= htmlspecialchars($payments['paypal_me_link']??'') ?>" placeholder="https://paypal.me/yourname"/><div style="font-size:11px;color:var(--text3);margin-top:4px">Shown as a direct payment button.</div></div>
      </div>
    </div>
  </div>

  <!-- WIRE DETAILS -->
  <div class="section" style="margin-top:1.5rem">
    <div class="section-head"><span class="section-title">Wire Transfer Bank Details</span><span class="section-meta">Shown to investors during wire deposits</span></div>
    <div class="section-body">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
        <div class="fg"><label class="fl">Bank Name</label><input class="fi" name="wire_bank_name" value="<?= htmlspecialchars($payments['wire_bank_name']??'') ?>"/></div>
        <div class="fg"><label class="fl">Account Holder Name</label><input class="fi" name="wire_account_name" value="<?= htmlspecialchars($payments['wire_account_name']??'') ?>"/></div>
        <div class="fg"><label class="fl">Account Number / IBAN</label><input class="fi" name="wire_account_number" value="<?= htmlspecialchars($payments['wire_account_number']??'') ?>"/></div>
        <div class="fg"><label class="fl">Routing Number</label><input class="fi" name="wire_routing" value="<?= htmlspecialchars($payments['wire_routing']??'') ?>"/></div>
        <div class="fg"><label class="fl">SWIFT / BIC Code</label><input class="fi" name="wire_swift" value="<?= htmlspecialchars($payments['wire_swift']??'') ?>"/></div>
        <div class="fg"><label class="fl">Bank Country</label><input class="fi" name="wire_bank_country" value="<?= htmlspecialchars($payments['wire_bank_country']??'') ?>"/></div>
      </div>
    </div>
  </div>

  <!-- TRANSACTION LIMITS -->
  <div class="section" style="margin-top:1.5rem">
    <div class="section-head"><span class="section-title">Transaction Limits</span><span class="section-meta">Minimum amounts for deposits and withdrawals</span></div>
    <div class="section-body">
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem">
        <div class="fg"><label class="fl">Minimum Deposit</label><input class="fi" type="number" name="min_deposit" value="<?= htmlspecialchars($payments['min_deposit']??'100') ?>" min="1" step="1"/><div style="font-size:11px;color:var(--text3);margin-top:4px">Minimum deposit per transaction.</div></div>
        <div class="fg"><label class="fl">Minimum Withdrawal</label><input class="fi" type="number" name="min_withdrawal" value="<?= htmlspecialchars($payments['min_withdrawal']??'50') ?>" min="1" step="1"/><div style="font-size:11px;color:var(--text3);margin-top:4px">Minimum withdrawal per request.</div></div>
        <div class="fg"><label class="fl">Deposit Invoice Timeout (s)</label><input class="fi" type="number" name="deposit_timeout" value="<?= htmlspecialchars($payments['deposit_timeout']??'1800') ?>" min="300" step="60"/><div style="font-size:11px;color:var(--text3);margin-top:4px">Default: 1800 = 30 minutes.</div></div>
      </div>
    </div>
  </div>

  <!-- SMTP / EMAIL -->
  <div class="section" style="margin-top:1.5rem">
    <div class="section-head"><span class="section-title">SMTP / Email Settings</span><span class="section-meta">Outgoing email configuration for all platform emails</span></div>
    <div class="section-body">
      <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:var(--r);padding:.85rem 1rem;margin-bottom:1.25rem;font-size:12.5px;color:#1e40af">
        <?= svgIcon('info',13,'#1e40af') ?> <strong>Important:</strong> Save your settings first, then use the test button below to verify the connection.
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
        <div class="fg"><label class="fl">SMTP Host</label><input class="fi" name="smtp_host" value="<?= htmlspecialchars($smtp['smtp_host']??'') ?>" placeholder="smtp.yourdomain.com"/></div>
        <div class="fg"><label class="fl">SMTP Port</label><input class="fi" type="number" name="smtp_port" value="<?= htmlspecialchars($smtp['smtp_port']??'587') ?>" placeholder="587"/></div>
        <div class="fg"><label class="fl">SMTP Username</label><input class="fi" name="smtp_user" value="<?= htmlspecialchars($smtp['smtp_user']??'') ?>" placeholder="noreply@yourdomain.com" autocomplete="off"/></div>
        <div class="fg"><label class="fl">SMTP Password</label><input class="fi" type="password" name="smtp_pass" value="<?= htmlspecialchars($smtp['smtp_pass']??'') ?>" placeholder="••••••••" autocomplete="new-password"/></div>
        <div class="fg">
          <label class="fl">Encryption</label>
          <select class="fsel" name="smtp_secure">
            <option value="tls" <?= ($smtp['smtp_secure']??'tls')==='tls'?'selected':'' ?>>TLS (STARTTLS) — port 587</option>
            <option value="ssl" <?= ($smtp['smtp_secure']??'')==='ssl'?'selected':'' ?>>SSL — port 465</option>
            <option value="" <?= ($smtp['smtp_secure']??'')===''?'selected':'' ?>>None (not recommended)</option>
          </select>
        </div>
        <div class="fg"><label class="fl">From Name</label><input class="fi" name="smtp_from_name" value="<?= htmlspecialchars($smtp['smtp_from_name']??'') ?>" placeholder="NexVest Capital Group"/></div>
        <div class="fg"><label class="fl">Admin Notification Email <span class="fl-opt">(receives alerts for registrations, KYC, deposits, withdrawals, tickets)</span></label><input class="fi" type="email" name="admin_notification_email" value="<?= htmlspecialchars($smtp['admin_notification_email']??'') ?>" placeholder="admin@yourdomain.com"/></div>
      </div>
      <!-- SMTP Test -->
      <div style="margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid var(--border)">
        <div style="font-size:13px;font-weight:600;margin-bottom:.5rem">Test SMTP Connection</div>
        <div id="smtp-test-alert" style="margin-bottom:.65rem"></div>
        <div style="display:flex;gap:.65rem;align-items:flex-end">
          <div class="fg" style="flex:1;margin-bottom:0"><label class="fl">Send test email to</label><input class="fi" type="email" id="smtp-test-email" placeholder="your@email.com" value="<?= htmlspecialchars($smtp['smtp_user']??'') ?>"/></div>
          <button type="button" class="btn btn-outline" id="smtp-test-btn" style="height:40px;white-space:nowrap"><?= svgIcon('send',13) ?> Send test email</button>
        </div>
      </div>
    </div>
  </div>

  <!-- INTEGRATIONS / LIVE CHAT -->
  <div class="section" style="margin-top:1.5rem">
    <div class="section-head"><span class="section-title">Live Chat &amp; Integrations</span><span class="section-meta">Paste third-party widget code (e.g. Smartsupp live chat)</span></div>
    <div class="section-body">
      <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:var(--r);padding:.85rem 1rem;margin-bottom:1.25rem;font-size:12.5px;color:#1e40af">
        <?= svgIcon('info',13,'#1e40af') ?> Paste the full Smartsupp code snippet (including the <code>&lt;script&gt;</code> tags) from your Smartsupp dashboard. It is injected on every visitor-facing page. Leave blank to disable live chat.
      </div>
      <div class="fg">
        <label class="fl">Smartsupp Live Chat Code <span class="fl-opt">(full &lt;script&gt; snippet)</span></label>
        <textarea class="fi" name="smartsupp_code" rows="8" style="resize:vertical;font-family:monospace;font-size:12px" placeholder="&lt;script type=&quot;text/javascript&quot;&gt;
var _smartsupp = _smartsupp || {};
_smartsupp.key = 'YOUR_KEY_HERE';
...
&lt;/script&gt;"><?= htmlspecialchars(platform_setting('smartsupp_code','')) ?></textarea>
      </div>
    </div>
  </div>

  <div class="section" style="margin-top:1.5rem">
    <div class="section-head"><span class="section-title">Legal &amp; Compliance</span><span class="section-meta">Company details shown in footer, certificates, and legal pages</span></div>
    <div class="section-body">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
        <div class="fg"><label class="fl">Legal Company Name</label><input class="fi" name="legal_company_name" value="<?= htmlspecialchars($legal['legal_company_name']??'') ?>" placeholder="Acme Investments Ltd"/></div>
        <div class="fg"><label class="fl">Registration Number</label><input class="fi" name="legal_registration_number" value="<?= htmlspecialchars($legal['legal_registration_number']??'') ?>" placeholder="e.g. 12345678"/></div>
        <div class="fg"><label class="fl">Regulator / Authority</label><input class="fi" name="legal_regulator" value="<?= htmlspecialchars($legal['legal_regulator']??'') ?>" placeholder="e.g. FCA, SEC"/></div>
        <div class="fg"><label class="fl">Jurisdiction</label><input class="fi" name="legal_jurisdiction" value="<?= htmlspecialchars($legal['legal_jurisdiction']??'') ?>" placeholder="e.g. United Kingdom"/></div>
      </div>
      <div class="fg" style="margin-top:.25rem">
        <label class="fl">Terms of Service <span class="fl-opt">(HTML or plain text — displayed on /terms page and registration)</span></label>
        <textarea class="fi" name="legal_terms" rows="10" style="resize:vertical;font-family:monospace;font-size:12px"><?= htmlspecialchars($legal['legal_terms']??'') ?></textarea>
      </div>
      <div class="fg">
        <label class="fl">Privacy Policy <span class="fl-opt">(HTML or plain text — displayed on /privacy page)</span></label>
        <textarea class="fi" name="legal_privacy" rows="10" style="resize:vertical;font-family:monospace;font-size:12px"><?= htmlspecialchars($legal['legal_privacy']??'') ?></textarea>
      </div>
    </div>
  </div>

  <div style="margin-top:1.5rem">
    <button type="submit" class="btn btn-primary btn-lg" id="save-btn"><?= svgIcon('check',14,'#fff') ?>Save All Settings</button>
  </div>
</form>

<script>
document.getElementById('smtp-test-btn').addEventListener('click', async function() {
  const email = document.getElementById('smtp-test-email').value.trim();
  if (!email) { document.getElementById('smtp-test-alert').innerHTML='<div class="alert alert-err">Enter a recipient email first.</div>'; return; }
  setLoading(this, true, 'Sending…');
  const data = await post('/admin/settings/test-smtp', { email });
  setLoading(this, false);
  document.getElementById('smtp-test-alert').innerHTML = data.success
    ? '<div class="alert alert-ok"><?= svgIcon('check',13,'var(--green)') ?> ' + data.message + '</div>'
    : '<div class="alert alert-err">' + (data.error || 'SMTP test failed.') + '</div>';
});

document.getElementById('settings-form').addEventListener('submit', async e => {
  e.preventDefault();
  const btn  = document.getElementById('save-btn');
  setLoading(btn, true, 'Saving…');
  const fd   = new FormData(e.target);
  const data = await post('/admin/settings', fd, true);
  setLoading(btn, false);
  document.getElementById('settings-alert').innerHTML = data.success
    ? '<div class="alert alert-ok"><?= svgIcon('check',14,'var(--green)') ?> ' + data.message + '</div>'
    : '<div class="alert alert-err">' + (data.error || 'Failed to save settings.') + '</div>';
  window.scrollTo({top:0,behavior:'smooth'});
});
</script>
