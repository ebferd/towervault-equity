<?php
/**
 * NexVest — Daily ROI Payout Cron
 * Run daily: php /path/to/nexvest/cron/payout.php
 * cPanel cron: 0 6 * * * php /home/username/public_html/cron/payout.php
 */
declare(strict_types=1);

define('ROOT', dirname(__DIR__));
define('CRON', true);

require_once ROOT . '/config/config.php';
require_once ROOT . '/config/database.php';
require_once ROOT . '/app/helpers/helpers.php';
require_once ROOT . '/app/mail/Mailer.php';

if (file_exists(ROOT . '/vendor/autoload.php')) {
    require_once ROOT . '/vendor/autoload.php';
}

$log = function(string $msg) { echo '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL; };

$log('=== NexVest Payout Cron Started ===');

// Process due payouts
$due = DB::fetchAll(
    "SELECT ps.*, u.email, u.first_name, u.last_name, u.wallet_balance,
            i.name AS investment_name, i.roi, i.payout_frequency,
            ih.auto_reinvest
     FROM payout_schedules ps
     JOIN users u ON u.id = ps.user_id
     JOIN investment_holdings ih ON ih.id = ps.holding_id
     JOIN investments i ON i.id = ih.investment_id
     WHERE ps.status = 'scheduled' AND ps.due_date <= CURDATE()
     ORDER BY ps.due_date ASC LIMIT 500"
);

$log('Found ' . count($due) . ' payout(s) due.');

foreach ($due as $payout) {
    DB::beginTransaction();
    try {
        $uid       = (int) $payout['user_id'];
        $amount    = (float) $payout['amount'];
        $reinvest  = (bool) ($payout['auto_reinvest'] ?? false);

        if ($reinvest) {
            // Auto-reinvest: add return to holding principal, no wallet credit
            DB::execute("UPDATE investment_holdings SET amount = amount + ?, total_earned = total_earned + ?, last_payout_at=NOW() WHERE id=?",
                [$amount, $amount, $payout['holding_id']]);
            $ref = generate_reference('RNV');
            $txId = (int) DB::insert(
                "INSERT INTO transactions (user_id, type, amount, balance_before, balance_after, status, reference, description, holding_id)
                 VALUES (?,?,?,?,?,'completed',?,?,?)",
                [$uid, 'return', $amount, 0, 0, $ref, 'Auto-reinvested — ' . $payout['investment_name'], $payout['holding_id']]
            );
            DB::execute("UPDATE payout_schedules SET status='paid', paid_at=NOW(), tx_id=? WHERE id=?", [$txId, $payout['id']]);
            create_notification($uid, 'return', 'Return Auto-Reinvested',
                fmt_currency($amount) . ' return from ' . $payout['investment_name'] . ' has been reinvested into your position.');
            DB::commit();
            $log("✓ Auto-reinvested " . fmt_currency($amount) . " for user #{$uid}");
        } else {
            $balBefore = (float) $payout['wallet_balance'];
            $balAfter  = $balBefore + $amount;
            DB::execute("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?", [$amount, $uid]);
            $ref = generate_reference('RTN');
            $txId = (int) DB::insert(
                "INSERT INTO transactions (user_id, type, amount, balance_before, balance_after, status, reference, description, holding_id)
                 VALUES (?,?,?,?,?,'completed',?,?,?)",
                [$uid, 'return', $amount, $balBefore, $balAfter, $ref, 'ROI payout — ' . $payout['investment_name'], $payout['holding_id']]
            );
            DB::execute("UPDATE payout_schedules SET status='paid', paid_at=NOW(), tx_id=? WHERE id=?", [$txId, $payout['id']]);
            DB::execute("UPDATE investment_holdings SET total_earned = total_earned + ?, last_payout_at=NOW() WHERE id=?", [$amount, $payout['holding_id']]);
            create_notification($uid, 'return', 'Return Credited',
                fmt_currency($amount) . ' return from ' . $payout['investment_name'] . ' has been credited.');
            DB::commit();
            Mailer::sendReturnCredited(['first_name' => $payout['first_name'], 'email' => $payout['email']], $amount, $payout['investment_name']);
            $log("✓ Paid " . fmt_currency($amount) . " to user #{$uid}");
        }

    } catch (Exception $e) {
        DB::rollback();
        DB::execute("UPDATE payout_schedules SET status='failed' WHERE id=?", [$payout['id']]);
        $log("✗ Failed payout #{$payout['id']}: " . $e->getMessage());
    }
}

// Expire old invoices
$expired = DB::execute("UPDATE deposit_invoices SET status='expired' WHERE status='pending' AND expires_at < NOW()");
$log("Expired {$expired} invoice(s).");

// Mature completed holdings and return principal
$maturingHoldings = DB::fetchAll(
    "SELECT ih.*, u.wallet_balance, u.email, u.first_name, u.last_name, i.name AS investment_name
     FROM investment_holdings ih
     JOIN users u ON u.id = ih.user_id
     JOIN investments i ON i.id = ih.investment_id
     WHERE ih.status='active' AND ih.end_date < CURDATE()"
);
$maturedCount = 0;
foreach ($maturingHoldings as $mh) {
    DB::beginTransaction();
    try {
        DB::execute("UPDATE investment_holdings SET status='matured' WHERE id=?", [$mh['id']]);
        // Return the principal to the investor's wallet
        $principal = (float)$mh['amount'];
        DB::execute("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id=?", [$principal, $mh['user_id']]);
        $updated  = DB::fetch("SELECT wallet_balance FROM users WHERE id=?", [$mh['user_id']]);
        $balAfter = (float)($updated['wallet_balance'] ?? 0);
        $balBefore = $balAfter - $principal;
        $ref = generate_reference('MAT');
        DB::query(
            "INSERT INTO transactions (user_id, type, amount, balance_before, balance_after, status, reference, description, holding_id)
             VALUES (?,?,?,?,?,'completed',?,?,?)",
            [$mh['user_id'], 'return', $principal, $balBefore, $balAfter, $ref,
             'Principal returned — ' . $mh['investment_name'], $mh['id']]
        );
        create_notification($mh['user_id'], 'investment', 'Investment Matured',
            'Your investment in ' . $mh['investment_name'] . ' has matured. Principal of ' . fmt_currency($principal) . ' returned to your wallet.');
        DB::commit();
        try { Mailer::sendReturnCredited(['first_name' => $mh['first_name'], 'email' => $mh['email']], $principal, 'Principal return — ' . $mh['investment_name']); } catch (Exception $e) {}
        $maturedCount++;
        $log("✓ Matured holding #{$mh['id']} — returned " . fmt_currency($principal) . " to user #{$mh['user_id']}");
    } catch (Exception $e) {
        DB::rollback();
        $log("✗ Failed to mature holding #{$mh['id']}: " . $e->getMessage());
    }
}
$log("Matured {$maturedCount} holding(s).");

// Schedule next payouts
$holdings = DB::fetchAll(
    "SELECT ih.*, i.roi, i.payout_frequency, i.duration_value, i.duration_unit
     FROM investment_holdings ih
     JOIN investments i ON i.id = ih.investment_id
     WHERE ih.status = 'active'
     AND ih.id NOT IN (SELECT holding_id FROM payout_schedules WHERE status='scheduled' AND due_date > CURDATE())
     LIMIT 200"
);

foreach ($holdings as $h) {
    // ROI is the total return over the whole duration; distribute it across periods.
    $amount = calc_period_return(
        (float)$h['amount'], (float)$h['roi'], $h['payout_frequency'],
        (int)$h['duration_value'], $h['duration_unit']
    );
    $nextDate = match($h['payout_frequency']) {
        'daily'       => date('Y-m-d', strtotime('+1 day')),
        'weekly'      => date('Y-m-d', strtotime('+1 week')),
        'quarterly'   => date('Y-m-d', strtotime('+3 months')),
        'semi_annual' => date('Y-m-d', strtotime('+6 months')),
        'at_maturity' => $h['end_date'],
        default       => date('Y-m-d', strtotime('+1 month')),
    };
    if ($h['end_date'] >= $nextDate) {
        $existing = DB::fetch("SELECT id FROM payout_schedules WHERE holding_id=? AND due_date=?", [$h['id'], $nextDate]);
        if (!$existing) {
            DB::query("INSERT INTO payout_schedules (holding_id, user_id, amount, due_date) VALUES (?,?,?,?)",
                [$h['id'], $h['user_id'], round($amount, 2), $nextDate]);
        }
    }
}

$log('=== Cron Completed ===');
