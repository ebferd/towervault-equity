<?php
/**
 * NexVest — Reminder / lifecycle emails
 * Run daily (hourly is fine too — everything is de-duplicated):
 *   0 7 * * * php /home/USER/DOCROOT/cron/reminders.php
 *
 * A user receives at most ONE reminder per run, chosen by priority
 * (verify > kyc > abandoned deposit > finish setup > monthly summary >
 * dormant > referral), so nobody gets two nudges at once.
 */
declare(strict_types=1);

define('ROOT', dirname(__DIR__));
define('CRON', true);

require_once ROOT . '/config/config.php';
require_once ROOT . '/config/database.php';
require_once ROOT . '/app/helpers/helpers.php';
require_once ROOT . '/app/mail/Mailer.php';
if (file_exists(ROOT . '/vendor/autoload.php')) require_once ROOT . '/vendor/autoload.php';

$log = fn(string $m) => print('[' . date('Y-m-d H:i:s') . '] ' . $m . PHP_EOL);
$log('=== Reminder Cron Started ===');

// Reminders are email-only — skip entirely if SMTP isn't set up yet.
$smtpHost = platform_setting('smtp_host', env('SMTP_HOST', ''));
$smtpUser = platform_setting('smtp_user', env('SMTP_USER', ''));
$smtpPass = platform_setting('smtp_pass', env('SMTP_PASS', ''));
if ($smtpHost === '' || $smtpUser === '' || $smtpPass === '') {
    $log('SMTP not configured — skipping reminders.');
    $log('=== Reminder Cron Completed ===');
    return;
}

$globalRate = (float) platform_setting('referral_commission', '5');
$counts   = ['verify'=>0,'kyc'=>0,'abandoned_deposit'=>0,'setup'=>0,'monthly'=>0,'dormant'=>0,'referral'=>0];
$emailed  = []; // user_id => true — at most one reminder per user per run

// Helpers -----------------------------------------------------------------
$wasSent = function (int $uid, string $type, ?string $ref = null): bool {
    $row = $ref === null
        ? DB::fetch("SELECT id FROM email_reminders WHERE user_id=? AND type=? LIMIT 1", [$uid, $type])
        : DB::fetch("SELECT id FROM email_reminders WHERE user_id=? AND type=? AND ref=? LIMIT 1", [$uid, $type, $ref]);
    return (bool) $row;
};
$sentAfter  = fn(int $uid, string $type, string $ts): bool =>
    (bool) DB::fetch("SELECT id FROM email_reminders WHERE user_id=? AND type=? AND sent_at > ? LIMIT 1", [$uid, $type, $ts]);
$sentWithin = fn(int $uid, string $type, int $days): bool =>
    (bool) DB::fetch("SELECT id FROM email_reminders WHERE user_id=? AND type=? AND sent_at > (NOW() - INTERVAL {$days} DAY) LIMIT 1", [$uid, $type]);
$mark = function (int $uid, string $type, ?string $ref = null) use (&$emailed): void {
    DB::query("INSERT INTO email_reminders (user_id, type, ref) VALUES (?,?,?)", [$uid, $type, $ref]);
    $emailed[$uid] = true;
};

