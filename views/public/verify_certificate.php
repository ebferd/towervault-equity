<?php
$pName    = platform_setting('platform_name','NexVest');
$pTagline = platform_setting('platform_tagline','Capital Group');
$pInit    = platform_setting('platform_initials','NV');
$pUrl     = platform_setting('platform_website','https://nexvest.com');
$sym      = platform_setting('platform_symbol','$');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Verify Certificate — <?= htmlspecialchars($pName) ?></title>
<link rel="stylesheet" href="/assets/css/app.css"/>
</head>
<body style="background:var(--bg);min-height:100vh;display:flex;flex-direction:column">
<header style="background:var(--navy);padding:1rem 2rem;display:flex;align-items:center;gap:10px">
  <div style="width:32px;height:32px;background:var(--accent);border-radius:3px;display:flex;align-items:center;justify-content:center;font-family:'DM Serif Display',serif;font-size:13px;color:#fff"><?= htmlspecialchars($pInit) ?></div>
  <div><div style="font-size:14px;font-weight:600;color:#fff"><?= htmlspecialchars($pName) ?></div><div style="font-size:9px;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,.3)"><?= htmlspecialchars($pTagline) ?></div></div>
  <div style="margin-left:auto;font-size:11px;color:rgba(255,255,255,.3)">Certificate Verification Portal</div>
</header>
<main style="flex:1;display:flex;align-items:center;justify-content:center;padding:3rem 1.5rem">
  <div style="width:100%;max-width:640px">
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);padding:2rem;margin-bottom:1.5rem">
      <div style="text-align:center;margin-bottom:1.5rem">
        <div style="width:52px;height:52px;background:var(--accent-l);border:1.5px solid var(--accent-m);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto .85rem"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div>
        <h1 style="font-family:'DM Serif Display',serif;font-size:1.5rem;color:var(--text);margin-bottom:.25rem">Verify Investment Certificate</h1>
        <p style="font-size:13px;color:var(--text2)">Enter the certificate reference number to verify its authenticity.</p>
      </div>
      <form method="GET" action="/verify" style="display:flex;gap:.65rem">
        <input class="fi" type="text" name="ref" placeholder="e.g. INV-A3KP7Q" value="<?= htmlspecialchars($ref??'') ?>" style="flex:1;text-transform:uppercase;font-family:monospace;font-size:14px;letter-spacing:1px"/>
        <button type="submit" class="btn btn-primary">Verify</button>
      </form>
    </div>
    <?php if (!empty($ref)): ?>
      <?php if ($holding && $user): ?>
        <div style="background:var(--green-bg);border:1.5px solid var(--green-b);border-radius:var(--r);padding:1.5rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:12px">
          <div style="width:40px;height:40px;background:var(--green);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div>
          <div><div style="font-size:14px;font-weight:700;color:var(--green)">Certificate Verified</div><div style="font-size:12.5px;color:var(--green);margin-top:2px">This certificate is authentic and was issued by <?= htmlspecialchars($pName) ?>.</div></div>
        </div>
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);overflow:hidden">
          <div style="background:var(--navy);padding:1.25rem 1.5rem;display:flex;justify-content:space-between;align-items:center">
            <div style="font-family:'DM Serif Display',serif;font-size:1.15rem;color:#fff">Investment Certificate</div>
            <div style="font-family:monospace;font-size:13px;font-weight:700;color:rgba(255,255,255,.65);letter-spacing:1px"><?= htmlspecialchars($holding['certificate_ref']) ?></div>
          </div>
          <div style="padding:1.5rem">
            <div style="display:flex;align-items:center;gap:12px;padding:.85rem 1rem;background:var(--surface2);border:1px solid var(--border);border-radius:var(--r);margin-bottom:1.25rem">
              <div style="width:38px;height:38px;background:var(--navy);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#fff"><?= strtoupper(substr($user['first_name'],0,1).substr($user['last_name'],0,1)) ?></div>
              <div><div style="font-size:14px;font-weight:600;color:var(--text)"><?= htmlspecialchars($user['first_name'].' '.$user['last_name']) ?></div><div style="font-size:12px;color:var(--text3)"><?= htmlspecialchars($user['country']??'') ?></div></div>
            </div>
            <div style="border:1px solid var(--border);border-radius:var(--r);overflow:hidden;margin-bottom:1.25rem">
              <?php
              $totalReturn = (float)$holding['amount'] * (float)$holding['roi'] / 100;
              foreach ([['Investment',$holding['inv_name']??$holding['name']??''],['Type',$holding['type']==='real_estate'?'Real Estate':'Index Fund'],['Amount Invested',fmt_currency((float)$holding['amount'])],['Total ROI',$holding['roi'].'%'],['Total Return',fmt_currency($totalReturn)],['Start Date',fmt_date($holding['start_date'])],['Maturity Date',fmt_date($holding['end_date'])],['Status',ucfirst($holding['status'])]] as $i=>[$l,$v]):
                $bg=$i%2===0?'var(--surface2)':'var(--surface)';
              ?>
                <div style="display:flex;justify-content:space-between;padding:.65rem 1rem;background:<?= $bg ?>;border-bottom:1px solid var(--border);font-size:12.5px"><span style="color:var(--text3);font-weight:500"><?= $l ?></span><span style="font-weight:600;color:var(--text)"><?= htmlspecialchars($v) ?></span></div>
              <?php endforeach; ?>
            </div>
            <div style="background:var(--accent-l);border:1px solid var(--accent-m);border-radius:var(--r);padding:.85rem 1rem;font-size:12.5px;color:var(--accent-h)">Verified <?= date('F j, Y') ?> · Issued by <?= htmlspecialchars($pName.' '.$pTagline) ?></div>
          </div>
        </div>
      <?php else: ?>
        <div style="background:var(--red-bg);border:1.5px solid var(--red-b);border-radius:var(--r);padding:1.5rem;display:flex;align-items:center;gap:12px">
          <div style="width:40px;height:40px;background:var(--red);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></div>
          <div><div style="font-size:14px;font-weight:700;color:var(--red)">Certificate Not Found</div><div style="font-size:12.5px;color:var(--red);margin-top:2px">Reference <strong><?= htmlspecialchars($ref) ?></strong> does not match any valid certificate.</div></div>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</main>
<footer style="background:var(--navy);padding:1.25rem 2rem;text-align:center"><div style="font-size:11.5px;color:rgba(255,255,255,.3)">© <?= date('Y') ?> <?= htmlspecialchars($pName) ?> <?= htmlspecialchars($pTagline) ?> · <a href="<?= htmlspecialchars($pUrl) ?>" style="color:rgba(255,255,255,.3)">Return to Platform</a></div></footer>
<script src="/assets/js/app.js"></script>
</body>
</html>
