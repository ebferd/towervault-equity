<?php /* views/admin/user_detail.php */ ?>
<div class="page-header" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1rem">
  <div><h1 class="page-title"><?= htmlspecialchars($user['first_name'].' '.$user['last_name']) ?></h1><p class="page-sub"><?= htmlspecialchars($user['email']) ?> · Joined <?= fmt_date($user['created_at']) ?></p></div>
  <div style="display:flex;gap:.5rem;flex-wrap:wrap">
    <a href="/admin/ghost/<?= $user['id'] ?>" class="btn btn-outline btn-sm" onclick="return confirm('Log in as this investor? All actions are logged.')"><?= svgIcon('login',12,'var(--red)') ?>Ghost Login</a>
    <a href="/admin/users" class="btn btn-outline btn-sm">← Back to Users</a>
  </div>
</div>

<div id="ud-alert"></div>

<div style="display:grid;grid-template-columns:300px 1fr;gap:1.5rem;align-items:start">
  <!-- Left -->
  <div style="display:flex;flex-direction:column;gap:1rem">
    <div class="section">
      <div style="padding:1.75rem;text-align:center">
        <div style="width:60px;height:60px;background:var(--accent);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:700;color:#fff;margin:0 auto .85rem"><?= strtoupper(substr($user['first_name'],0,1).substr($user['last_name'],0,1)) ?></div>
        <div style="font-size:15px;font-weight:600;margin-bottom:.2rem"><?= htmlspecialchars($user['first_name'].' '.$user['last_name']) ?></div>
        <div style="font-size:12px;color:var(--text3);margin-bottom:.75rem"><?= htmlspecialchars($user['email']) ?></div>
        <div style="display:flex;gap:.5rem;justify-content:center;flex-wrap:wrap"><?= badge($user['status']) ?><?= badge($user['kyc_status']) ?></div>
      </div>
      <div style="padding:0 1.25rem 1.25rem">
        <?php foreach ([['Country',$user['country']??'—'],['Phone',$user['phone']??'—'],['Wallet Balance',fmt_currency((float)$user['wallet_balance'])],['2FA',($user['two_fa_enabled']?'Enabled':'Disabled')],['Referral Code',$user['referral_code']??'—'],['Last Login',$user['last_login_at']?time_ago($user['last_login_at']):'Never']] as [$l,$v]): ?>
          <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--border);font-size:12.5px"><span style="color:var(--text3);font-weight:500"><?= $l ?></span><span style="font-weight:600"><?= htmlspecialchars($v) ?></span></div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Quick actions -->
    <div class="section">
      <div class="section-head"><span class="section-title">Quick Actions</span></div>
      <div style="padding:1.25rem;display:flex;flex-direction:column;gap:.5rem">
        <button class="btn btn-outline btn-sm btn-block" onclick="document.getElementById('edit-modal').style.display='flex'"><?= svgIcon('edit',12) ?>Edit Details</button>
        <button class="btn btn-outline btn-sm btn-block" onclick="document.getElementById('credit-modal').style.display='flex'"><?= svgIcon('arrowDown',12) ?>Credit Wallet</button>
        <button class="btn btn-outline btn-sm btn-block" onclick="document.getElementById('debit-modal').style.display='flex'"><?= svgIcon('arrowUp',12) ?>Debit Wallet</button>
        <button class="btn btn-outline btn-sm btn-block" onclick="document.getElementById('email-modal').style.display='flex'"><?= svgIcon('send',12) ?>Email Investor</button>
        <button class="btn btn-outline btn-sm btn-block" onclick="document.getElementById('invoice-modal').style.display='flex'"><?= svgIcon('file',12) ?>Issue Invoice</button>
        <button class="btn btn-sm btn-block" style="background:var(--<?= $user['status']==='suspended'?'green':'red' ?>-bg);color:var(--<?= $user['status']==='suspended'?'green':'red' ?>);border:1px solid var(--<?= $user['status']==='suspended'?'green-b':'red-b' ?>)" onclick="toggleSuspend()"><?= svgIcon($user['status']==='suspended'?'check':'x',12,'currentColor') ?><?= $user['status']==='suspended'?'Unsuspend Account':'Suspend Account' ?></button>
      </div>
    </div>
  </div>

  <!-- Right tabs -->
  <div>
    <div class="tabs" style="margin-bottom:1rem;background:var(--surface);border:1px solid var(--border);border-bottom:none;border-radius:var(--r) var(--r) 0 0;padding:0 1.25rem">
      <?php foreach ([['holdings','Investments'],['transactions','Transactions'],['referrals','Referrals & Commissions'],['tickets','Support Tickets'],['sessions','Sessions']] as [$t,$l]): ?>
        <a href="#<?= $t ?>" class="tab" onclick="showTab('<?= $t ?>');return false;"><?= $l ?></a>
      <?php endforeach; ?>
    </div>

    <div class="section" style="border-top:none;border-radius:0 0 var(--r) var(--r);margin:0">
      <!-- Holdings -->
      <div id="tab-holdings">
        <div class="tbl-overflow">
          <table class="data-table">
            <thead><tr><th>Investment</th><th>Amount</th><th>ROI</th><th>Earned</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($holdings as $h): ?>
              <tr><td style="font-weight:500"><?= htmlspecialchars($h['name']) ?></td><td style="font-weight:600"><?= fmt_currency((float)$h['amount']) ?></td><td style="color:var(--green);font-weight:600"><?= $h['roi'] ?>%</td><td style="color:var(--green)"><?= fmt_currency((float)$h['total_earned']) ?></td><td><?= badge($h['status']) ?></td></tr>
            <?php endforeach; ?>
            <?php if (empty($holdings)): ?><tr><td colspan="5" style="text-align:center;color:var(--text3);padding:2rem">No investments.</td></tr><?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
      <!-- Transactions -->
      <div id="tab-transactions" style="display:none">
        <div class="tbl-overflow">
          <table class="data-table">
            <thead><tr><th>Description</th><th>Ref</th><th>Amount</th><th>Date</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($transactions as $tx):
              $credit = in_array($tx['type'],['return','deposit','referral_commission','adjustment']);
            ?>
              <tr><td style="font-weight:500"><?= htmlspecialchars($tx['description']??ucfirst($tx['type'])) ?></td><td style="font-family:monospace;font-size:11.5px;color:var(--text3)"><?= htmlspecialchars($tx['reference']) ?></td><td style="font-weight:700;color:<?= $credit?'var(--green)':'var(--text)' ?>"><?= $credit?'+':'-' ?><?= fmt_currency((float)$tx['amount']) ?></td><td style="font-size:12px;color:var(--text3)"><?= fmt_date($tx['created_at']) ?></td><td><?= badge($tx['status']) ?></td></tr>
            <?php endforeach; ?>
            <?php if (empty($transactions)): ?><tr><td colspan="5" style="text-align:center;color:var(--text3);padding:2rem">No transactions.</td></tr><?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
      <!-- Referrals & Commissions -->
      <div id="tab-referrals" style="display:none">
        <?php if ($referralAsReferred): ?>
          <div style="background:var(--mist-50);border:1px solid var(--border);border-radius:var(--r);padding:.85rem 1rem;margin:.75rem 1.25rem;font-size:12.5px">
            <div style="font-weight:600;margin-bottom:.3rem;color:var(--text2)">Referred by</div>
            <div><?= htmlspecialchars($referralAsReferred['ref_first'].' '.$referralAsReferred['ref_last']) ?> &nbsp;&middot;&nbsp; <?= htmlspecialchars($referralAsReferred['ref_email']) ?></div>
            <div style="color:var(--text3);margin-top:.2rem">Total commission paid to referrer: <strong style="color:var(--green)"><?= fmt_currency((float)$referralAsReferred['commission_amount']) ?></strong></div>
          </div>
        <?php else: ?>
          <div style="padding:1.25rem;font-size:12.5px;color:var(--text3)">This investor was not referred by anyone.</div>
        <?php endif; ?>

        <?php if (!empty($referralAsReferrer)): ?>
          <div style="padding:.5rem 1.25rem .25rem;font-size:12px;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:.07em">Investors referred by this user</div>
          <div class="tbl-overflow">
            <table class="data-table">
              <thead><tr><th>Investor</th><th>Email</th><th>Joined</th><th>Commission Earned</th><th>Status</th></tr></thead>
              <tbody>
              <?php foreach ($referralAsReferrer as $r): ?>
                <tr>
                  <td style="font-weight:500"><?= htmlspecialchars($r['inv_first'].' '.$r['inv_last']) ?></td>
                  <td style="font-size:12px;color:var(--text3)"><?= htmlspecialchars($r['inv_email']) ?></td>
                  <td style="font-size:12px;color:var(--text3)"><?= fmt_date($r['created_at']) ?></td>
                  <td style="font-weight:600;color:var(--green)"><?= fmt_currency((float)$r['commission_amount']) ?></td>
                  <td><?= badge($r['status']) ?></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>

        <?php if (!empty($commissionTx)): ?>
          <div style="padding:.75rem 1.25rem .25rem;font-size:12px;font-weight:700;color:var(--text3);text-transform:uppercase;letter-spacing:.07em">Commission Transaction History</div>
          <div class="tbl-overflow">
            <table class="data-table">
              <thead><tr><th>Reference</th><th>Amount</th><th>Date</th><th>Status</th></tr></thead>
              <tbody>
              <?php foreach ($commissionTx as $tx): ?>
                <tr>
                  <td style="font-family:monospace;font-size:11.5px;color:var(--text3)"><?= htmlspecialchars($tx['reference']) ?></td>
                  <td style="font-weight:700;color:var(--green)">+<?= fmt_currency((float)$tx['amount']) ?></td>
                  <td style="font-size:12px;color:var(--text3)"><?= fmt_date($tx['created_at']) ?></td>
                  <td><?= badge($tx['status']) ?></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>

        <?php if (!$referralAsReferred && empty($referralAsReferrer)): ?>
          <div style="text-align:center;padding:2.5rem 2rem;color:var(--text3);font-size:13px">No referral activity for this investor.</div>
        <?php endif; ?>
      </div>
      <!-- Tickets -->
      <div id="tab-tickets" style="display:none">
        <div class="tbl-overflow">
          <table class="data-table">
            <thead><tr><th>Reference</th><th>Subject</th><th>Status</th><th>Date</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($tickets as $t): ?>
              <tr><td style="font-family:monospace;font-size:11.5px;color:var(--text3)"><?= htmlspecialchars($t['reference']) ?></td><td style="font-weight:500"><?= htmlspecialchars($t['subject']) ?></td><td><?= badge($t['status']) ?></td><td style="font-size:12px;color:var(--text3)"><?= fmt_date($t['created_at']) ?></td><td><a href="/admin/tickets?ticket=<?= $t['id'] ?>" class="btn btn-outline btn-sm"><?= svgIcon('eye',11) ?>View</a></td></tr>
            <?php endforeach; ?>
            <?php if (empty($tickets)): ?><tr><td colspan="5" style="text-align:center;color:var(--text3);padding:2rem">No tickets.</td></tr><?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
      <!-- Sessions -->
      <div id="tab-sessions" style="display:none">
        <div class="tbl-overflow">
          <table class="data-table">
            <thead><tr><th>IP Address</th><th>Device</th><th>Last Active</th><th>Expires</th></tr></thead>
            <tbody>
            <?php foreach ($sessions as $s): ?>
              <tr><td style="font-family:monospace;font-size:12px"><?= htmlspecialchars($s['ip_address']??'') ?></td><td style="font-size:12px;color:var(--text2);max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($s['user_agent']??'Unknown') ?></td><td style="font-size:12px;color:var(--text3)"><?= time_ago($s['last_active']) ?></td><td style="font-size:12px;color:var(--text3)"><?= fmt_datetime($s['expires_at']) ?></td></tr>
            <?php endforeach; ?>
            <?php if (empty($sessions)): ?><tr><td colspan="4" style="text-align:center;color:var(--text3);padding:2rem">No active sessions.</td></tr><?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Edit Investor Details Modal -->
