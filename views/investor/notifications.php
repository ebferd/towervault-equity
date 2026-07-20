<?php /* views/investor/notifications.php */ ?>
<style>
.notif-page-head{display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1rem;margin-bottom:1.25rem}
.notif-list{background:var(--surface);border:1px solid var(--border);border-radius:14px;overflow:hidden}
.notif-row{display:flex;gap:12px;padding:1rem 1.25rem;border-bottom:1px solid var(--border);cursor:pointer;transition:background .12s;align-items:flex-start}
.notif-row:last-child{border-bottom:none}
.notif-row.unread{background:var(--accent-l)}
.notif-row:hover{background:var(--mist-50)}
.notif-dot-wrap{flex-shrink:0;width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin-top:1px}
.notif-body{flex:1;min-width:0}
.notif-title{font-size:13.5px;font-weight:600;color:var(--text);margin-bottom:2px}
.notif-msg{font-size:12.5px;color:var(--text2);line-height:1.5}
.notif-time{font-size:11px;color:var(--text3);margin-top:4px}
.notif-unread-dot{width:7px;height:7px;border-radius:50%;background:var(--accent);flex-shrink:0;margin-top:6px}
</style>

<div class="notif-page-head">
  <div>
    <h1 class="greet">Notifications</h1>
    <p class="greet-sub"><?= count(array_filter($data??[], fn($n)=>!$n['is_read'])) ?> unread</p>
  </div>
  <button class="qbtn outline" style="height:36px" onclick="markAllRead()">Mark all read</button>
</div>

<?php if (empty($data)): ?>
  <div style="background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:4rem 2rem;text-align:center;color:var(--text3)">
    <?= svgIcon('bell',32,'var(--border)') ?>
    <p style="margin-top:.85rem;font-size:14px">No notifications yet.</p>
  </div>
<?php else: ?>
  <?php
  $typeIcons = [
    'return'             => ['bg'=>'#D1FAE5','icon'=>'arrowDown','color'=>'var(--green)'],
    'deposit'            => ['bg'=>'#D1FAE5','icon'=>'arrowDown','color'=>'var(--green)'],
    'investment'         => ['bg'=>'#DBEAFE','icon'=>'building','color'=>'#2563EB'],
    'withdrawal'         => ['bg'=>'#FEF3C7','icon'=>'arrowUp','color'=>'var(--warn)'],
    'kyc'                => ['bg'=>'#FEF3C7','icon'=>'shield','color'=>'var(--warn)'],
    'alert'              => ['bg'=>'#FEE2E2','icon'=>'bell','color'=>'var(--red)'],
    'login_alert'        => ['bg'=>'#FEE2E2','icon'=>'user','color'=>'var(--red)'],
    'referral_signup'    => ['bg'=>'#D1FAE5','icon'=>'gift','color'=>'var(--green)'],
    'referral_commission'=> ['bg'=>'#D1FAE5','icon'=>'gift','color'=>'var(--green)'],
    'adjustment'         => ['bg'=>'#EDE9FE','icon'=>'settings','color'=>'#7C3AED'],
    'info'               => ['bg'=>'#DBEAFE','icon'=>'info','color'=>'#2563EB'],
    'transfer_sent'      => ['bg'=>'#FEF3C7','icon'=>'arrowUp','color'=>'var(--warn)'],
    'transfer_received'  => ['bg'=>'#D1FAE5','icon'=>'arrowDown','color'=>'var(--green)'],
  ];
  ?>
  <div class="notif-list">
    <?php foreach ($data as $n):
      $ic = $typeIcons[$n['type']] ?? ['bg'=>'#F3F4F6','icon'=>'bell','color'=>'var(--text3)'];
    ?>
      <div class="notif-row<?= !$n['is_read']?' unread':'' ?>" data-id="<?= $n['id'] ?>">
        <div class="notif-dot-wrap" style="background:<?= $ic['bg'] ?>">
          <?= svgIcon($ic['icon'], 16, $ic['color']) ?>
        </div>
        <div class="notif-body">
          <?php if (!empty($n['title'])): ?>
            <div class="notif-title"><?= htmlspecialchars($n['title']) ?></div>
          <?php endif; ?>
          <div class="notif-msg"><?= htmlspecialchars($n['message']) ?></div>
          <div class="notif-time"><?= time_ago($n['created_at']) ?></div>
        </div>
        <?php if (!$n['is_read']): ?>
          <div class="notif-unread-dot"></div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
  <div style="margin-top:.75rem"><?php renderPagination($page, $pages, '/investor/notifications?'); ?></div>
<?php endif; ?>

<script>
function markAllRead() {
  post('/investor/notifications/read', { id: 0 }).then(() => location.reload());
}
</script>
