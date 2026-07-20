<?php /* views/admin/audit_log.php */
$ACTION_META = [
  'ghost_login'=>['label'=>'Ghost Login','bg'=>'var(--red-bg)','color'=>'var(--red)','border'=>'var(--red-b)'],
  'kyc_approved'=>['label'=>'KYC Approved','bg'=>'var(--green-bg)','color'=>'var(--green)','border'=>'var(--green-b)'],
  'kyc_rejected'=>['label'=>'KYC Rejected','bg'=>'var(--warn-bg)','color'=>'var(--warn)','border'=>'var(--warn-b)'],
  'wallet_credit'=>['label'=>'Wallet Credit','bg'=>'var(--green-bg)','color'=>'var(--green)','border'=>'var(--green-b)'],
  'wallet_debit'=>['label'=>'Wallet Debit','bg'=>'var(--red-bg)','color'=>'var(--red)','border'=>'var(--red-b)'],
  'withdrawal_approved'=>['label'=>'Withdrawal Approved','bg'=>'var(--accent-l)','color'=>'var(--accent)','border'=>'var(--accent-m)'],
  'withdrawal_completed'=>['label'=>'Withdrawal Completed','bg'=>'var(--green-bg)','color'=>'var(--green)','border'=>'var(--green-b)'],
  'withdrawal_rejected'=>['label'=>'Withdrawal Rejected','bg'=>'var(--red-bg)','color'=>'var(--red)','border'=>'var(--red-b)'],
  'user_suspended'=>['label'=>'User Suspended','bg'=>'var(--red-bg)','color'=>'var(--red)','border'=>'var(--red-b)'],
  'investment_created'=>['label'=>'Investment Created','bg'=>'var(--accent-l)','color'=>'var(--accent)','border'=>'var(--accent-m)'],
  'investment_edited'=>['label'=>'Investment Edited','bg'=>'var(--warn-bg)','color'=>'var(--warn)','border'=>'var(--warn-b)'],
  'settings_updated'=>['label'=>'Settings Updated','bg'=>'var(--warn-bg)','color'=>'var(--warn)','border'=>'var(--warn-b)'],
  'announcement_sent'=>['label'=>'Announcement Sent','bg'=>'var(--accent-l)','color'=>'var(--accent)','border'=>'var(--accent-m)'],
  'password_reset'=>['label'=>'Password Reset','bg'=>'var(--warn-bg)','color'=>'var(--warn)','border'=>'var(--warn-b)'],
  'session_revoked'=>['label'=>'Session Revoked','bg'=>'var(--red-bg)','color'=>'var(--red)','border'=>'var(--red-b)'],
  'ticket_replied'=>['label'=>'Ticket Reply','bg'=>'var(--surface3)','color'=>'var(--text3)','border'=>'var(--border)'],
  'ticket_closed'=>['label'=>'Ticket Closed','bg'=>'var(--surface3)','color'=>'var(--text3)','border'=>'var(--border)'],
  'admin_login'=>['label'=>'Admin Login','bg'=>'var(--surface3)','color'=>'var(--text3)','border'=>'var(--border)'],
];
$SEV_COLORS = ['high'=>'var(--red)','medium'=>'var(--warn)','low'=>'var(--green)'];
?>
<div class="page-header" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1rem">
  <div><h1 class="page-title">Audit Log</h1><p class="page-sub">Tamper-evident record of all administrator actions.</p></div>
</div>