<div id="edit-modal" class="modal-overlay" style="display:none">
  <div class="modal" style="max-width:520px">
    <div class="modal-head"><h3 class="modal-title">Edit Investor Details</h3><button class="modal-close" onclick="document.getElementById('edit-modal').style.display='none'">&times;</button></div>
    <div class="modal-body">
      <div id="edit-result" style="margin-bottom:.65rem"></div>
      <form id="edit-form">
        <input type="hidden" name="_token" value="<?= csrf_token() ?>"/>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem">
          <div class="fg"><label class="fl">First Name</label><input class="fi" name="first_name" required value="<?= htmlspecialchars($user['first_name']) ?>"/></div>
          <div class="fg"><label class="fl">Last Name</label><input class="fi" name="last_name" required value="<?= htmlspecialchars($user['last_name']) ?>"/></div>
        </div>
        <div class="fg"><label class="fl">Email Address</label><input class="fi" type="email" name="email" required value="<?= htmlspecialchars($user['email']) ?>"/></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem">
          <div class="fg"><label class="fl">Phone</label><input class="fi" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>"/></div>
          <div class="fg"><label class="fl">Country</label><input class="fi" name="country" value="<?= htmlspecialchars($user['country'] ?? '') ?>"/></div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem">
          <div class="fg">
            <label class="fl">Account Status</label>
            <select class="fsel" name="status">
              <?php foreach (['active'=>'Active','suspended'=>'Suspended','banned'=>'Banned'] as $v=>$l): ?>
                <option value="<?= $v ?>" <?= ($user['status']??'active')===$v?'selected':'' ?>><?= $l ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="fg">
            <label class="fl">KYC Status</label>
            <select class="fsel" name="kyc_status">
              <?php foreach (['not_submitted'=>'Not Submitted','pending'=>'Pending','verified'=>'Verified','rejected'=>'Rejected'] as $v=>$l): ?>
                <option value="<?= $v ?>" <?= ($user['kyc_status']??'pending')===$v?'selected':'' ?>><?= $l ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div style="margin-top:.4rem;padding-top:.9rem;border-top:1px solid var(--border)">
          <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text3);margin-bottom:.65rem">Account Restrictions</div>

          <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;margin-bottom:.85rem">
            <div>
              <div style="font-size:13px;font-weight:600">Disable Withdrawals</div>
              <div style="font-size:11.5px;color:var(--text3)">Blocks this investor from requesting any withdrawal.</div>
            </div>
            <label class="toggle">
              <input type="hidden" name="withdrawals_disabled" value="0"/>
              <input type="checkbox" name="withdrawals_disabled" value="1" <?= !empty($user['withdrawals_disabled'])?'checked':'' ?> onchange="this.previousElementSibling.value=this.checked?'1':'0'"/>
              <div class="t-track"></div><div class="t-thumb"></div>
            </label>
          </div>

          <div class="fg">
            <label class="fl">Minimum Investment Override <span class="fl-opt">(leave blank for platform default)</span></label>
            <input class="fi" type="number" name="min_investment_override" min="0" step="0.01"
                   value="<?= $user['min_investment_override']!==null ? htmlspecialchars((string)(float)$user['min_investment_override']) : '' ?>"
                   placeholder="e.g. 5000"/>
          </div>
          <div class="fg">
            <label class="fl">Reason / Note to Investor <span class="fl-opt">(shown to the user when they try to invest below this amount)</span></label>
            <textarea class="fi" name="min_investment_note" rows="2" style="resize:vertical" placeholder="e.g. Your account is on a premium tier requiring a higher minimum."><?= htmlspecialchars($user['min_investment_note'] ?? '') ?></textarea>
          </div>
        </div>

        <div style="display:flex;gap:.65rem;margin-top:.5rem">
          <button type="submit" class="btn btn-primary" id="edit-btn"><?= svgIcon('check',13,'#fff') ?>Save Changes</button>
          <button type="button" class="btn btn-outline" onclick="document.getElementById('edit-modal').style.display='none'">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Credit Modal -->
