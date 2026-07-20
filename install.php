<?php
/**
 * NexVest Web Installer
 * 
 * USAGE:
 * 1. Upload the nexvest folder contents to your domain root
 * 2. Run: composer install (via SSH in your domain folder)
 * 3. Visit: https://yourdomain.com/install.php
 * 4. Follow the steps
 * 5. Installer deletes itself when done
 */

define('ROOT', __DIR__);
$step   = (int)($_GET['step'] ?? 0);
$errors = [];

function testDB(string $h, string $n, string $u, string $p): bool|string {
    try {
        new PDO("mysql:host=$h;dbname=$n;charset=utf8mb4", $u, $p, [PDO::ATTR_TIMEOUT=>5, PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
        return true;
    } catch (PDOException $e) { return $e->getMessage(); }
}

function importSchema(string $h, string $n, string $u, string $p): bool|string {
    try {
        $pdo = new PDO("mysql:host=$h;dbname=$n;charset=utf8mb4", $u, $p, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
        $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
        $pdo->exec("SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO'");

        $sql     = file_get_contents(ROOT.'/database/schema.sql');
        $buffer  = '';
        $inStr   = false;
        $strChar = '';
        $len     = strlen($sql);

        // Skip statements that conflict with existing cPanel database
        $skipPrefixes = ['CREATE DATABASE', 'USE `'];

        for ($i = 0; $i < $len; $i++) {
            $ch = $sql[$i];

            // Track string boundaries to avoid splitting inside values
            if (!$inStr && ($ch === "'" || $ch === '"' || $ch === '`')) {
                $inStr   = true;
                $strChar = $ch;
                $buffer .= $ch;
                continue;
            }
            if ($inStr) {
                $buffer .= $ch;
                // Handle escaped quote
                if ($ch === '\\' && $i + 1 < $len) {
                    $buffer .= $sql[++$i];
                    continue;
                }
                if ($ch === $strChar) $inStr = false;
                continue;
            }

            // Skip single-line comments
            if ($ch === '-' && $i + 1 < $len && $sql[$i+1] === '-') {
                while ($i < $len && $sql[$i] !== "\n") $i++;
                continue;
            }

            // Skip /* */ comments
            if ($ch === '/' && $i + 1 < $len && $sql[$i+1] === '*') {
                $i += 2;
                while ($i < $len - 1 && !($sql[$i] === '*' && $sql[$i+1] === '/')) $i++;
                $i += 2;
                continue;
            }

            if ($ch === ';') {
                $stmt = trim($buffer);
                if ($stmt !== '') {
                    // Skip CREATE DATABASE and USE statements - DB already exists
                    $skip = false;
                    foreach ($skipPrefixes as $prefix) {
                        if (str_starts_with($stmt, $prefix)) { $skip = true; break; }
                    }
                    if (!$skip) $pdo->exec($stmt);
                }
                $buffer = '';
                continue;
            }

            $buffer .= $ch;
        }

        // Execute any remaining statement
        $stmt = trim($buffer);
        if ($stmt !== '') $pdo->exec($stmt);

        $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
        return true;
    } catch (PDOException $e) { return $e->getMessage(); }
}

function writeEnv(array $d): bool {
    $key = bin2hex(random_bytes(32));
    $tz   = $d['app_timezone'] ?? 'UTC';
    $sec  = $d['smtp_secure']  ?? 'tls';
    $env = "APP_NAME=\"{$d['app_name']}\"\nAPP_URL={$d['app_url']}\nAPP_ENV=production\nAPP_DEBUG=false\nAPP_KEY=$key\nAPP_TIMEZONE=$tz\n\nDB_HOST={$d['db_host']}\nDB_PORT=3306\nDB_NAME={$d['db_name']}\nDB_USER={$d['db_user']}\nDB_PASS={$d['db_pass']}\n\nSMTP_HOST={$d['smtp_host']}\nSMTP_PORT={$d['smtp_port']}\nSMTP_SECURE=$sec\nSMTP_USER={$d['smtp_user']}\nSMTP_PASS={$d['smtp_pass']}\nSMTP_FROM_EMAIL={$d['smtp_from']}\nSMTP_FROM_NAME=\"{$d['app_name']}\"\nMAIL_SUPPORT={$d['smtp_from']}\n\nSESSION_NAME=nexvest_sess\nSESSION_LIFETIME=7200\nSESSION_SAVE_PATH=\n\nBCRYPT_ROUNDS=12\nMAX_LOGIN_ATTEMPTS=5\nLOGIN_LOCKOUT_MINUTES=15\nPASSWORD_RESET_EXPIRE=30\nEMAIL_VERIFY_EXPIRE=15\nDEPOSIT_TIMEOUT=1800\nREFERRAL_COMMISSION_RATE=5\n";
    return (bool)file_put_contents(ROOT.'/.env', $env);
}

function setAdmin(string $h, string $n, string $u, string $p, string $email, string $pass): bool|string {
    try {
        $pdo  = new PDO("mysql:host=$h;dbname=$n;charset=utf8mb4", $u, $p);
        $hash = password_hash($pass, PASSWORD_BCRYPT, ['cost'=>12]);
        $pdo->prepare("UPDATE admins SET email=?,password=? WHERE id=1")->execute([$email,$hash]);
        return true;
    } catch (PDOException $e) { return $e->getMessage(); }
}

function setPlatformSettings(string $h, string $n, string $u, string $p, array $d): void {
    try {
        $pdo  = new PDO("mysql:host=$h;dbname=$n;charset=utf8mb4", $u, $p);
        $stmt = $pdo->prepare("UPDATE platform_settings SET setting_value=? WHERE setting_key=?");
        foreach ([
            'platform_name'     => $d['app_name'],
            'platform_website'  => $d['app_url'],
            'platform_email'    => $d['smtp_from'],
            'platform_support_email' => $d['smtp_from'],
            'platform_currency' => $d['platform_currency'] ?? 'USD',
            'platform_symbol'   => $d['platform_symbol']   ?? '$',
            'smtp_host'         => $d['smtp_host'],
            'smtp_port'         => $d['smtp_port']   ?? '587',
            'smtp_secure'       => $d['smtp_secure'] ?? 'tls',
            'smtp_user'         => $d['smtp_user'],
            'smtp_pass'         => $d['smtp_pass'],
            'smtp_from_name'    => $d['app_name'],
        ] as $k=>$v) { if($v !== '') $stmt->execute([$v,$k]); }
    } catch(PDOException $e) {}
}

session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>NexVest Installer</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;background:#F0F2F5;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem}
.card{background:#fff;border:1px solid #DDE3EC;border-radius:8px;width:100%;max-width:540px;overflow:hidden;box-shadow:0 8px 30px rgba(0,0,0,.1)}
.hdr{background:#0D1B2A;padding:1.5rem 2rem;display:flex;align-items:center;gap:12px}
.logo{width:42px;height:42px;background:#1B6CA8;border-radius:5px;display:flex;align-items:center;justify-content:center;font-weight:800;color:#fff;font-size:14px;flex-shrink:0}
.hdr h1{color:#fff;font-size:1.1rem;margin-bottom:2px}
.hdr p{color:rgba(255,255,255,.4);font-size:12px}
.steps{display:flex;background:#F7F8FA;border-bottom:1px solid #DDE3EC}
.stp{flex:1;padding:.65rem .5rem;text-align:center;font-size:10.5px;font-weight:600;color:#8A96A8;border-right:1px solid #DDE3EC}
.stp:last-child{border-right:none}
.stp.on{color:#1B6CA8;background:#EBF3FB}
.stp.done{color:#1A7A4A;background:#F0FAF4}
.body{padding:2rem}
h2{font-size:1.1rem;color:#0D1B2A;margin-bottom:.35rem}
.sub{font-size:13px;color:#4A5568;margin-bottom:1.5rem;line-height:1.6}
.fg{margin-bottom:1rem}
label{display:block;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#4A5568;margin-bottom:5px}
input,select{width:100%;padding:9px 12px;border:1px solid #DDE3EC;border-radius:5px;font-size:13.5px;font-family:inherit;outline:none;transition:border-color .15s}
input:focus,select:focus{border-color:#1B6CA8;box-shadow:0 0 0 3px rgba(27,108,168,.1)}
.row{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
.btn{display:flex;align-items:center;justify-content:center;width:100%;padding:11px;border-radius:5px;font-size:13.5px;font-weight:700;cursor:pointer;border:none;font-family:inherit;background:#1B6CA8;color:#fff;margin-top:.5rem;transition:background .15s;text-decoration:none}
.btn:hover{background:#155D94}
.btn.dark{background:#0D1B2A;margin-top:.65rem}
.err{background:#FDF2F2;border:1px solid #FCCACA;color:#C0392B;padding:10px 14px;border-radius:5px;font-size:13px;margin-bottom:1rem;line-height:1.5}
.ok{background:#F0FAF4;border:1px solid #A7F3C9;color:#1A7A4A;padding:10px 14px;border-radius:5px;font-size:13px;margin-bottom:.75rem}
.info{background:#EBF3FB;border:1px solid #BDD9F2;color:#155D94;padding:10px 14px;border-radius:5px;font-size:12.5px;margin-top:1rem;line-height:1.6}
.sec{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;color:#8A96A8;margin:1.25rem 0 .65rem;padding-bottom:.5rem;border-bottom:1px solid #DDE3EC}
.chk{display:flex;align-items:center;gap:10px;padding:.55rem 0;border-bottom:1px solid #F0F2F5;font-size:13px}
.chk .ic{font-weight:700;width:18px}
.chk .ok-ic{color:#1A7A4A}
.chk .err-ic{color:#C0392B}
.success-wrap{text-align:center;padding:1rem 0}
.success-icon{width:72px;height:72px;background:#F0FAF4;border:2px solid #A7F3C9;border-radius:50%;font-size:32px;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem}
code{display:block;background:#F7F8FA;border:1px solid #DDE3EC;border-radius:4px;padding:8px 12px;font-size:12px;margin:.5rem 0;word-break:break-all;color:#0D1B2A}
@media(max-width:480px){.row{grid-template-columns:1fr}.body{padding:1.5rem}}
</style>
</head>
<body>
<div class="card">
<div class="hdr">
  <div class="logo">NV</div>
  <div><h1>NexVest Installer</h1><p>Version 1.0 — Platform Setup Wizard</p></div>
</div>
<div class="steps">
<?php
$labels = ['Requirements','Database','Platform','Admin','Done'];
for($i=0;$i<5;$i++){
  $cls = $i<$step?'done':($i===$step?'on':'');
  echo "<div class='stp $cls'>".($i<$step?'✓ ':($i===$step?'→ ':''))."{$labels[$i]}</div>";
}
?>
</div>
<div class="body">
<?php

// ── STEP 0: Requirements ──────────────────────────────────────
if($step===0):
$phpOk    = PHP_VERSION_ID >= 80100;
$exts     = ['pdo','pdo_mysql','mbstring','json','openssl','gd','fileinfo','curl'];
$extOk    = array_filter($exts, fn($e)=>!extension_loaded($e));
$vendorOk = is_dir(ROOT.'/vendor');
$schemaOk = file_exists(ROOT.'/database/schema.sql');
$uploadsOk= is_writable(ROOT.'/uploads') || @mkdir(ROOT.'/uploads',0755,true);
$storageOk= is_writable(ROOT.'/storage/logs') || @mkdir(ROOT.'/storage/logs',0755,true);
$htaccessOk = file_exists(ROOT.'/.htaccess');
$allOk = $phpOk && empty($extOk) && $vendorOk && $schemaOk && $uploadsOk && $storageOk;
?>
<h2>Server Requirements</h2>
<p class="sub">Checking your server before we begin installation.</p>
<div class="chk"><span class="ic <?= $phpOk?'ok-ic':'err-ic' ?>"><?= $phpOk?'✓':'✗' ?></span>PHP <?= PHP_VERSION ?> <?= $phpOk?'(8.1+ required ✓)':'— PHP 8.1+ required!' ?></div>
<?php foreach($exts as $ext): $ok=extension_loaded($ext); ?>
<div class="chk"><span class="ic <?= $ok?'ok-ic':'err-ic' ?>"><?= $ok?'✓':'✗' ?></span>Extension: <?= $ext ?></div>
<?php endforeach; ?>
<div class="chk"><span class="ic <?= $vendorOk?'ok-ic':'err-ic' ?>"><?= $vendorOk?'✓':'✗' ?></span>
  <?php if($vendorOk): ?>Composer dependencies installed<?php else: ?><span>Composer not run — <strong>SSH into your folder and run:</strong> <code style="display:inline;font-size:11px">composer install --no-dev</code></span><?php endif; ?>
</div>
<div class="chk"><span class="ic <?= $schemaOk?'ok-ic':'err-ic' ?>"><?= $schemaOk?'✓':'✗' ?></span>Database schema file found</div>
<div class="chk"><span class="ic <?= $uploadsOk?'ok-ic':'err-ic' ?>"><?= $uploadsOk?'✓':'✗' ?></span>Uploads folder writable</div>
<div class="chk"><span class="ic <?= $storageOk?'ok-ic':'err-ic' ?>"><?= $storageOk?'✓':'✗' ?></span>Storage / logs folder writable</div>
<div class="chk"><span class="ic <?= $htaccessOk?'ok-ic':'err-ic' ?>"><?= $htaccessOk?'✓':'✗' ?></span>
  <?php if($htaccessOk): ?>URL rewrite file (.htaccess) present<?php else: ?><span><strong>.htaccess missing</strong> — pretty URLs won't work. Re-upload it (it may be hidden in your FTP client).</span><?php endif; ?>
</div>
<?php if($allOk): ?>
<a href="?step=1" class="btn" style="margin-top:1.25rem">✓ All good — Continue to Database →</a>
<?php else: ?>
<div class="info">Fix the issues above then <a href="?step=0" style="color:#1B6CA8;font-weight:600">refresh this page</a>.</div>
<?php endif; ?>

<?php
// ── STEP 1: Database ──────────────────────────────────────────
elseif($step===1):
if($_SERVER['REQUEST_METHOD']==='POST'){
    $h=trim($_POST['db_host']??'localhost');
    $n=trim($_POST['db_name']??'');
    $u=trim($_POST['db_user']??'');
    $p=trim($_POST['db_pass']??'');
    if(!$n||!$u){ $errors[]='Database name and username are required.'; }
    else {
        $test=testDB($h,$n,$u,$p);
        if($test!==true){ $errors[]='Connection failed: '.$test; }
        else {
            $imp=importSchema($h,$n,$u,$p);
            if($imp!==true){ $errors[]='Schema import failed: '.$imp; }
            else { $_SESSION['idb']=compact('h','n','u','p'); header('Location:?step=2'); exit; }
        }
    }
}
?>
<h2>Database Setup</h2>
<p class="sub">Create a MySQL database in cPanel first, then enter the details below. The installer will import all tables automatically.</p>
<?php foreach($errors as $e): ?><div class="err">✗ <?= htmlspecialchars($e) ?></div><?php endforeach; ?>
<form method="POST">
<div class="fg"><label>Database Host</label><input name="db_host" value="localhost" required></div>
<div class="fg"><label>Database Name</label><input name="db_name" placeholder="e.g. globadel_nexvest" required></div>
<div class="fg"><label>Database Username</label><input name="db_user" placeholder="e.g. globadel_nvuser" required></div>
<div class="fg"><label>Database Password</label><input type="password" name="db_pass" placeholder="Your database password"></div>
<button type="submit" class="btn">Test & Import Database →</button>
</form>

<?php
// ── STEP 2: Platform ──────────────────────────────────────────
elseif($step===2):
if($_SERVER['REQUEST_METHOD']==='POST'){
    $tz = trim($_POST['app_timezone']??'UTC');
    if (!in_array($tz, timezone_identifiers_list(), true)) $tz = 'UTC';
    $sec = trim($_POST['smtp_secure']??'tls');
    if (!in_array($sec, ['tls','ssl',''], true)) $sec = 'tls';
    $_SESSION['ipf']=[
        'app_name'        => trim($_POST['app_name']??'NexVest'),
        'app_url'         => rtrim(trim($_POST['app_url']??''),'/').'',
        'app_timezone'    => $tz,
        'platform_currency' => trim($_POST['platform_currency']??'USD'),
        'platform_symbol'   => trim($_POST['platform_symbol']??'$'),
        'smtp_host'       => trim($_POST['smtp_host']??''),
        'smtp_port'       => trim($_POST['smtp_port']??'587'),
        'smtp_secure'     => $sec,
        'smtp_user'       => trim($_POST['smtp_user']??''),
        'smtp_pass'       => trim($_POST['smtp_pass']??''),
        'smtp_from'       => trim($_POST['smtp_from']??''),
    ];
    header('Location:?step=3'); exit;
}
$url=(isset($_SERVER['HTTPS'])?'https':'http').'://'.($_SERVER['HTTP_HOST']??'yourdomain.com');
?>
<h2>Platform Settings</h2>
<p class="sub">Enter your platform name and email settings. You can change all of this later in Admin → Settings.</p>
<form method="POST">
<div class="sec">Platform</div>
<div class="fg"><label>Platform Name</label><input name="app_name" value="NexVest Capital Group" required></div>
<div class="fg"><label>Platform URL</label><input name="app_url" value="<?= htmlspecialchars($url) ?>" required></div>
<div class="row">
  <div class="fg"><label>Currency Code</label><input name="platform_currency" value="USD" placeholder="USD" required></div>
  <div class="fg"><label>Currency Symbol</label><input name="platform_symbol" value="$" placeholder="$" required></div>
</div>
<div class="fg"><label>Timezone <span style="font-weight:400;text-transform:none;letter-spacing:0;font-size:11px">(used for payouts &amp; timestamps)</span></label>
  <select name="app_timezone">
    <?php $tzCur = 'UTC'; foreach (timezone_identifiers_list() as $tz): ?>
      <option value="<?= htmlspecialchars($tz) ?>" <?= $tz===$tzCur?'selected':'' ?>><?= htmlspecialchars($tz) ?></option>
    <?php endforeach; ?>
  </select>
</div>
<div class="sec">Email / SMTP <span style="font-weight:400;text-transform:none;letter-spacing:0;font-size:11px">(SendGrid / Mailgun / Brevo — all have free plans)</span></div>
<div class="row">
  <div class="fg"><label>SMTP Host</label><input name="smtp_host" placeholder="smtp.sendgrid.net"></div>
  <div class="fg"><label>Port</label><input name="smtp_port" value="587"></div>
</div>
<div class="row">
  <div class="fg"><label>Encryption</label>
    <select name="smtp_secure">
      <option value="tls" selected>TLS (STARTTLS) — port 587</option>
      <option value="ssl">SSL — port 465</option>
      <option value="">None</option>
    </select>
  </div>
  <div class="fg"><label>SMTP Username</label><input name="smtp_user" placeholder="apikey"></div>
</div>
<div class="fg"><label>SMTP Password / API Key</label><input type="password" name="smtp_pass"></div>
<div class="fg"><label>From Email Address</label><input type="email" name="smtp_from" placeholder="noreply@yourdomain.com"></div>
<div class="info">💡 You can skip SMTP now and set it up later in Admin → Settings → Email.</div>
<button type="submit" class="btn">Save & Continue →</button>
</form>

<?php
// ── STEP 3: Admin ─────────────────────────────────────────────
elseif($step===3):
if($_SERVER['REQUEST_METHOD']==='POST'){
    $db  = $_SESSION['idb']??[];
    $pf  = $_SESSION['ipf']??[];
    $ae  = trim($_POST['admin_email']??'');
    $ap  = trim($_POST['admin_pass']??'');
    $ap2 = trim($_POST['admin_pass2']??'');
    if(!filter_var($ae,FILTER_VALIDATE_EMAIL)) $errors[]='Valid admin email required.';
    if(strlen($ap)<8)                          $errors[]='Password must be at least 8 characters.';
    if($ap!==$ap2)                             $errors[]='Passwords do not match.';
    if(empty($errors)){
        $data = array_merge($pf, ['db_host'=>$db['h'],'db_name'=>$db['n'],'db_user'=>$db['u'],'db_pass'=>$db['p']]);
        if(!writeEnv($data))         { $errors[]='Could not write .env — check folder is writable.'; }
        else {
            $r = setAdmin($db['h'],$db['n'],$db['u'],$db['p'],$ae,$ap);
            if($r!==true)            { $errors[]='Could not update admin: '.$r; }
            else {
                setPlatformSettings($db['h'],$db['n'],$db['u'],$db['p'],$data);
                $_SESSION['iadmin_email']=$ae;
                header('Location:?step=4'); exit;
            }
        }
    }
}
?>
<h2>Admin Account</h2>
<p class="sub">Set your admin login credentials. This replaces the default account.</p>
<?php foreach($errors as $e): ?><div class="err">✗ <?= htmlspecialchars($e) ?></div><?php endforeach; ?>
<form method="POST">
<div class="fg"><label>Admin Email Address</label><input type="email" name="admin_email" placeholder="admin@yourdomain.com" required></div>
<div class="fg"><label>Admin Password</label><input type="password" name="admin_pass" placeholder="Min. 8 characters" required></div>
<div class="fg"><label>Confirm Password</label><input type="password" name="admin_pass2" placeholder="Repeat password" required></div>
<button type="submit" class="btn">Complete Installation →</button>
</form>

<?php
// ── STEP 4: Done ──────────────────────────────────────────────
elseif($step===4):
@unlink(__FILE__); // Self-delete for security
$adminEmail = $_SESSION['iadmin_email']??'your admin email';
session_destroy();
?>
<div class="success-wrap">
  <div class="success-icon">✓</div>
  <h2 style="font-size:1.35rem;margin-bottom:.4rem">Installation Complete!</h2>
  <p class="sub">NexVest is ready. The installer has deleted itself for security.</p>
  <a href="/" class="btn">→ Open Investor Portal</a>
  <a href="/admin/login" class="btn dark">→ Open Admin Panel</a>
  <div class="info" style="text-align:left;margin-top:1.25rem">
    <strong>Admin login:</strong> <?= htmlspecialchars($adminEmail) ?><br><br>
    <strong>Set up cron job</strong> for automatic daily ROI payouts.<br>
    In cPanel → Cron Jobs → add:<br>
    <code>0 6 * * * php <?= ROOT ?>/cron/payout.php</code>
    <br><br>
    <strong>Next steps:</strong><br>
    1. Admin → Settings → Upload logo, set address &amp; phone<br>
    2. Admin → Investments → Create your first investment product<br>
    3. Test investor registration on the investor portal
  </div>
</div>
<?php endif; ?>
</div>
</div>
</body>
</html>