<!-- Filters -->
<form method="GET" action="/admin/audit" style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:.85rem 1.25rem;margin-bottom:1.5rem;display:flex;gap:.65rem;flex-wrap:wrap;align-items:center">
  <?= svgIcon('filter',15,'var(--text3)') ?>
  <input class="fi" type="text" name="q" placeholder="Search action or target…" value="<?= htmlspecialchars($search??'') ?>" style="flex:1;min-width:180px"/>
  <select class="fsel" name="admin" style="width:auto;padding:7px 28px 7px 10px">
    <option value="">All Admins</option>
    <?php foreach ($admins as $a): ?><option value="<?= $a['id'] ?>" <?= ($adminF??0)==$a['id']?'selected':'' ?>><?= htmlspecialchars($a['name']) ?></option><?php endforeach; ?>
  </select>
  <select class="fsel" name="action" style="width:auto;padding:7px 28px 7px 10px">
    <option value="">All Actions</option>
    <?php foreach ($actions as $a): ?><option value="<?= htmlspecialchars($a['action']) ?>" <?= ($actionF??'')===$a['action']?'selected':'' ?>><?= htmlspecialchars(ucwords(str_replace('_',' ',$a['action']))) ?></option><?php endforeach; ?>
  </select>
  <select class="fsel" name="severity" style="width:auto;padding:7px 28px 7px 10px">
    <option value="">All Severity</option>
    <option value="high" <?= ($sevF??'')==='high'?'selected':'' ?>>High</option>
    <option value="medium" <?= ($sevF??'')==='medium'?'selected':'' ?>>Medium</option>
    <option value="low" <?= ($sevF??'')==='low'?'selected':'' ?>>Low</option>
  </select>
  <button type="submit" class="btn btn-primary btn-sm">Apply</button>
  <a href="/admin/audit" class="btn btn-outline btn-sm">Clear</a>
  <span style="margin-left:auto;font-size:12px;color:var(--text3)"><?= $total ?? 0 ?> entries</span>
</form>

<div class="section">
  <?php if (empty($data)): ?>
    <div style="padding:3rem;text-align:center;color:var(--text3)"><?= svgIcon('log',28,'var(--border2)') ?><p style="margin-top:.75rem">No audit log entries match your filter.</p></div>
  <?php else: ?>
  <div class="tbl-overflow">
    <table class="data-table">
      <thead><tr><th>Severity</th><th>Timestamp</th><th>Admin</th><th>Action</th><th>Detail</th><th>IP</th></tr></thead>
      <tbody>
      <?php foreach ($data as $log):
        $meta = $ACTION_META[$log['action']] ?? ['label'=>ucwords(str_replace('_',' ',$log['action'])),'bg'=>'var(--surface3)','color'=>'var(--text3)','border'=>'var(--border)'];
        $sevColor = $SEV_COLORS[$log['severity']] ?? 'var(--text3)';
      ?>
        <tr>
          <td><div style="display:flex;align-items:center;gap:6px"><div style="width:7px;height:7px;border-radius:50%;background:<?= $sevColor ?>;flex-shrink:0"></div><span style="font-size:10.5px;font-weight:600;color:<?= $sevColor ?>"><?= ucfirst($log['severity']) ?></span></div></td>
          <td style="font-size:11.5px;color:var(--text3);font-family:monospace;white-space:nowrap"><?= htmlspecialchars(substr($log['created_at'],0,16)) ?></td>
          <td><div style="font-size:12.5px;font-weight:500"><?= htmlspecialchars($log['admin_name']) ?></div><div style="font-size:10.5px;color:var(--text3)"><?= htmlspecialchars(str_replace('_',' ',ucwords($log['admin_role']??''))) ?></div></td>
          <td><span style="display:inline-flex;align-items:center;font-size:10px;font-weight:700;padding:2px 8px;border-radius:2px;background:<?= $meta['bg'] ?>;color:<?= $meta['color'] ?>;border:1px solid <?= $meta['border'] ?>;white-space:nowrap"><?= htmlspecialchars($meta['label']) ?></span></td>
          <td style="max-width:260px"><div style="font-size:12px;color:var(--text2);white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="<?= htmlspecialchars($log['detail']) ?>"><?= htmlspecialchars($log['detail']) ?></div><?php if ($log['target_name']): ?><div style="font-size:10.5px;color:var(--text3)">Target: <?= htmlspecialchars($log['target_name']) ?></div><?php endif; ?></td>
          <td style="font-size:11px;color:var(--text3);font-family:monospace"><?= htmlspecialchars($log['ip_address']??'') ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php renderPagination($page, $pages, '/admin/audit?q='.urlencode($search??'').'&admin='.($adminF??'').'&action='.urlencode($actionF??'').'&severity='.($sevF??'').'&'); ?>
  <?php endif; ?>
</div>

<div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:1rem 1.35rem;display:flex;align-items:flex-start;gap:10px">
  <?= svgIcon('info',15,'var(--text3)') ?>
  <div style="font-size:12px;color:var(--text3);line-height:1.65">All audit log entries are immutable and cannot be deleted or modified. Logs are retained for a minimum of 7 years in compliance with financial regulatory standards. Ghost login sessions and manual balance adjustments are flagged as High severity.</div>
</div>