<div id="credit-modal" class="modal-overlay" style="display:none">
  <div class="modal" style="max-width:400px">
    <div class="modal-head"><h3 class="modal-title">Credit Wallet</h3><button class="modal-close" onclick="document.getElementById('credit-modal').style.display='none'">&times;</button></div>
    <div class="modal-body">
      <div id="credit-result"></div>
      <form id="credit-form">
        <input type="hidden" name="_token" value="<?= csrf_token() ?>"/>
        <input type="hidden" name="type" value="credit"/>
        <div class="fg"><label class="fl">Amount (<?= htmlspecialchars(platform_setting('platform_symbol','$')) ?>)</label><input class="fi" type="number" name="amount" min="0.01" step="0.01" required placeholder="0.00"/></div>
        <div class="fg"><label class="fl">Internal Note (required)</label><textarea class="fta" name="note" style="min-height:70px" required placeholder="Reason for credit…"></textarea></div>
        <div style="display:flex;gap:.65rem">
          <button type="submit" class="btn btn-success"><?= svgIcon('arrowDown',13,'#fff') ?>Credit</button>
          <button type="button" class="btn btn-outline" onclick="document.getElementById('credit-modal').style.display='none'">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Debit Modal -->
<div id="debit-modal" class="modal-overlay" style="display:none">
  <div class="modal" style="max-width:400px">
    <div class="modal-head"><h3 class="modal-title">Debit Wallet</h3><button class="modal-close" onclick="document.getElementById('debit-modal').style.display='none'">&times;</button></div>
    <div class="modal-body">
      <div id="debit-result"></div>
      <form id="debit-form">
        <input type="hidden" name="_token" value="<?= csrf_token() ?>"/>
        <input type="hidden" name="type" value="debit"/>
        <div class="fg"><label class="fl">Amount (<?= htmlspecialchars(platform_setting('platform_symbol','$')) ?>)</label><input class="fi" type="number" name="amount" min="0.01" step="0.01" required placeholder="0.00"/></div>
        <div class="fg"><label class="fl">Internal Note (required)</label><textarea class="fta" name="note" style="min-height:70px" required placeholder="Reason for debit…"></textarea></div>
        <div style="display:flex;gap:.65rem">
          <button type="submit" class="btn btn-danger"><?= svgIcon('arrowUp',13,'#fff') ?>Debit</button>
          <button type="button" class="btn btn-outline" onclick="document.getElementById('debit-modal').style.display='none'">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Issue Invoice Modal -->
