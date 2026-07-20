<?php /* views/investor/certificate_pdf.php — rendered as print-friendly HTML */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<title>Certificate <?= htmlspecialchars($holding['certificate_ref']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
body { background:#F8F6F0;margin:0;padding:2rem;font-family:'Inter',sans-serif; }
.cert { background:#fff;border:1px solid #D4C89A;max-width:800px;margin:0 auto;position:relative;overflow:hidden; }
.cert-watermark { position:absolute;top:50%;left:50%;transform:translate(-50%,-50%) rotate(-30deg);font-size:80px;font-weight:700;color:rgba(27,108,168,0.04);white-space:nowrap;pointer-events:none;font-family:'DM Serif Display',serif; }
.cert-header { background:#0D1B2A;padding:1.5rem 2.5rem;display:flex;align-items:center;justify-content:space-between; }
.cert-body { padding:2.5rem; }
.cert-grid { display:grid;grid-template-columns:1fr 1fr;gap:1px;background:#E8E0C8;border:1px solid #E8E0C8;border-radius:2px;overflow:hidden;margin-bottom:1.5rem; }
.cert-cell { padding:.85rem 1.1rem;background:#fff; }
.cert-cell-lbl { font-size:9px;letter-spacing:1.5px;text-transform:uppercase;color:#8A96A8;font-weight:600;margin-bottom:.3rem; }
.cert-cell-val { font-size:14px;color:#0D1B2A;font-weight:700; }
.cert-footer { background:#F8F6F0;border-top:1px solid #E8E0C8;padding:1rem 2.5rem;display:flex;align-items:center;justify-content:space-between; }
@media print { body{padding:0;background:#fff;} .no-print{display:none!important;} }
</style>
</head>
<body>
<?php
$pName    = platform_setting('platform_name','NexVest');
$pTagline = platform_setting('platform_tagline','Capital Group');
$pInit    = platform_setting('platform_initials','NV');
$pUrl     = platform_setting('platform_website','https://nexvest.com');
$pAddr    = platform_setting('platform_address','');
$sym      = platform_setting('platform_symbol','$');
$roi    = (float)($holding['roi'] ?? $holding['inv_roi'] ?? 0);
$dv     = (int)($holding['duration_value'] ?? 0);
$du     = $holding['duration_unit'] ?? 'months';
// ROI is the total return over the full duration
$total  = (float)$holding['amount'] * $roi / 100;
?>
<div style="max-width:800px;margin:0 auto;padding-bottom:2rem">
  <div style="text-align:center;margin-bottom:1.5rem" class="no-print">
    <button onclick="window.print()" style="display:inline-flex;align-items:center;gap:7px;height:40px;padding:0 20px;border-radius:8px;background:#059669;color:#fff;border:none;font-size:13px;font-weight:600;cursor:pointer;font-family:Inter,sans-serif">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
      Download / Print PDF
    </button>
    <a href="/investor/certificates" style="display:inline-flex;align-items:center;height:40px;padding:0 20px;border-radius:8px;background:#fff;color:#0B1120;border:1.5px solid #E2E8F0;font-size:13px;font-weight:600;text-decoration:none;margin-left:.5rem;font-family:Inter,sans-serif">Back</a>
  </div>

  <div class="cert">
    <div class="cert-watermark">VERIFIED</div>

    <div class="cert-header">
      <div style="display:flex;align-items:center;gap:10px">
        <div style="width:36px;height:36px;background:#1B6CA8;border-radius:3px;display:flex;align-items:center;justify-content:center;font-family:'DM Serif Display',serif;font-size:14px;color:#fff"><?= htmlspecialchars($pInit) ?></div>
        <div><div style="font-size:18px;font-weight:700;color:#fff;font-family:'DM Serif Display',serif"><?= htmlspecialchars($pName) ?></div><div style="font-size:9px;letter-spacing:2.5px;text-transform:uppercase;color:rgba(255,255,255,.3)"><?= htmlspecialchars($pTagline) ?></div></div>
      </div>
      <div style="text-align:right">
        <div style="font-size:9px;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,.3);margin-bottom:3px">Certificate Reference</div>
        <div style="font-size:15px;font-weight:700;color:#fff;letter-spacing:1px;font-family:monospace"><?= htmlspecialchars($holding['certificate_ref']) ?></div>
      </div>
    </div>

    <div class="cert-body">
      <div style="font-size:10px;letter-spacing:3px;text-transform:uppercase;color:#8A96A8;margin-bottom:.5rem">Certificate of Investment</div>
      <div style="font-family:'DM Serif Display',serif;font-size:26px;color:#0D1B2A;margin-bottom:.25rem">This certifies that</div>

      <div style="display:flex;align-items:center;gap:14px;padding:1rem 1.25rem;background:#F8F6F0;border:1px solid #E8E0C8;border-radius:2px;margin-bottom:1.5rem">
        <div style="width:44px;height:44px;background:#0D1B2A;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:700;color:#fff;flex-shrink:0"><?= strtoupper(substr($user['first_name']??'?',0,1).substr($user['last_name']??'?',0,1)) ?></div>
        <div>
          <div style="font-size:17px;color:#0D1B2A;font-family:'DM Serif Display',serif"><?= htmlspecialchars(($user['first_name']??'').' '.($user['last_name']??'')) ?></div>
          <div style="font-size:12px;color:#8A96A8"><?= htmlspecialchars($user['email']??'') ?> · <?= htmlspecialchars($user['country']??'') ?></div>
        </div>
      </div>

      <p style="font-size:13.5px;color:#4A5568;line-height:1.7;margin-bottom:1.5rem">has made a confirmed investment in the following product offered through <strong><?= htmlspecialchars($pName.' '.$pTagline) ?></strong>, subject to the terms and conditions of the investment agreement.</p>

      <div style="height:1px;background:linear-gradient(to right,#D4C89A,transparent);margin-bottom:1.5rem"></div>

      <div class="cert-grid">
        <?php foreach ([
          ['Investment Product', $holding['inv_name']??$holding['name']??''],
          ['Investment Type',   $holding['type']==='real_estate'?'Real Estate':'Index Fund'],
          ['Total ROI',        $roi.'%'],
          ['Duration',          ($holding['duration_value']??'').' '.ucfirst($holding['duration_unit']??'months').'s'],
          ['Start Date',        fmt_date($holding['start_date'])],
          ['Maturity Date',     fmt_date($holding['end_date'])],
          ['Payment Method',    ucfirst($holding['payment_method']??'')],
          ['Certificate Ref',   $holding['certificate_ref']],
        ] as [$l,$v]): ?>
          <div class="cert-cell"><div class="cert-cell-lbl"><?= $l ?></div><div class="cert-cell-val"><?= htmlspecialchars($v) ?></div></div>
        <?php endforeach; ?>
      </div>

      <div style="background:#0D1B2A;border-radius:2px;padding:1.25rem 1.5rem;margin-bottom:1.5rem;display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;text-align:center">
        <?php foreach ([['Principal Invested',fmt_currency((float)$holding['amount']),false],['Total Return ('.$roi.'%)',fmt_currency($total),true],['Maturity Value',fmt_currency((float)$holding['amount'] + $total),true]] as [$l,$v,$g]): ?>
          <div><div style="font-size:9px;letter-spacing:2px;text-transform:uppercase;color:rgba(255,255,255,.3);font-weight:600;margin-bottom:.35rem"><?= $l ?></div><div style="font-size:20px;font-weight:700;color:<?= $g?'#4CAF82':'#fff' ?>"><?= $v ?></div></div>
        <?php endforeach; ?>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:2rem;margin-top:1.75rem">
        <?php foreach (['Chief Investment Officer','Head of Compliance'] as $role): ?>
          <div>
            <svg width="120" height="36" viewBox="0 0 120 36" fill="none" style="margin-bottom:.5rem;opacity:.5"><path d="M5 28 Q20 8 35 22 Q50 36 65 16 Q80 4 95 18 Q110 28 115 22" stroke="#0D1B2A" stroke-width="1.5" fill="none" stroke-linecap="round"/></svg>
            <div style="border-top:1px solid #D4C89A;padding-top:.5rem">
              <div style="font-size:13px;color:#0D1B2A;font-family:'DM Serif Display',serif"><?= $role ?></div>
              <div style="font-size:10px;color:#8A96A8;margin-top:2px"><?= htmlspecialchars($pName.' '.$pTagline) ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="cert-footer">
      <div style="font-size:10px;color:#8A96A8;line-height:1.6">
        Issued <?= date('F j, Y') ?> · <?= htmlspecialchars($pName) ?> · <?= htmlspecialchars($pAddr) ?><br/>
        Verify at <?= htmlspecialchars($pUrl) ?>/verify?ref=<?= htmlspecialchars($holding['certificate_ref']) ?>
      </div>
      <div style="text-align:center">
        <div style="width:72px;height:72px;border-radius:50%;border:2px solid rgba(27,108,168,.3);display:flex;flex-direction:column;align-items:center;justify-content:center;color:rgba(27,108,168,.4)">
          <div style="font-size:7px;letter-spacing:1.5px;text-transform:uppercase;font-weight:700;text-align:center;line-height:1.3">CERTIFIED<br/>INVESTMENT</div>
          <div style="font-size:14px;font-weight:700;font-family:'DM Serif Display',serif"><?= date('Y') ?></div>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
