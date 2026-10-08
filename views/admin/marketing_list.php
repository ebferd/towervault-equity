<?php
$qs = fn(array $extra = []) => http_build_query(array_merge(['q' => $search, 'filter' => $filter], $extra));
$filters = [
  'all'          => ['All',            (int)($totals['total'] ?? 0)],
  'subscribed'   => ['Subscribed',     (int)($totals['subscribed'] ?? 0)],
  'opened'       => ['Opened',         (int)($totals['opened'] ?? 0)],
  'not_opened'   => ['Never opened',   (int)($totals['subscribed'] ?? 0) - (int)($totals['opened'] ?? 0)],
  'unsubscribed' => ['Unsubscribed / bounced', (int)($totals['unsubscribed'] ?? 0)],
];
?>
<div class="page-head" style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap">
  <div>
    <a href="/admin/marketing" style="font-size:12.5px;color:var(--text3);text-decoration:none">&larr; Back to Marketing</a>
    <h1 class="page-title" style="margin-top:.3rem">Email list</h1>
    <p class="page-sub">Every address you have emailed from the Marketing page, with how it responded. Unsubscribed and bounced addresses are never emailed again.</p>
  </div>
  <div class="ml-actions">
    <a href="/admin/marketing/list/export?<?= $qs() ?>" class="btn btn-ghost"><?= svgIcon('file',14) ?>Download CSV</a>
    <a href="/admin/marketing?from_list=1&<?= $qs() ?>" class="btn btn-primary"><?= svgIcon('send',14,'#fff') ?>Use in a campaign</a>
  </div>
</div>

<div class="ml-tiles">
  <?php foreach ([
      ['Total addresses', (int)($totals['total'] ?? 0), '#111827'],
      ['Subscribed',      (int)($totals['subscribed'] ?? 0), '#0f7a4a'],
      ['Have opened',     (int)($totals['opened'] ?? 0), '#1E3A5F'],
      ['Unsubscribed / bounced', (int)($totals['unsubscribed'] ?? 0), '#9CA3AF'],
    ] as [$lbl, $val, $col]): ?>
    <div class="section" style="padding:1.1rem 1.25rem;margin:0">
      <div style="font-size:11px;letter-spacing:.5px;text-transform:uppercase;color:var(--text3);margin-bottom:6px"><?= $lbl ?></div>
      <div style="font-size:22px;font-weight:700;color:<?= $col ?>"><?= number_format($val) ?></div>
    </div>
  <?php endforeach; ?>
</div>

<div class="section">
  <div class="section-head" style="gap:.75rem;flex-wrap:wrap">
    <div class="ml-tabs">
      <?php foreach ($filters as $key => [$lbl, $n]): ?>
        <a href="/admin/marketing/list?<?= http_build_query(['q' => $search, 'filter' => $key]) ?>" class="ml-tab<?= $filter === $key ? ' on' : '' ?>"><?= $lbl ?> <span><?= number_format(max(0, $n)) ?></span></a>
      <?php endforeach; ?>
    </div>
    <form method="GET" action="/admin/marketing/list" style="margin-left:auto;display:flex;gap:.4rem">
      <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>"/>
      <input class="fi" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search email…" style="width:220px;padding:7px 10px;font-size:12.5px"/>
      <button class="btn btn-ghost" style="font-size:12.5px">Search</button>
    </form>
  </div>
  <div class="section-body" style="padding:0">
    <?php if (empty($rows)): ?>
      <p style="padding:1.5rem;color:var(--text3);font-size:13px">
        <?= $search !== '' || $filter !== 'all' ? 'No addresses match this search.' : 'No addresses yet. They appear here once you send a campaign from the Marketing page.' ?>
      </p>
    <?php else: ?>
    <table class="tbl" style="width:100%;border-collapse:collapse">
      <thead><tr>
        <th style="text-align:left">Email</th><th>Status</th><th>Campaigns</th><th>Opened</th><th>Clicked</th><th style="text-align:right">Last emailed</th>
      </tr></thead>
      <tbody>
        <?php foreach ($rows as $r):
          $unsub = (int)$r['unsubscribed'] === 1; $bounced = (int)$r['bounced'] === 1;
          $opens = (int)$r['opens']; $clicks = (int)$r['clicks']; ?>
          <tr>
            <td style="text-align:left;font-family:monospace;font-size:12.5px"><?= htmlspecialchars($r['email']) ?></td>
            <td style="text-align:center">
              <?php if ($bounced): ?>
                <span class="ml-pill bad">Bounced</span>
              <?php elseif ($unsub): ?>
                <span class="ml-pill off">Unsubscribed</span>
              <?php else: ?>
                <span class="ml-pill ok">Subscribed</span>
              <?php endif; ?>
            </td>
            <td style="text-align:center"><?= (int)$r['campaigns'] ?></td>
            <td style="text-align:center;color:<?= $opens ? '#1E3A5F' : '#C4C9D4' ?>;font-weight:<?= $opens ? 600 : 400 ?>">
              <?= $opens ? 'Yes' . ($r['last_opened'] ? ' <span style="font-weight:400;color:#9CA3AF">· ' . date('M j', strtotime($r['last_opened'])) . '</span>' : '') : 'No' ?>
            </td>
            <td style="text-align:center;color:<?= $clicks ? '#1E3A5F' : '#C4C9D4' ?>;font-weight:<?= $clicks ? 600 : 400 ?>"><?= $clicks ? 'Yes' : 'No' ?></td>
            <td style="text-align:right;color:#9CA3AF;font-size:12.5px"><?= $r['last_sent'] ? date('M j, Y', strtotime($r['last_sent'])) : '—' ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>
<div style="margin-top:1rem"><?php renderPagination($page, $pages, '/admin/marketing/list?' . $qs() . '&'); ?></div>

<style>
  .ml-actions{display:flex;gap:.6rem;flex-wrap:wrap}
  .ml-tiles{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.25rem}
  .ml-tabs{display:flex;gap:.3rem;flex-wrap:wrap}
  .ml-tab{font-size:12.5px;font-weight:600;color:#6B7280;text-decoration:none;padding:6px 11px;border-radius:8px}
  .ml-tab span{font-weight:500;color:#9CA3AF;margin-left:2px}
  .ml-tab:hover{background:#F5F6F8}
  .ml-tab.on{background:#F5F8FC;color:#1E3A5F;box-shadow:inset 0 0 0 1px #DCE3EE}
  .ml-pill{display:inline-block;font-size:11px;font-weight:600;padding:3px 10px;border-radius:99px}
  .ml-pill.ok{background:#E7F3EC;color:#0f7a4a}
  .ml-pill.off{background:#F1F3F6;color:#9CA3AF}
  .ml-pill.bad{background:#FDF0EE;color:#C0392B}
  .tbl th{font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:#9CA3AF;font-weight:600;padding:12px 16px;border-bottom:1px solid #F0F2F7}
  .tbl td{font-size:13px;color:#374151;padding:12px 16px;border-bottom:1px solid #F5F6F8}
  .tbl tbody tr:last-child td{border-bottom:none}
  @media(max-width:900px){.ml-tiles{grid-template-columns:1fr 1fr}}
</style>