<div id="invoice-modal" class="modal-overlay" style="display:none">
  <div class="modal" style="max-width:440px">
    <div class="modal-head">
      <h3 class="modal-title"><?= svgIcon('file',14) ?> Issue Payment Invoice</h3>
      <button class="modal-close" onclick="document.getElementById('invoice-modal').style.display='none'">&times;</button>
    </div>
    <div class="modal-body">
      <div id="invoice-result"></div>
      <div class="alert alert-info" style="margin-bottom:.85rem;font-size:12px">Invoice will appear on the investor's dashboard and be sent by email.</div>
      <form id="invoice-form">
        <input type="hidden" name="_token" value="<?= csrf_token() ?>"/>
        <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>"/>
        <div class="fg"><label class="fl">Investor</label><input class="fi" readonly value="<?= htmlspecialchars($user['first_name'].' '.$user['last_name'].' — '.$user['email']) ?>" style="background:var(--mist-50);color:var(--text3)"/></div>
        <div class="fg"><label class="fl">Invoice title <span style="color:var(--red)">*</span></label><input class="fi" name="title" required placeholder="e.g. Account verification fee"/></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem">
          <div class="fg"><label class="fl">Amount (<?= htmlspecialchars(platform_setting('platform_symbol','$')) ?>) <span style="color:var(--red)">*</span></label><input class="fi" type="number" name="amount" min="0.01" step="0.01" required placeholder="0.00"/></div>
          <div class="fg"><label class="fl">Due date <span style="color:var(--red)">*</span></label><input class="fi" type="date" name="due_date" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>"/></div>
        </div>
        <div class="fg"><label class="fl">Payment method</label>
          <select class="fi" name="payment_method">
            <option value="any">Any available method</option>
            <option value="crypto">Crypto only</option>
            <option value="paypal">PayPal only</option>
            <option value="wire">Wire transfer only</option>
          </select>
        </div>
        <div class="fg"><label class="fl">Description / reason</label><textarea class="fta" name="description" style="min-height:70px" placeholder="Explain what this invoice is for…"></textarea></div>
        <div style="display:flex;gap:.65rem">
          <button type="submit" class="btn btn-primary" id="invoice-btn"><?= svgIcon('send',13,'#fff') ?>Send Invoice</button>
          <button type="button" class="btn btn-outline" onclick="document.getElementById('invoice-modal').style.display='none'">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Email Investor Modal -->