// 1) EMAIL NOT VERIFIED — registered >24h, still unverified ---------------
foreach (DB::fetchAll("SELECT id,first_name,email FROM users
        WHERE email_verified=0 AND created_at < (NOW() - INTERVAL 1 DAY) LIMIT 500") as $u) {
    $uid = (int) $u['id'];
    if (isset($emailed[$uid]) || $wasSent($uid, 'verify')) continue;
    $otp = generate_otp(); $token = generate_token();
    $exp = gmdate('Y-m-d H:i:s', time() + (CONFIG['security']['verify_expire_minutes'] ?? 15) * 60);
    DB::execute("UPDATE email_verifications SET used=1 WHERE user_id=?", [$uid]);
    DB::query("INSERT INTO email_verifications (user_id, token, otp, expires_at) VALUES (?,?,?,?)", [$uid, $token, $otp, $exp]);
    if (Mailer::sendVerifyReminder($u, $otp)) { $mark($uid, 'verify'); $counts['verify']++; }
}

// 2) KYC NEEDS ATTENTION — rejected --------------------------------------
foreach (DB::fetchAll("SELECT id,first_name,email FROM users WHERE kyc_status='rejected' LIMIT 500") as $u) {
    $uid = (int) $u['id'];
    if (isset($emailed[$uid])) continue;
    $sub = DB::fetch("SELECT id, rejection_reason FROM kyc_submissions WHERE user_id=? ORDER BY updated_at DESC, id DESC LIMIT 1", [$uid]);
    $ref = $sub ? (string) $sub['id'] : 'none';
    if ($wasSent($uid, 'kyc', $ref)) continue;
    if (Mailer::sendKycAttentionReminder($u, (string) ($sub['rejection_reason'] ?? ''))) { $mark($uid, 'kyc', $ref); $counts['kyc']++; }
}

// 3) ABANDONED DEPOSIT — started, never completed ------------------------
foreach (DB::fetchAll("SELECT di.reference, di.amount, di.method, u.id AS uid, u.first_name, u.email
        FROM deposit_invoices di JOIN users u ON u.id=di.user_id
        WHERE di.status IN ('pending','expired')
          AND di.created_at BETWEEN (NOW() - INTERVAL 3 DAY) AND (NOW() - INTERVAL 2 HOUR) LIMIT 500") as $d) {
    $uid = (int) $d['uid'];
    if (isset($emailed[$uid]) || $wasSent($uid, 'abandoned_deposit', $d['reference'])) continue;
    if (DB::fetch("SELECT id FROM deposit_invoices WHERE user_id=? AND status='paid' AND created_at >= (NOW() - INTERVAL 3 DAY) LIMIT 1", [$uid])) continue;
    if (Mailer::sendAbandonedDepositReminder(['first_name'=>$d['first_name'],'email'=>$d['email']], $d)) { $mark($uid, 'abandoned_deposit', $d['reference']); $counts['abandoned_deposit']++; }
}

// 4) FINISH SETTING UP — verified, 3+ days old, no investment yet ---------
foreach (DB::fetchAll("SELECT u.id,first_name,email,kyc_status,wallet_balance FROM users u
        WHERE u.email_verified=1 AND u.status='active' AND u.created_at < (NOW() - INTERVAL 3 DAY)
          AND NOT EXISTS (SELECT 1 FROM investment_holdings ih WHERE ih.user_id=u.id) LIMIT 500") as $u) {
    $uid = (int) $u['id'];
    if (isset($emailed[$uid]) || $wasSent($uid, 'setup')) continue;
    $kycOn = platform_setting('kyc_enabled', '1') === '1';
    $items = [['Account created &amp; email verified', true]];
    if ($kycOn) $items[] = ['Verify your identity (KYC)', ($u['kyc_status'] ?? '') === 'verified'];
    $items[] = ['Add funds to your wallet', (float) $u['wallet_balance'] > 0];
    $items[] = ['Make your first investment', false];
    if (Mailer::sendFinishSetupReminder($u, $items)) { $mark($uid, 'setup'); $counts['setup']++; }
}

// 5) MONTHLY EARNINGS SUMMARY — first few days of the month ---------------
if ((int) date('j') <= 3) {
    $mFrom = date('Y-m-01 00:00:00', strtotime('first day of last month'));
    $mTo   = date('Y-m-t 23:59:59', strtotime('last day of last month'));
    $monthName = date('F', strtotime('first day of last month'));
    $mRef  = date('Y-m', strtotime('first day of last month'));
    foreach (DB::fetchAll("SELECT id,first_name,email,wallet_balance FROM users WHERE status='active' AND email_verified=1 LIMIT 2000") as $u) {
        $uid = (int) $u['id'];
        if (isset($emailed[$uid]) || $wasSent($uid, 'monthly', $mRef)) continue;
        $returns   = (float) (DB::fetch("SELECT COALESCE(SUM(amount),0) s FROM transactions WHERE user_id=? AND type='return' AND status='completed' AND created_at BETWEEN ? AND ?", [$uid, $mFrom, $mTo])['s'] ?? 0);
        $commission= (float) (DB::fetch("SELECT COALESCE(SUM(amount),0) s FROM transactions WHERE user_id=? AND type='referral_commission' AND status='completed' AND created_at BETWEEN ? AND ?", [$uid, $mFrom, $mTo])['s'] ?? 0);
        $positions = (int) (DB::fetch("SELECT COUNT(*) c FROM investment_holdings WHERE user_id=? AND status='active'", [$uid])['c'] ?? 0);
        $invested  = (float) (DB::fetch("SELECT COALESCE(SUM(amount),0) s FROM investment_holdings WHERE user_id=? AND status='active'", [$uid])['s'] ?? 0);
        if ($returns <= 0 && $positions <= 0 && $commission <= 0) continue;
        if (Mailer::sendMonthlyEarningsSummary($u, $monthName, $returns, $invested, $positions, $commission, (float) $u['wallet_balance'])) { $mark($uid, 'monthly', $mRef); $counts['monthly']++; }
    }
}

// 6) DORMANT — no sign-in for 30 days ------------------------------------
foreach (DB::fetchAll("SELECT id,first_name,email,wallet_balance,last_login_at FROM users
        WHERE status='active' AND email_verified=1 AND last_login_at IS NOT NULL
          AND last_login_at < (NOW() - INTERVAL 30 DAY) LIMIT 500") as $u) {
    $uid = (int) $u['id'];
    if (isset($emailed[$uid]) || $sentAfter($uid, 'dormant', $u['last_login_at'])) continue;
    $active  = (int) (DB::fetch("SELECT COUNT(*) c FROM investment_holdings WHERE user_id=? AND status='active'", [$uid])['c'] ?? 0);
    $returns = (float) (DB::fetch("SELECT COALESCE(SUM(amount),0) s FROM transactions WHERE user_id=? AND type='return' AND status='completed'", [$uid])['s'] ?? 0);
    if (Mailer::sendDormantReminder($u, (float) $u['wallet_balance'], $active, $returns)) { $mark($uid, 'dormant'); $counts['dormant']++; }
}

// 7) REFERRAL NUDGE — active investors, at most every 45 days -------------
foreach (DB::fetchAll("SELECT id,first_name,email,referral_code,is_agent,agent_commission FROM users u
        WHERE status='active' AND email_verified=1
          AND EXISTS (SELECT 1 FROM investment_holdings ih WHERE ih.user_id=u.id) LIMIT 500") as $u) {
    $uid = (int) $u['id'];
    if (isset($emailed[$uid]) || $sentWithin($uid, 'referral', 45)) continue;
    $earned = (float) (DB::fetch("SELECT COALESCE(SUM(commission_amount),0) s FROM referrals WHERE referrer_id=?", [$uid])['s'] ?? 0);
    $rate   = ((int) ($u['is_agent'] ?? 0) === 1 && $u['agent_commission'] !== null) ? (float) $u['agent_commission'] : $globalRate;
    if (Mailer::sendReferralNudge($u, $earned, (string) $u['referral_code'], $rate)) { $mark($uid, 'referral'); $counts['referral']++; }
}

foreach ($counts as $t => $n) $log(sprintf('%-18s sent %d', $t, $n));
$log('=== Reminder Cron Completed ===');
