<?php /* views/admin/users.php */ ?>
<div class="page-header"><h1 class="page-title">Investor Accounts</h1><p class="page-sub">Manage all registered investors on the platform.</p></div>
<div class="section">
  <form method="GET" action="/admin/users" style="display:flex;gap:.65rem;padding:.85rem 1.25rem;border-bottom:1px solid var(--border);background:var(--surface2);flex-wrap:wrap">
    <input type="text" name="q" class="fi" style="flex:1;min-width:200px" placeholder="Search by name, email or country…" value="<?= htmlspecialchars($search??'') ?>"/>
    <select name="filter" class="fsel" style="width:auto;padding:7px 28px 7px 10px">
      <option value="all" <?= ($filter??'all')==='all'?'selected':'' ?>>All Users</option>
      <option value="suspended" <?= ($filter??'')==='suspended'?'selected':'' ?>>Suspended</option>
      <option value="kyc_pending" <?= ($filter??'')==='kyc_pending'?'selected':'' ?>>KYC Pending</option>
      <option value="unverified" <?= ($filter??'')==='unverified'?'selected':'' ?>>Email Unverified</option>
    </select>
    <button type="submit" class="btn btn-primary btn-sm"><?= svgIcon('filter',13,'#fff') ?>Filter</button>
  </form>
  <div class="tbl-overflow">
    <table class="data-table">
      <thead><tr><th>Investor</th><th>Country</th><th>Balance</th><th>KYC</th><th>Status</th><th>Last Login</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($data as $u): ?>
        <tr>
          <td><div style="display:flex;align-items:center;gap:9px"><div style="width:28px;height:28px;border-radius:50%;background:var(--accent);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:#fff;flex-shrink:0"><?= strtoupper(substr($u['first_name'],0,1).substr($u['last_name'],0,1)) ?></div><div><div style="font-size:13px;font-weight:500"><?= htmlspecialchars($u['first_name'].' '.$u['last_name']) ?></div><div style="font-size:11px;color:var(--text3)"><?= htmlspecialchars($u['email']) ?></div></div></div></td>
          <td style="color:var(--text2)"><?= htmlspecialchars($u['country']??'—') ?></td>
          <td style="font-weight:600"><?= fmt_currency((float)$u['wallet_balance']) ?></td>
          <td><?= badge($u['kyc_status']) ?></td>
          <td><?= badge($u['status']) ?></td>
          <td style="font-size:11.5px;color:var(--text3)"><?= $u['last_login_at'] ? time_ago($u['last_login_at']) : 'Never' ?></td>
          <td>
            <div style="display:flex;gap:4px">
              <a href="/admin/users/<?= $u['id'] ?>" class="btn btn-outline btn-sm"><?= svgIcon('edit',11) ?>Manage</a>
              <a href="/admin/ghost/<?= $u['id'] ?>" class="btn btn-sm" style="background:var(--red-bg);color:var(--red);border:1px solid var(--red-b)" onclick="return confirm('Log in as <?= htmlspecialchars(addslashes($u['first_name'])) ?>? This is logged.')"><?= svgIcon('login',11,'var(--red)') ?>Ghost</a>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($data)): ?><tr><td colspan="7" style="text-align:center;color:var(--text3);padding:2.5rem">No investors found.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php renderPagination($page, $pages, '/admin/users?q='.urlencode($search??'').'&filter='.($filter??'all').'&'); ?>
</div>