<div id="email-modal" class="modal-overlay" style="display:none">
  <div class="modal" style="max-width:480px">
    <div class="modal-head">
      <h3 class="modal-title">Email <?= htmlspecialchars($user['first_name'].' '.$user['last_name']) ?></h3>
      <button class="modal-close" onclick="document.getElementById('email-modal').style.display='none'">&times;</button>
    </div>
    <div class="modal-body">
      <div id="email-result" style="margin-bottom:.65rem"></div>
      <div style="font-size:12px;color:var(--text3);margin-bottom:1rem">Sending to: <strong><?= htmlspecialchars($user['email']) ?></strong></div>
      <form id="email-form">
        <input type="hidden" name="_token" value="<?= csrf_token() ?>"/>
        <div class="fg"><label class="fl">Subject</label><input class="fi" name="subject" required placeholder="e.g. Important update about your account"/></div>
        <div class="fg"><label class="fl">Message</label><textarea class="fta" name="message" style="min-height:130px" required placeholder="Write your message here…"></textarea></div>
        <div style="display:flex;gap:.65rem">
          <button type="submit" class="btn btn-primary" id="email-btn"><?= svgIcon('send',13,'#fff') ?>Send Email</button>
          <button type="button" class="btn btn-outline" onclick="document.getElementById('email-modal').style.display='none'">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
const TABS = ['holdings','transactions','referrals','tickets','sessions'];
function showTab(t) {
  TABS.forEach(id => document.getElementById('tab-'+id).style.display = id===t?'':'none');
  document.querySelectorAll('.tabs .tab').forEach((el,i) => { el.classList.toggle('active', TABS[i]===t); });
}
showTab('holdings');

async function walletAction(formId, resultId, modalId) {
  const form = document.getElementById(formId);
  const data = await post('/admin/users/<?= $user['id'] ?>/credit', new FormData(form), true);
  document.getElementById(resultId).innerHTML = data.success
    ? '<div class="alert alert-ok"><?= svgIcon('check',14,'var(--green)') ?> Done. New balance: <?= htmlspecialchars(platform_setting('platform_symbol','$')) ?>' + parseFloat(data.new_balance).toLocaleString('en-US',{minimumFractionDigits:2}) + '</div>'
    : '<div class="alert alert-err">' + (data.error||'Failed.') + '</div>';
  if (data.success) setTimeout(()=>location.reload(),2000);
}

document.getElementById('credit-form').addEventListener('submit', e => { e.preventDefault(); walletAction('credit-form','credit-result','credit-modal'); });
document.getElementById('debit-form').addEventListener('submit',  e => { e.preventDefault(); walletAction('debit-form','debit-result','debit-modal');   });

async function toggleSuspend() {
  const action = '<?= $user['status']==='suspended' ? 'unsuspend' : 'suspend' ?>';
  if (!confirm(action === 'suspend' ? 'Suspend this investor account?' : 'Unsuspend this account?')) return;
  const data = await post('/admin/users/<?= $user['id'] ?>/suspend', {action});
  if (data.success) location.reload();
  else showFlash(data.error||'Failed.','err');
}

document.getElementById('edit-form').addEventListener('submit', async e => {
  e.preventDefault();
  const btn  = document.getElementById('edit-btn');
  setLoading(btn, true, 'Saving…');
  const fd   = new FormData(e.target);
  const data = await post('/admin/users/<?= $user['id'] ?>', fd, true);
  setLoading(btn, false);
  document.getElementById('edit-result').innerHTML = data.success
    ? '<div class="alert alert-ok"><?= svgIcon('check',13,'var(--green)') ?> Details updated successfully.</div>'
    : '<div class="alert alert-err">' + (data.error || 'Failed to save.') + '</div>';
  if (data.success) setTimeout(() => location.reload(), 1500);
});

document.getElementById('invoice-form').addEventListener('submit', async e => {
  e.preventDefault();
  const btn  = document.getElementById('invoice-btn');
  setLoading(btn, true, 'Sending…');
  const fd   = new FormData(e.target);
  const data = await post('/admin/invoices', fd, true);
  setLoading(btn, false);
  document.getElementById('invoice-result').innerHTML = data.success
    ? '<div class="alert alert-ok"><?= svgIcon('check',13,'var(--green)') ?> Invoice sent. Ref: ' + data.reference + '</div>'
    : '<div class="alert alert-err">' + (data.error || 'Failed to send invoice.') + '</div>';
  if (data.success) { e.target.reset(); setTimeout(()=>document.getElementById('invoice-modal').style.display='none',2500); }
});

document.getElementById('email-form').addEventListener('submit', async e => {
  e.preventDefault();
  const btn  = document.getElementById('email-btn');
  setLoading(btn, true, 'Sending…');
  const fd   = new FormData(e.target);
  const data = await post('/admin/users/<?= $user['id'] ?>/email', fd, true);
  setLoading(btn, false);
  document.getElementById('email-result').innerHTML = data.success
    ? '<div class="alert alert-ok"><?= svgIcon('check',13,'var(--green)') ?> ' + data.message + '</div>'
    : '<div class="alert alert-err">' + (data.error || 'Failed to send.') + '</div>';
  if (data.success) { e.target.reset(); setTimeout(()=>document.getElementById('email-modal').style.display='none',2500); }
});
</script>
