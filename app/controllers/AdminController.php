<?php
// ============================================================
//  NexVest — Admin Controller
//  app/controllers/AdminController.php
// ============================================================

declare(strict_types=1);

class AdminController {

    // ── Login ─────────────────────────────────────────────────
    public static function showLogin(): void {
        AuthMiddleware::init();
        if (is_admin()) redirect('/admin/dashboard');
        view('admin.login', ['title' => 'Admin Sign In'], 'admin_auth');
    }

    public static function login(): void {
        AuthMiddleware::init();
        AuthMiddleware::verifyCsrf();
        AuthMiddleware::rateLimit('admin_login', 5, 300);

        $email    = sanitize_email($_POST['email']    ?? '');
        $password = $_POST['password'] ?? '';

        if (!$email || !$password) json_response(['success' => false, 'error' => 'Email and password are required.']);

        $admin = DB::fetch("SELECT * FROM admins WHERE email=? AND is_active=1", [$email]);
        if (!$admin || !verify_password($password, $admin['password'])) {
            json_response(['success' => false, 'error' => 'Invalid email or password.']);
        }

        $_SESSION['admin_id']    = $admin['id'];
        $_SESSION['admin_name']  = $admin['name'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_role']  = $admin['role'];

        try {
            DB::execute("UPDATE admins SET last_login_at=NOW(), last_login_ip=? WHERE id=?", [get_ip(), $admin['id']]);
            audit_log((int)$admin['id'], 'admin_login', 'Admin logged in', 'low');
        } catch (\Throwable $e) {}

        json_response(['success' => true, 'redirect' => '/admin/dashboard']);
    }

    public static function logout(): void {
        AuthMiddleware::init();
        if (is_admin()) {
            audit_log(current_admin_id(), 'admin_logout', 'Admin logged out', 'low');
        }
        AuthMiddleware::adminLogout();
        $dest = platform_setting('logout_redirect_url', '');
        redirect($dest ?: '/admin/login');
    }

    // ── Dashboard ─────────────────────────────────────────────
    public static function dashboard(): void {
        AuthMiddleware::admin();
        $stats = DB::fetch("SELECT * FROM v_platform_stats") ?: [];
        $recent_users = DB::fetchAll("SELECT id, first_name, last_name, email, country, kyc_status, wallet_balance, created_at FROM users ORDER BY created_at DESC LIMIT 5");
        $pending_wr   = DB::fetchAll("SELECT wr.*, CONCAT(u.first_name,' ',u.last_name) AS user_name FROM withdrawal_requests wr JOIN users u ON u.id=wr.user_id WHERE wr.status='pending' ORDER BY wr.created_at DESC LIMIT 5");
        $investments  = DB::fetchAll("SELECT i.*, (SELECT COUNT(*) FROM investment_holdings ih WHERE ih.investment_id=i.id AND ih.status='active') AS investor_count FROM investments i ORDER BY i.created_at DESC LIMIT 10");
        view('admin.dashboard', compact('stats','recent_users','pending_wr','investments'), 'admin');
    }

    // ── Users ─────────────────────────────────────────────────
    public static function users(): void {
        AuthMiddleware::admin();
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $search = sanitize($_GET['q']      ?? '');
        $filter = sanitize($_GET['filter'] ?? 'all');

        $where  = "1=1";
        $params = [];
        if ($search) {
            $where .= " AND (email LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR country LIKE ?)";
            $like = "%{$search}%";
            $params = array_merge($params, [$like,$like,$like,$like]);
        }
        if ($filter === 'suspended')  { $where .= " AND status='suspended'";  }
        if ($filter === 'kyc_pending'){ $where .= " AND kyc_status='pending'";}
        if ($filter === 'unverified') { $where .= " AND email_verified=0";    }

        $result = paginate(
            "SELECT COUNT(*) AS total FROM users WHERE {$where}",
            "SELECT * FROM users WHERE {$where} ORDER BY created_at DESC",
            $params, $page, 20
        );
        view('admin.users', array_merge(compact('search','filter'), $result), 'admin');
    }

    public static function userDetail(): void {
        AuthMiddleware::admin();
        $id   = (int) ($_GET['id'] ?? 0);
        $user = DB::fetch("SELECT * FROM users WHERE id=?", [$id]);
        if (!$user) { http_response_code(404); view('errors.404', ['title'=>'Not Found'], 'minimal'); return; }

        $holdings    = DB::fetchAll("SELECT ih.*, i.name FROM investment_holdings ih JOIN investments i ON i.id=ih.investment_id WHERE ih.user_id=? ORDER BY ih.created_at DESC", [$id]);
        $transactions= DB::fetchAll("SELECT * FROM transactions WHERE user_id=? ORDER BY created_at DESC LIMIT 20", [$id]);
        $sessions    = DB::fetchAll("SELECT * FROM user_sessions WHERE user_id=? ORDER BY last_active DESC", [$id]);
        $kyc         = DB::fetch("SELECT * FROM kyc_submissions WHERE user_id=? ORDER BY id DESC LIMIT 1", [$id]);
        $tickets     = DB::fetchAll("SELECT * FROM support_tickets WHERE user_id=? ORDER BY created_at DESC LIMIT 5", [$id]);
        // Referral & commission data
        $referralAsReferred = DB::fetch(
            "SELECT r.*, u.first_name AS ref_first, u.last_name AS ref_last, u.email AS ref_email
             FROM referrals r JOIN users u ON u.id=r.referrer_id WHERE r.referred_id=?", [$id]
        );
        $referralAsReferrer = DB::fetchAll(
            "SELECT r.*, u.first_name AS inv_first, u.last_name AS inv_last, u.email AS inv_email
             FROM referrals r JOIN users u ON u.id=r.referred_id WHERE r.referrer_id=? ORDER BY r.created_at DESC", [$id]
        );
        $commissionTx = DB::fetchAll(
            "SELECT * FROM transactions WHERE user_id=? AND type='referral_commission' ORDER BY created_at DESC", [$id]
        );

        $investPlans = DB::fetchAll("SELECT id, name, roi, duration_value, duration_unit, payout_frequency FROM investments ORDER BY name");
        view('admin.user_detail', compact('user','holdings','transactions','sessions','kyc','tickets','referralAsReferred','referralAsReferrer','commissionTx','investPlans'), 'admin');
    }

    public static function updateUser(): void {
        AuthMiddleware::admin();
        AuthMiddleware::verifyCsrf();
        $adminId = current_admin_id();
        $id      = (int) ($_GET['id'] ?? 0);
        $user    = DB::fetch("SELECT * FROM users WHERE id=?", [$id]);
        if (!$user) json_response(['success' => false, 'error' => 'User not found.']);

        $status     = sanitize((string)($_POST['status']     ?? $user['status']     ?? ''));
        $kycStatus  = sanitize((string)($_POST['kyc_status'] ?? $user['kyc_status'] ?? ''));
        $firstName  = sanitize((string)($_POST['first_name'] ?? $user['first_name'] ?? ''));
        $lastName   = sanitize((string)($_POST['last_name']  ?? $user['last_name']  ?? ''));
        $phone      = sanitize((string)($_POST['phone']      ?? $user['phone']      ?? ''));
        $country    = sanitize((string)($_POST['country']    ?? $user['country']    ?? ''));
        $newEmail   = sanitize_email($_POST['email'] ?? '') ?: $user['email'];

        // Check email uniqueness if changed
        if ($newEmail !== $user['email']) {
            $exists = DB::fetch("SELECT id FROM users WHERE email=? AND id!=?", [$newEmail, $id]);
            if ($exists) json_response(['success' => false, 'error' => 'That email address is already in use.']);
        }

        // Per-user account restrictions
        $withdrawDisabled = isset($_POST['withdrawals_disabled']) && $_POST['withdrawals_disabled'] === '1' ? 1 : 0;
        $minOverrideRaw   = trim($_POST['min_investment_override'] ?? '');
        $minOverride      = ($minOverrideRaw !== '' && (float)$minOverrideRaw > 0) ? (float)$minOverrideRaw : null;
        $minNote          = $minOverride !== null ? sanitize($_POST['min_investment_note'] ?? '') : null;

        // Joined date (member since) — admin-editable. Accept datetime-local or date.
        $joinedRaw = trim($_POST['joined_date'] ?? '');
        $joinedAt  = $user['created_at'];
        if ($joinedRaw !== '') {
            $ts = strtotime(str_replace('T', ' ', $joinedRaw));
            if ($ts !== false) $joinedAt = date('Y-m-d H:i:s', $ts);
        }

        DB::execute(
            "UPDATE users SET first_name=?, last_name=?, email=?, phone=?, country=?, status=?, kyc_status=?,
                withdrawals_disabled=?, min_investment_override=?, min_investment_note=?, created_at=?, updated_at=NOW() WHERE id=?",
            [$firstName, $lastName, $newEmail, $phone, $country, $status, $kycStatus,
             $withdrawDisabled, $minOverride, $minNote, $joinedAt, $id]
        );
        audit_log($adminId, 'user_updated', "Updated details for user #{$id}", 'medium', 'user', $id, $user['email'],
            ['status'=>$user['status'],'kyc_status'=>$user['kyc_status'],'email'=>$user['email']],
            ['status'=>$status,'kyc_status'=>$kycStatus,'email'=>$newEmail]
        );
        json_response(['success' => true, 'message' => 'Investor details updated.']);
    }

    public static function creditWallet(): void {
        AuthMiddleware::admin();
        AuthMiddleware::verifyCsrf();
        $adminId = current_admin_id();
        $id      = (int) ($_GET['id'] ?? 0);
        $type    = sanitize($_POST['type']   ?? 'credit');
        $amount  = (float) ($_POST['amount'] ?? 0);
        $note    = sanitize($_POST['note']   ?? '');

        if ($amount <= 0) json_response(['success' => false, 'error' => 'Invalid amount.']);
        if (!$note)       json_response(['success' => false, 'error' => 'Internal note is required.']);

        $user = DB::fetch("SELECT * FROM users WHERE id=?", [$id]);
        if (!$user) json_response(['success' => false, 'error' => 'User not found.']);

        DB::beginTransaction();
        try {
            $balBefore = (float) $user['wallet_balance'];
            $balAfter  = $type === 'credit' ? $balBefore + $amount : max(0, $balBefore - $amount);
            $actual    = abs($balAfter - $balBefore);
            $txType    = $type === 'credit' ? 'adjustment' : 'debit';

            DB::execute("UPDATE users SET wallet_balance=? WHERE id=?", [$balAfter, $id]);
            $ref = generate_reference('ADJ');
            DB::query(
                "INSERT INTO transactions (user_id,type,amount,balance_before,balance_after,status,reference,description,admin_note,processed_by,processed_at)
                 VALUES (?,?,?,?,?,'completed',?,?,?,?,NOW())",
                [$id, $txType, $actual, $balBefore, $balAfter, $ref, "Manual " . ucfirst($type) . " by admin", $note, $adminId]
            );
            DB::commit();

            $severity = 'high';
            audit_log($adminId, 'wallet_' . $type, "Manual {$type} of " . fmt_currency($actual) . " on user #{$id}. Note: {$note}", $severity, 'user', $id, $user['email']);

            if ($type === 'credit') {
                create_notification($id, 'adjustment', 'Wallet Credited', fmt_currency($actual) . ' has been credited to your wallet.');
            }

            json_response(['success' => true, 'new_balance' => $balAfter]);
        } catch (Exception $e) {
            DB::rollback();
            json_response(['success' => false, 'error' => 'Failed to adjust wallet.']);
        }
    }

    public static function suspendUser(): void {
        AuthMiddleware::admin();
        AuthMiddleware::verifyCsrf();
        $adminId = current_admin_id();
        $id      = (int) ($_GET['id'] ?? 0);
        $action  = sanitize(input('action', 'suspend'));
        $status  = $action === 'unsuspend' ? 'active' : 'suspended';

        DB::execute("UPDATE users SET status=? WHERE id=?", [$status, $id]);
        $user = DB::fetch("SELECT email FROM users WHERE id=?", [$id]);
        audit_log($adminId, 'user_' . $action, "User #{$id} {$action}d", 'high', 'user', $id, $user['email'] ?? '');
        json_response(['success' => true]);
    }

    // ── Partner (special agent) status ─────────────────────────
    public static function setPartner(): void {
        AuthMiddleware::admin();
        AuthMiddleware::verifyCsrf();
        $adminId = current_admin_id();
        $id      = (int) ($_GET['id'] ?? 0);
        $user    = DB::fetch("SELECT * FROM users WHERE id=?", [$id]);
        if (!$user) json_response(['success' => false, 'error' => 'Investor not found.']);

        $enable = input('is_agent', '0') === '1';
        $name   = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));

        if (!$enable) {
            DB::execute("UPDATE users SET is_agent=0 WHERE id=?", [$id]);
            audit_log($adminId, 'partner_disabled', "Removed Partner status from user #{$id}", 'medium', 'user', $id, $name);
            json_response(['success' => true, 'message' => 'Partner status removed. Existing referral rates are unchanged.']);
        }

        $rate = (float) input('agent_commission', 0);
        if ($rate <= 0 || $rate > 100) {
            json_response(['success' => false, 'error' => 'Enter a commission rate between 0.1 and 100.']);
        }

        $wasAgent    = (int) ($user['is_agent'] ?? 0) === 1;
        $rateChanged = !$wasAgent || (float) ($user['agent_commission'] ?? 0) !== $rate;

        DB::execute("UPDATE users SET is_agent=1, agent_commission=? WHERE id=?", [$rate, $id]);
        audit_log($adminId, 'partner_enabled', "Set Partner ({$rate}%) for user #{$id}", 'medium', 'user', $id, $name);

        // Email the investor when newly activated or their rate changed
        if ($rateChanged) {
            try { Mailer::sendPartnerUpgrade($user, $rate); } catch (\Throwable $e) { error_log('Partner email error: ' . $e->getMessage()); }
        }
        json_response(['success' => true, 'message' => 'Partner status saved' . ($rateChanged ? ' — notification email sent.' : '.')]);
    }

    // ── Manually add a transaction record (any date/time) ──────
    public static function addTransaction(): void {
        AuthMiddleware::admin();
        AuthMiddleware::verifyCsrf();
        $adminId = current_admin_id();
        $id      = (int) ($_GET['id'] ?? 0);
        $user    = DB::fetch("SELECT * FROM users WHERE id=?", [$id]);
        if (!$user) json_response(['success' => false, 'error' => 'Investor not found.']);

        $creditTypes = ['deposit', 'return', 'referral_commission', 'transfer_received'];
        $debitTypes  = ['withdrawal', 'investment', 'debit', 'transfer_sent'];
        $type   = sanitize($_POST['type'] ?? '');
        if (!in_array($type, array_merge($creditTypes, $debitTypes, ['adjustment']), true)) {
            json_response(['success' => false, 'error' => 'Choose a valid transaction type.']);
        }
        $amount = round((float) input('amount', 0), 2);
        if ($amount <= 0) json_response(['success' => false, 'error' => 'Enter an amount greater than zero.']);

        $status = in_array(($_POST['status'] ?? 'completed'), ['completed','pending','failed','rejected'], true) ? $_POST['status'] : 'completed';
        $desc   = sanitize($_POST['description'] ?? '') ?: ucwords(str_replace('_', ' ', $type));
        $adjust = isset($_POST['adjust_balance']) && $_POST['adjust_balance'] === '1';

        // Chosen date/time (backdating allowed); default now
        $whenRaw = trim($_POST['occurred_at'] ?? '');
        $when    = 'now';
        if ($whenRaw !== '') { $ts = strtotime(str_replace('T', ' ', $whenRaw)); if ($ts !== false) $when = date('Y-m-d H:i:s', $ts); }
        $when    = $when === 'now' ? date('Y-m-d H:i:s') : $when;

        // Direction of the money for this type
        $isCredit = in_array($type, $creditTypes, true) || ($type === 'adjustment' && $amount > 0);
        $signed   = $isCredit ? $amount : -$amount;

        $balBefore = (float) $user['wallet_balance'];
        $balAfter  = $adjust ? round($balBefore + $signed, 2) : $balBefore;
        if ($adjust && $balAfter < 0) json_response(['success' => false, 'error' => 'This would put the wallet below zero.']);

        $ref = generate_reference('TXN');
        DB::query(
            "INSERT INTO transactions (user_id,type,amount,balance_before,balance_after,status,reference,description,admin_note,processed_by,processed_at,created_at)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?)",
            [$id, $type, $amount, $balBefore, $balAfter, $status, $ref, $desc, 'Manually added by admin', $adminId, $when, $when]
        );
        if ($adjust) DB::execute("UPDATE users SET wallet_balance=? WHERE id=?", [$balAfter, $id]);

        audit_log($adminId, 'transaction_added', "Added {$type} of " . fmt_currency($amount) . " for user #{$id}" . ($adjust ? ' (wallet adjusted)' : ' (record only)'), 'high', 'user', $id, trim($user['first_name'].' '.$user['last_name']));
        json_response(['success' => true, 'message' => 'Transaction added' . ($adjust ? ' and wallet updated.' : ' (record only).')]);
    }

    // ── Generate a full investment transaction history ─────────
    // Preview when commit != 1; writes everything when commit == 1.
    public static function generateHistory(): void {
        AuthMiddleware::admin();
        AuthMiddleware::verifyCsrf();
        $adminId = current_admin_id();
        $id      = (int) ($_GET['id'] ?? 0);
        $user    = DB::fetch("SELECT * FROM users WHERE id=?", [$id]);
        if (!$user) json_response(['success' => false, 'error' => 'Investor not found.']);

        $amount = round((float) input('amount', 0), 2);
        if ($amount <= 0) json_response(['success' => false, 'error' => 'Enter an amount greater than zero.']);

        // Investment plan or custom parameters
        $invId = (int) input('investment_id', 0);
        if ($invId > 0) {
            $inv = DB::fetch("SELECT * FROM investments WHERE id=?", [$invId]);
            if (!$inv) json_response(['success' => false, 'error' => 'Investment not found.']);
            $roi = (float) $inv['roi']; $dv = (int) $inv['duration_value']; $du = $inv['duration_unit'];
            $freq = $inv['payout_frequency']; $planName = $inv['name'];
        } else {
            $roi  = (float) input('roi', 0);
            $dv   = (int) input('duration_value', 0);
            $du   = in_array($_POST['duration_unit'] ?? '', ['days','weeks','months','years'], true) ? $_POST['duration_unit'] : 'months';
            $freq = in_array($_POST['payout_frequency'] ?? '', ['daily','weekly','monthly','quarterly','semi_annual','at_maturity'], true) ? $_POST['payout_frequency'] : 'monthly';
            $planName = sanitize(input('plan_name', '')) ?: 'Investment';
        }
        if ($roi <= 0 || $dv <= 0) json_response(['success' => false, 'error' => 'ROI and duration must be greater than zero.']);

        // Start date (must be in the past — this is history)
        $startRaw = trim($_POST['start_date'] ?? '');
        $startTs  = $startRaw !== '' ? strtotime(str_replace('T', ' ', $startRaw)) : strtotime("-{$dv} {$du}");
        if ($startTs === false) json_response(['success' => false, 'error' => 'Invalid start date.']);
        $now = time();
        if ($startTs > $now) json_response(['success' => false, 'error' => 'The start date must be in the past.']);

        $endTs   = strtotime("+{$dv} {$du}", $startTs);
        $upTo    = ($_POST['generate_up_to'] ?? 'today') === 'maturity' ? 'maturity' : 'today';
        $cutoffTs = $upTo === 'maturity' ? $endTs : min($now, $endTs);

        // Toggles
        $tDeposit   = input('create_deposit', '0') === '1';
        $tHolding   = input('create_holding', '0') === '1' && $invId > 0; // real holding needs a real plan
        $tPrincipal = input('return_principal', '0') === '1';
        $tWallet    = input('adjust_wallet', '0') === '1';

        // Full payout schedule (drift-free: start + i·period)
        $advance = function (int $i) use ($startTs, $freq, $endTs): int {
            return match ($freq) {
                'daily'       => strtotime("+{$i} days", $startTs),
                'weekly'      => strtotime("+" . ($i * 7) . " days", $startTs),
                'monthly'     => strtotime("+{$i} months", $startTs),
                'quarterly'   => strtotime("+" . ($i * 3) . " months", $startTs),
                'semi_annual' => strtotime("+" . ($i * 6) . " months", $startTs),
                'at_maturity' => $endTs,
                default       => strtotime("+{$i} months", $startTs),
            };
        };
        $fullDates = [];
        if ($freq === 'at_maturity') {
            $fullDates[] = $endTs;
        } else {
            for ($i = 1; $i <= 100000; $i++) { $ts = $advance($i); if ($ts > $endTs) break; $fullDates[] = $ts; }
        }
        $fullCount = count($fullDates);
        if ($fullCount < 1) json_response(['success' => false, 'error' => 'This produces no payouts — check the duration and frequency.']);

        $total     = round($amount * $roi / 100, 2);
        $perPayout = round($total / $fullCount, 2);
        $createDates = array_values(array_filter($fullDates, fn($ts) => $ts <= $cutoffTs));
        $paidCount   = count($createDates);
        $reachedMaturity = $cutoffTs >= $endTs;

        // Build the chronological transaction list
        $txList = [];
        if ($tDeposit) $txList[] = ['ts' => $startTs - 60, 'type' => 'deposit', 'sign' => 1, 'amt' => $amount, 'desc' => 'Wallet deposit'];
        $txList[] = ['ts' => $startTs, 'type' => 'investment', 'sign' => -1, 'amt' => $amount, 'desc' => 'Investment — ' . $planName];
        $sumReturns = 0.0;
        foreach ($createDates as $idx => $ts) {
            $amt = $perPayout;
            if ($paidCount === $fullCount && $idx === $fullCount - 1) $amt = round($total - $perPayout * ($fullCount - 1), 2); // absorb rounding on final
            $sumReturns += $amt;
            $txList[] = ['ts' => $ts, 'type' => 'return', 'sign' => 1, 'amt' => $amt, 'desc' => 'ROI payout — ' . $planName];
        }
        $principal = 0.0;
        if ($tPrincipal && $reachedMaturity) { $principal = $amount; $txList[] = ['ts' => $endTs, 'type' => 'return', 'sign' => 1, 'amt' => $amount, 'desc' => 'Principal returned — ' . $planName]; }
        usort($txList, fn($a, $b) => $a['ts'] <=> $b['ts']);

        $net = 0.0; foreach ($txList as $t) $net += $t['sign'] * $t['amt'];
        $curBal = (float) $user['wallet_balance'];
        $newBal = $tWallet ? round($curBal + $net, 2) : $curBal;
        $sym = platform_setting('platform_symbol', '$');

        if ($tWallet && $newBal < 0) {
            json_response(['success' => false, 'error' =>
                'With "adjust wallet" on this would put the balance at ' . $sym . number_format($newBal, 2) .
                '. Turn on "Add deposit first", raise the wallet, or switch off "adjust wallet".']);
        }

        // ── Preview ──
        if (input('commit', '0') !== '1') {
            json_response(['success' => true, 'preview' => [
                'total_tx'    => count($txList),
                'returns'     => $paidCount,
                'per_payout'  => $sym . number_format($perPayout, 2),
                'returns_sum' => $sym . number_format($sumReturns, 2),
                'principal'   => $principal > 0 ? $sym . number_format($principal, 2) : null,
                'deposit'     => $tDeposit ? $sym . number_format($amount, 2) : null,
                'investment'  => $sym . number_format($amount, 2),
                'from'        => date('j M Y', $txList[0]['ts']),
                'to'          => date('j M Y', end($txList)['ts']),
                'matured'     => $reachedMaturity,
                'holding'     => $tHolding,
                'wallet'      => $tWallet ? ($sym . number_format($curBal, 2) . ' → ' . $sym . number_format($newBal, 2)) : 'unchanged',
                'freq'        => $freq,
                'note'        => (input('create_holding','0') === '1' && $invId === 0) ? 'Custom plans cannot create a portfolio holding — history records only.' : '',
            ]]);
        }

        // ── Commit ──
        $batch = 'BAT-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        $note  = "batch={$batch} adj=" . ($tWallet ? '1' : '0');
        DB::beginTransaction();
        try {
            $holdingId = null;
            if ($tHolding) {
                $status = $reachedMaturity ? 'matured' : 'active';
                $lastPay = $paidCount > 0 ? date('Y-m-d H:i:s', end($createDates)) : null;
                $holdingId = (int) DB::insert(
                    "INSERT INTO investment_holdings (user_id,investment_id,amount,payment_method,status,start_date,end_date,roi,total_earned,last_payout_at,certificate_ref,created_at)
                     VALUES (?,?,?,?,?,?,?,?,?,?,?,?)",
                    [$id, $invId, $amount, 'wallet', $status, date('Y-m-d H:i:s', $startTs), date('Y-m-d H:i:s', $endTs), $roi, $sumReturns, $lastPay, generate_reference('CERT'), date('Y-m-d H:i:s', $startTs)]
                );
            }
            $running = $curBal;
            foreach ($txList as $t) {
                $signed = $t['sign'] * $t['amt'];
                if ($tWallet) { $before = $running; $running = round($running + $signed, 2); $after = $running; }
                else { $before = $curBal; $after = $curBal; }
                DB::query(
                    "INSERT INTO transactions (user_id,type,amount,balance_before,balance_after,status,reference,description,admin_note,holding_id,processed_by,processed_at,created_at)
                     VALUES (?,?,?,?,?,'completed',?,?,?,?,?,?,?)",
                    [$id, $t['type'], $t['amt'], $before, $after, generate_reference('TXN'), $t['desc'], $note, $holdingId, $adminId, date('Y-m-d H:i:s', $t['ts']), date('Y-m-d H:i:s', $t['ts'])]
                );
            }
            if ($tWallet) DB::execute("UPDATE users SET wallet_balance=? WHERE id=?", [$newBal, $id]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollback();
            error_log('generateHistory error: ' . $e->getMessage());
            json_response(['success' => false, 'error' => 'Failed to generate history — nothing was saved.']);
        }
        audit_log($adminId, 'history_generated', "Generated " . count($txList) . " transactions ({$batch}) for user #{$id}", 'high', 'user', $id, trim($user['first_name'] . ' ' . $user['last_name']));
        json_response(['success' => true, 'message' => count($txList) . ' transactions created.', 'batch' => $batch]);
    }

    public static function removeHistoryBatch(): void {
        AuthMiddleware::admin();
        AuthMiddleware::verifyCsrf();
        $adminId = current_admin_id();
        $id      = (int) ($_GET['id'] ?? 0);
        $batch   = preg_replace('/[^A-Za-z0-9-]/', '', input('batch', ''));
        if ($batch === '') json_response(['success' => false, 'error' => 'Missing batch reference.']);

        $txns = DB::fetchAll("SELECT * FROM transactions WHERE user_id=? AND admin_note LIKE ?", [$id, "batch={$batch}%"]);
        if (empty($txns)) json_response(['success' => false, 'error' => 'No transactions found for that batch.']);
        $adjusted = str_contains($txns[0]['admin_note'] ?? '', 'adj=1');

        DB::beginTransaction();
        try {
            $net = 0.0; $holdingIds = [];
            $credit = ['deposit', 'return', 'referral_commission', 'transfer_received'];
            foreach ($txns as $t) {
                $net += (in_array($t['type'], $credit, true) ? 1 : -1) * (float) $t['amount'];
                if (!empty($t['holding_id'])) $holdingIds[(int) $t['holding_id']] = true;
            }
            DB::execute("DELETE FROM transactions WHERE user_id=? AND admin_note LIKE ?", [$id, "batch={$batch}%"]);
            foreach (array_keys($holdingIds) as $hid) {
                DB::execute("DELETE FROM payout_schedules WHERE holding_id=?", [$hid]);
                DB::execute("DELETE FROM investment_holdings WHERE id=? AND user_id=?", [$hid, $id]);
            }
            if ($adjusted) DB::execute("UPDATE users SET wallet_balance = wallet_balance - ? WHERE id=?", [round($net, 2), $id]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollback();
            json_response(['success' => false, 'error' => 'Failed to remove the batch.']);
        }
        audit_log($adminId, 'history_batch_removed', "Removed generated batch {$batch} for user #{$id}", 'high', 'user', $id, '');
        json_response(['success' => true, 'message' => 'Batch removed and reversed.']);
    }


    // ── Ghost Login ────────────────────────────────────────────
    public static function ghostLogin(): void {
        AuthMiddleware::admin();
        require_super_admin();
        // Verify the request originates from this server (prevents CSRF via crafted link)
        $host    = $_SERVER['HTTP_HOST'] ?? '';
        $referer = parse_url($_SERVER['HTTP_REFERER'] ?? '', PHP_URL_HOST);
        if ($referer !== $host) redirect('/admin/users');
        $adminId = current_admin_id();
        $id      = (int) ($_GET['id'] ?? 0);
        $user    = DB::fetch("SELECT * FROM users WHERE id=? AND status='active'", [$id]);
        if (!$user) redirect('/admin/users');

        $_SESSION['ghost_admin_id']   = $adminId;
        $_SESSION['ghost_admin_name'] = $_SESSION['admin_name'];
        $_SESSION['user_id']          = $user['id'];
        $_SESSION['user_name']        = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['user_email']       = $user['email'];
        $_SESSION['kyc_status']       = $user['kyc_status'];
        $_SESSION['is_ghost']         = true;
        unset($_SESSION['admin_id'], $_SESSION['admin_role'], $_SESSION['admin_name'], $_SESSION['admin_email']);

        audit_log($adminId, 'ghost_login', "Ghost login into investor account #{$id} ({$user['email']})", 'high', 'user', $id, $user['email']);
        redirect('/investor/dashboard');
    }

    public static function exitGhost(): void {
        AuthMiddleware::init();
        if (!isset($_SESSION['ghost_admin_id'])) redirect('/admin/login');

        $adminId = (int) $_SESSION['ghost_admin_id'];
        $admin   = DB::fetch("SELECT * FROM admins WHERE id=? AND is_active=1", [$adminId]);
        if (!$admin) redirect('/admin/login');

        $ghostedUserId = $_SESSION['user_id'] ?? null;
        unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email'], $_SESSION['kyc_status'], $_SESSION['is_ghost'], $_SESSION['session_token']);
        $_SESSION['admin_id']    = $admin['id'];
        $_SESSION['admin_name']  = $admin['name'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_role']  = $admin['role'];
        unset($_SESSION['ghost_admin_id'], $_SESSION['ghost_admin_name']);

        audit_log($adminId, 'ghost_exit', "Exited ghost login from user #{$ghostedUserId}", 'medium');
        redirect('/admin/users');
    }

    // ── KYC ───────────────────────────────────────────────────
    public static function kycQueue(): void {
        AuthMiddleware::admin();
        $filter = sanitize($_GET['status'] ?? 'pending');
        if (!in_array($filter, ['pending','approved','rejected','all'], true)) $filter = 'pending';
        $where = $filter === 'all' ? '' : "WHERE ks.status='$filter'";
        $queue = DB::fetchAll(
            "SELECT ks.*, CONCAT(u.first_name,' ',u.last_name) AS user_name, u.email, u.country
             FROM kyc_submissions ks JOIN users u ON u.id=ks.user_id
             $where ORDER BY ks.submitted_at DESC"
        );
        view('admin.kyc_queue', compact('queue', 'filter'), 'admin');
    }

    public static function kycDetail(): void {
        AuthMiddleware::admin();
        $id  = (int) ($_GET['id'] ?? 0);
        $kyc = DB::fetch("SELECT ks.*, CONCAT(u.first_name,' ',u.last_name) AS user_name, u.email FROM kyc_submissions ks JOIN users u ON u.id=ks.user_id WHERE ks.id=?", [$id]);
        if (!$kyc) redirect('/admin/kyc');
        view('admin.kyc_detail', compact('kyc'), 'admin');
    }

    public static function approveKyc(): void {
        AuthMiddleware::admin();
        AuthMiddleware::verifyCsrf();
        $adminId = current_admin_id();
        $id      = (int) ($_GET['id'] ?? 0);
        $kyc     = DB::fetch("SELECT * FROM kyc_submissions WHERE id=? AND status='pending'", [$id]);
        if (!$kyc) json_response(['success' => false, 'error' => 'Submission not found.']);

        DB::beginTransaction();
        try {
            DB::execute("UPDATE kyc_submissions SET status='approved', reviewed_by=?, reviewed_at=NOW() WHERE id=?", [$adminId, $id]);
            DB::execute("UPDATE users SET kyc_status='verified' WHERE id=?", [$kyc['user_id']]);
            DB::commit();

            $user = DB::fetch("SELECT * FROM users WHERE id=?", [$kyc['user_id']]);
            try { Mailer::sendKycApproved($user); } catch (\Throwable $e) {}
            create_notification($kyc['user_id'], 'kyc', 'KYC Approved', 'Your identity has been verified. You now have full platform access.');
            audit_log($adminId, 'kyc_approved', "KYC approved for user #{$kyc['user_id']}", 'medium', 'user', $kyc['user_id'], $user['email']);
            json_response(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            json_response(['success' => false, 'error' => 'Failed to approve KYC.']);
        }
    }

    public static function rejectKyc(): void {
        AuthMiddleware::admin();
        AuthMiddleware::verifyCsrf();
        $adminId = current_admin_id();
        $id      = (int) ($_GET['id'] ?? 0);
        $reason  = sanitize(input('reason', ''));
        if (!$reason) json_response(['success' => false, 'error' => 'Rejection reason is required.']);

        $kyc = DB::fetch("SELECT * FROM kyc_submissions WHERE id=? AND status='pending'", [$id]);
        if (!$kyc) json_response(['success' => false, 'error' => 'Submission not found.']);

        DB::execute("UPDATE kyc_submissions SET status='rejected', rejection_reason=?, reviewed_by=?, reviewed_at=NOW() WHERE id=?", [$reason, $adminId, $id]);
        DB::execute("UPDATE users SET kyc_status='rejected' WHERE id=?", [$kyc['user_id']]);

        $user = DB::fetch("SELECT * FROM users WHERE id=?", [$kyc['user_id']]);
        try { Mailer::sendKycRejected($user, $reason); } catch (\Throwable $e) {}
        create_notification($kyc['user_id'], 'kyc', 'KYC Rejected', 'Your KYC submission requires resubmission. Please check your email for details.');
        audit_log($adminId, 'kyc_rejected', "KYC rejected for user #{$kyc['user_id']}. Reason: {$reason}", 'medium', 'user', $kyc['user_id'], $user['email']);
        json_response(['success' => true]);
    }

    // ── Investments ────────────────────────────────────────────
    public static function investments(): void {
        AuthMiddleware::admin();
        $investments = DB::fetchAll("SELECT i.*, (SELECT COUNT(*) FROM investment_holdings ih WHERE ih.investment_id=i.id AND ih.status='active') AS investor_count FROM investments i ORDER BY i.created_at DESC");
        view('admin.investments', compact('investments'), 'admin');
    }

    public static function createInvestment(): void {
        AuthMiddleware::admin();
        view('admin.investment_form', ['investment' => null, 'title' => 'New Investment'], 'admin');
    }

    public static function storeInvestment(): void {
        AuthMiddleware::admin();
        AuthMiddleware::verifyCsrf();
        $adminId = current_admin_id();
        $data    = self::parseInvestmentPost();

        $slug = self::generateSlug($data['name']);
        while (DB::fetch("SELECT id FROM investments WHERE slug=?", [$slug])) {
            $slug .= '-' . substr(bin2hex(random_bytes(2)), 0, 4);
        }

        DB::beginTransaction();
        try {
            $id = (int) DB::insert(
                "INSERT INTO investments (name, slug, type, status, short_desc, description, roi, duration_value, duration_unit,
                  payout_frequency, min_investment, max_investment, funding_target, property_type, street_address, city, state_region,
                  country, postcode, maps_link, property_size, total_units, occupancy_rate, year_built, completion_date,
                  ticker, fund_category, risk_level, management_fee, benchmark, fund_start_date, fund_end_date,
                  is_featured, is_verified, notify_on_launch, created_by)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
                [
                    $data['name'], $slug, $data['type'], $data['status'], $data['short_desc'], $data['description'],
                    $data['roi'], $data['duration_value'], $data['duration_unit'], $data['payout_frequency'],
                    $data['min_investment'], $data['max_investment'], $data['funding_target'], $data['property_type'],
                    $data['street_address'], $data['city'], $data['state_region'], $data['country'], $data['postcode'],
                    $data['maps_link'], $data['property_size'], $data['total_units'], $data['occupancy_rate'],
                    $data['year_built'], $data['completion_date'], $data['ticker'], $data['fund_category'],
                    $data['risk_level'], $data['management_fee'], $data['benchmark'], $data['fund_start_date'],
                    $data['fund_end_date'], $data['is_featured'], $data['is_verified'], $data['notify_on_launch'],
                    $adminId,
                ]
            );

            // Upload image
            if (!empty($_FILES['image']['name'])) {
                $imgPath = upload_file($_FILES['image'], CONFIG['upload']['inv_path'], CONFIG['upload']['img_types']);
                if ($imgPath) DB::execute("UPDATE investments SET image=? WHERE id=?", [$imgPath, $id]);
            }

            // Upload documents
            if (!empty($_FILES['documents']['name'][0])) {
                $files = self::reArrayFiles($_FILES['documents']);
                foreach ($files as $file) {
                    $path = upload_file($file, CONFIG['upload']['inv_path'], CONFIG['upload']['doc_types']);
                    if ($path) DB::query("INSERT INTO investment_documents (investment_id, name, file_path, file_size, uploaded_by) VALUES (?,?,?,?,?)", [$id, $file['name'], $path, $file['size'], $adminId]);
                }
            }

            // Fund holdings (index fund top holdings)
            if (!empty($_POST['fund_holdings'])) {
                $holdings = array_filter(array_map('trim', explode("\n", $_POST['fund_holdings'])));
                foreach ($holdings as $i => $h) {
                    DB::query("INSERT INTO fund_holdings (investment_id, holding_name, sort_order) VALUES (?,?,?)", [$id, sanitize($h), $i]);
                }
            }

            DB::commit();
            audit_log($adminId, 'investment_created', "Created investment: {$data['name']}", 'medium', 'investment', $id, $data['name']);

            if ($data['notify_on_launch']) {
                self::notifyInvestorsNewInvestment($id, $data['name']);
            }

            json_response(['success' => true, 'redirect' => '/admin/investments']);
        } catch (Exception $e) {
            DB::rollback();
            error_log('Create investment error: ' . $e->getMessage());
            json_response(['success' => false, 'error' => 'Failed to create investment. Please try again.']);
        }
    }

    public static function editInvestment(): void {
        AuthMiddleware::admin();
        $id         = (int) ($_GET['id'] ?? 0);
        $investment = DB::fetch("SELECT * FROM investments WHERE id=?", [$id]);
        if (!$investment) redirect('/admin/investments');
        $investment['holdings'] = DB::fetchAll("SELECT * FROM fund_holdings WHERE investment_id=? ORDER BY sort_order", [$id]);
        $investment['documents']= DB::fetchAll("SELECT * FROM investment_documents WHERE investment_id=?", [$id]);
        view('admin.investment_form', compact('investment') + ['title' => 'Edit Investment'], 'admin');
    }

    public static function updateInvestment(): void {
        AuthMiddleware::admin();
        AuthMiddleware::verifyCsrf();
        $adminId = current_admin_id();
        $id      = (int) ($_GET['id'] ?? 0);
        $old     = DB::fetch("SELECT * FROM investments WHERE id=?", [$id]);
        if (!$old) json_response(['success' => false, 'error' => 'Investment not found.']);

        $data = self::parseInvestmentPost();

        DB::execute(
            "UPDATE investments SET name=?,type=?,status=?,short_desc=?,description=?,roi=?,duration_value=?,duration_unit=?,
              payout_frequency=?,min_investment=?,max_investment=?,funding_target=?,property_type=?,street_address=?,city=?,
              state_region=?,country=?,postcode=?,maps_link=?,property_size=?,total_units=?,occupancy_rate=?,year_built=?,
              completion_date=?,ticker=?,fund_category=?,risk_level=?,management_fee=?,benchmark=?,fund_start_date=?,
              fund_end_date=?,is_featured=?,is_verified=?,notify_on_launch=?,updated_at=NOW() WHERE id=?",
            [...array_values($data), $id]
        );

        // Upload image
        if (!empty($_FILES['image']['name'])) {
            $imgPath = upload_file($_FILES['image'], CONFIG['upload']['inv_path'], CONFIG['upload']['img_types']);
            if ($imgPath) DB::execute("UPDATE investments SET image=? WHERE id=?", [$imgPath, $id]);
        }

        // Upload documents
        if (!empty($_FILES['documents']['name'][0])) {
            $files = self::reArrayFiles($_FILES['documents']);
            foreach ($files as $file) {
                $path = upload_file($file, CONFIG['upload']['inv_path'], CONFIG['upload']['doc_types']);
                if ($path) DB::query("INSERT INTO investment_documents (investment_id, name, file_path, file_size, uploaded_by) VALUES (?,?,?,?,?)", [$id, $file['name'], $path, $file['size'], $adminId]);
            }
        }

        // Fund holdings — replace the full set so edits, additions and removals all persist
        if (isset($_POST['fund_holdings'])) {
            DB::execute("DELETE FROM fund_holdings WHERE investment_id=?", [$id]);
            $holdings = array_filter(array_map('trim', explode("\n", $_POST['fund_holdings'])));
            foreach (array_values($holdings) as $i => $h) {
                DB::query("INSERT INTO fund_holdings (investment_id, holding_name, sort_order) VALUES (?,?,?)", [$id, sanitize($h), $i]);
            }
        }

        audit_log($adminId, 'investment_edited', "Edited investment #{$id}: {$data['name']}", 'medium', 'investment', $id, $data['name']);
        json_response(['success' => true, 'message' => 'Investment updated.']);
    }

    public static function deleteInvestment(): void {
        AuthMiddleware::admin();
        require_super_admin();
        AuthMiddleware::verifyCsrf();
        $adminId = current_admin_id();
        $id      = (int) ($_GET['id'] ?? 0);

        $inv = DB::fetch("SELECT name FROM investments WHERE id=?", [$id]);
        if (!$inv) json_response(['success' => false, 'error' => 'Investment not found.']);

        $active = (DB::fetch("SELECT COUNT(*) AS c FROM investment_holdings WHERE investment_id=? AND status='active'", [$id]) ?? [])['c'] ?? 0;
        if ((int)$active > 0) json_response(['success' => false, 'error' => 'Cannot delete an investment with active holders.']);

        DB::execute("DELETE FROM investments WHERE id=?", [$id]);
        audit_log($adminId, 'investment_deleted', "Deleted investment #{$id}: {$inv['name']}", 'high', 'investment', $id, $inv['name']);
        json_response(['success' => true]);
    }

    // ── Withdrawals ────────────────────────────────────────────
    public static function withdrawals(): void {
        AuthMiddleware::admin();
        $filter = sanitize($_GET['status'] ?? 'pending');
        $where  = $filter !== 'all' ? "WHERE wr.status=?" : "WHERE 1=1";
        $params = $filter !== 'all' ? [$filter] : [];

        $withdrawals = DB::fetchAll(
            "SELECT wr.*, CONCAT(u.first_name,' ',u.last_name) AS user_name, u.email
             FROM withdrawal_requests wr JOIN users u ON u.id=wr.user_id {$where} ORDER BY wr.created_at DESC",
            $params
        );
        view('admin.withdrawals', compact('withdrawals','filter'), 'admin');
    }

    public static function approveWithdrawal(): void {
        AuthMiddleware::admin();
        AuthMiddleware::verifyCsrf();
        $adminId = current_admin_id();
        $id      = (int) ($_GET['id'] ?? 0);
        $wr      = DB::fetch("SELECT * FROM withdrawal_requests WHERE id=? AND status='pending'", [$id]);
        if (!$wr) json_response(['success' => false, 'error' => 'Request not found.']);

        DB::execute("UPDATE withdrawal_requests SET status='approved', reviewed_by=?, reviewed_at=NOW() WHERE id=?", [$adminId, $id]);
        DB::execute("UPDATE transactions SET status='pending' WHERE reference=?", [$wr['reference']]);

        $user = DB::fetch("SELECT * FROM users WHERE id=?", [$wr['user_id']]);
        try { Mailer::sendWithdrawalApproved($user, ['reference' => $wr['reference'], 'amount' => $wr['amount'], 'method' => $wr['method']]); } catch (\Throwable $e) {}
        create_notification($wr['user_id'], 'withdrawal', 'Withdrawal Approved', "Your withdrawal of " . fmt_currency((float)$wr['amount']) . " has been approved.");
        audit_log($adminId, 'withdrawal_approved', "Approved withdrawal {$wr['reference']} of " . fmt_currency((float)$wr['amount']), 'high', 'user', $wr['user_id']);
        json_response(['success' => true]);
    }

    public static function rejectWithdrawal(): void {
        AuthMiddleware::admin();
        AuthMiddleware::verifyCsrf();
        $adminId = current_admin_id();
        $id      = (int) ($_GET['id'] ?? 0);
        $note    = sanitize($_POST['note'] ?? '');
        $wr      = DB::fetch("SELECT * FROM withdrawal_requests WHERE id=? AND status='pending'", [$id]);
        if (!$wr) json_response(['success' => false, 'error' => 'Request not found.']);

        DB::beginTransaction();
        try {
            DB::execute("UPDATE withdrawal_requests SET status='rejected', rejection_note=?, reviewed_by=?, reviewed_at=NOW() WHERE id=?", [$note, $adminId, $id]);
            // Refund reserved balance
            DB::execute("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id=?", [$wr['amount'], $wr['user_id']]);
            DB::execute("UPDATE transactions SET status='rejected', admin_note=? WHERE reference=?", [$note, $wr['reference']]);
            DB::commit();

            create_notification($wr['user_id'], 'withdrawal', 'Withdrawal Rejected', "Your withdrawal request was rejected. Funds have been returned to your wallet.");
            audit_log($adminId, 'withdrawal_rejected', "Rejected withdrawal {$wr['reference']}", 'high', 'user', $wr['user_id']);
            json_response(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            json_response(['success' => false, 'error' => 'Failed to reject withdrawal.']);
        }
    }

    public static function completeWithdrawal(): void {
        AuthMiddleware::admin();
        AuthMiddleware::verifyCsrf();
        $adminId = current_admin_id();
        $id      = (int) ($_GET['id'] ?? 0);
        $wr      = DB::fetch("SELECT * FROM withdrawal_requests WHERE id=? AND status='approved'", [$id]);
        if (!$wr) json_response(['success' => false, 'error' => 'Request not found or not approved.']);

        DB::execute("UPDATE withdrawal_requests SET status='completed', completed_at=NOW() WHERE id=?", [$id]);
        DB::execute("UPDATE transactions SET status='completed', processed_by=?, processed_at=NOW() WHERE reference=?", [$adminId, $wr['reference']]);

        $user = DB::fetch("SELECT * FROM users WHERE id=?", [$wr['user_id']]);
        try { Mailer::sendWithdrawalCompleted($user, ['reference' => $wr['reference'], 'amount' => $wr['amount'], 'method' => $wr['method']]); } catch (\Throwable $e) {}
        create_notification($wr['user_id'], 'withdrawal', 'Withdrawal Completed', "Your withdrawal of " . fmt_currency((float)$wr['amount']) . " has been processed.");
        audit_log($adminId, 'withdrawal_completed', "Completed withdrawal {$wr['reference']}", 'medium', 'user', $wr['user_id']);
        json_response(['success' => true]);
    }

    // ── Deposits ──────────────────────────────────────────────
    public static function deposits(): void {
        AuthMiddleware::admin();
        $filter = sanitize($_GET['status'] ?? 'submitted');
        $page   = max(1, (int)($_GET['page'] ?? 1));

        if ($filter === 'all') {
            $where  = "WHERE di.status IN ('pending','submitted','paid','rejected')";
            $params = [];
        } else {
            $where  = "WHERE di.status=?";
            $params = [$filter];
        }

        $result = paginate(
            "SELECT COUNT(*) AS total FROM deposit_invoices di {$where}",
            "SELECT di.*, CONCAT(u.first_name,' ',u.last_name) AS user_name, u.email AS user_email
             FROM deposit_invoices di JOIN users u ON u.id=di.user_id {$where} ORDER BY di.created_at DESC",
            $params, $page, 25
        );

        $pendingCount = (int)((DB::fetch("SELECT COUNT(*) AS c FROM deposit_invoices WHERE status='submitted'") ?? [])['c'] ?? 0);
        view('admin.deposits', array_merge(compact('filter','pendingCount'), $result), 'admin');
    }

    public static function approveDeposit(): void {
        AuthMiddleware::admin();
        AuthMiddleware::verifyCsrf();
        $adminId = current_admin_id();
        $id      = (int)($_GET['id'] ?? 0);
        $note    = sanitize(input('note', ''));

        $invoice = DB::fetch(
            "SELECT di.*, u.wallet_balance, u.email, u.first_name, u.last_name,
                    CONCAT(u.first_name,' ',u.last_name) AS user_name
             FROM deposit_invoices di JOIN users u ON u.id=di.user_id WHERE di.id=?",
            [$id]
        );
        if (!$invoice) json_response(['success' => false, 'error' => 'Invoice not found.']);

        $user = ['first_name' => $invoice['first_name'], 'last_name' => $invoice['last_name'], 'email' => $invoice['email']];
        $ref  = generate_reference('DEP');

        DB::beginTransaction();
        try {
            // Re-check status inside the transaction with a locking read to prevent double-approval
            $locked = DB::fetch("SELECT status FROM deposit_invoices WHERE id=? FOR UPDATE", [$id]);
            if (!$locked || !in_array($locked['status'], ['pending','submitted'], true)) {
                DB::rollback();
                json_response(['success' => false, 'error' => 'Invoice already processed.']);
            }
            if (!empty($invoice['holding_id'])) {
                // ── Investment deposit: activate holding ──────────────
                $holding    = DB::fetch(
                    "SELECT ih.*, i.name AS inv_name, i.roi AS inv_roi FROM investment_holdings ih
                     JOIN investments i ON i.id=ih.investment_id WHERE ih.id=?",
                    [(int)$invoice['holding_id']]
                );
                $investment = $holding ? DB::fetch("SELECT * FROM investments WHERE id=?", [$holding['investment_id']]) : null;

                DB::execute("UPDATE investment_holdings SET status='active', payment_ref=? WHERE id=?", [$ref, (int)$invoice['holding_id']]);
                if ($holding) {
                    DB::execute("UPDATE investments SET funding_raised = funding_raised + ? WHERE id=?", [(float)$invoice['amount'], $holding['investment_id']]);
                }
                DB::query(
                    "INSERT INTO transactions (user_id,type,amount,balance_before,balance_after,status,method,reference,description,processed_by,processed_at)
                     VALUES (?,'investment',?,?,?,'completed',?,?,?,?,NOW())",
                    [$invoice['user_id'], $invoice['amount'],
                     (float)$invoice['wallet_balance'], (float)$invoice['wallet_balance'],
                     $invoice['method'], $ref,
                     'Investment confirmed: ' . ($holding['inv_name'] ?? ''), $adminId]
                );

                // Referral commission — paid on every investment by a referred user
                $referral = DB::fetch(
                    "SELECT r.*, u2.wallet_balance AS ref_bal FROM referrals r
                     JOIN users u2 ON u2.id=r.referrer_id
                     WHERE r.referred_id=?",
                    [$invoice['user_id']]
                );
                if ($referral) {
                    // Use the rate captured on the referral at signup (Partner rate for
                    // Partner referrals), falling back to the global rate for old rows.
                    $commRate   = (float) ($referral['commission_rate'] ?? platform_setting('referral_commission', '5'));
                    if ($commRate <= 0) $commRate = (float) platform_setting('referral_commission', '5');
                    $commission = round((float)$invoice['amount'] * $commRate / 100, 2);
                    if ($commission > 0) {
                        $commRef = generate_reference('COM');
                        $rBefore = (float)$referral['ref_bal'];
                        $rAfter  = $rBefore + $commission;
                        DB::execute("UPDATE users SET wallet_balance=? WHERE id=?", [$rAfter, $referral['referrer_id']]);
                        DB::query(
                            "INSERT INTO transactions (user_id,type,amount,balance_before,balance_after,status,reference,description,processed_by,processed_at)
                             VALUES (?,'referral_commission',?,?,?,'completed',?,?,?,NOW())",
                            [$referral['referrer_id'], $commission, $rBefore, $rAfter, $commRef,
                             'Referral commission for investor #' . $invoice['user_id'], $adminId]
                        );
                        DB::execute(
                            "UPDATE referrals SET commission_amount = commission_amount + ?, status='commission_paid' WHERE id=?",
                            [$commission, $referral['id']]
                        );
                        create_notification($referral['referrer_id'], 'referral', 'Referral Commission Received',
                            fmt_currency($commission) . ' commission credited for your referral.');
                        $referrer = DB::fetch("SELECT * FROM users WHERE id=?", [$referral['referrer_id']]);
                        $referred = DB::fetch("SELECT * FROM users WHERE id=?", [$invoice['user_id']]);
                        try { Mailer::sendReferralCommission($referrer, $referred, $commission); } catch (\Throwable $e) {}
                    }
                }

                DB::execute(
                    "UPDATE deposit_invoices SET status='paid', confirmed_at=NOW(), reviewed_by=?, reviewed_at=NOW(), admin_note=? WHERE id=?",
                    [$adminId, $note ?: null, $id]
                );
                DB::commit();

                create_notification($invoice['user_id'], 'investment', 'Investment Confirmed',
                    'Your investment of ' . fmt_currency((float)$invoice['amount']) . ' is now active. Ref: ' . $ref);
                if ($holding && $investment) {
                    try { Mailer::sendInvestmentConfirmed($user, $holding, $investment); } catch (\Throwable $e) {}
                }
                audit_log($adminId, 'investment_activated',
                    "Activated holding #{$invoice['holding_id']} — " . fmt_currency((float)$invoice['amount']),
                    'high', 'user', $invoice['user_id'], $invoice['user_name']);

            } else {
                // ── Wallet top-up: credit balance ─────────────────────
                $balBefore = (float)$invoice['wallet_balance'];
                $balAfter  = $balBefore + (float)$invoice['amount'];

                DB::execute("UPDATE users SET wallet_balance=? WHERE id=?", [$balAfter, $invoice['user_id']]);
                DB::query(
                    "INSERT INTO transactions (user_id,type,amount,balance_before,balance_after,status,method,reference,description,processed_by,processed_at)
                     VALUES (?,'deposit',?,?,?,'completed',?,?,'Deposit confirmed by admin',?,NOW())",
                    [$invoice['user_id'], $invoice['amount'], $balBefore, $balAfter, $invoice['method'], $ref, $adminId]
                );
                DB::execute(
                    "UPDATE deposit_invoices SET status='paid', confirmed_at=NOW(), reviewed_by=?, reviewed_at=NOW(), admin_note=? WHERE id=?",
                    [$adminId, $note ?: null, $id]
                );
                DB::commit();

                create_notification($invoice['user_id'], 'deposit', 'Deposit Confirmed',
                    fmt_currency((float)$invoice['amount']) . ' has been credited to your wallet. Reference: ' . $ref);
                try { Mailer::sendDepositConfirmed($user, $invoice); } catch (\Throwable $e) {}
                audit_log($adminId, 'deposit_approved',
                    "Approved deposit {$invoice['reference']} — " . fmt_currency((float)$invoice['amount']),
                    'medium', 'user', $invoice['user_id'], $invoice['user_name']);
            }

            json_response(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            error_log('Deposit approve error: ' . $e->getMessage());
            json_response(['success' => false, 'error' => 'Failed to approve deposit.']);
        }
    }

    public static function rejectDeposit(): void {
        AuthMiddleware::admin();
        AuthMiddleware::verifyCsrf();
        $adminId = current_admin_id();
        $id      = (int)($_GET['id'] ?? 0);
        $reason  = sanitize(input('reason', ''));

        if (!$reason) json_response(['success' => false, 'error' => 'Rejection reason is required.']);

        $invoice = DB::fetch(
            "SELECT di.*, CONCAT(u.first_name,' ',u.last_name) AS user_name FROM deposit_invoices di JOIN users u ON u.id=di.user_id WHERE di.id=?",
            [$id]
        );
        if (!$invoice) json_response(['success' => false, 'error' => 'Invoice not found.']);
        if (!in_array($invoice['status'], ['pending','submitted'], true)) {
            json_response(['success' => false, 'error' => 'Invoice already processed.']);
        }

        DB::execute(
            "UPDATE deposit_invoices SET status='rejected', reviewed_by=?, reviewed_at=NOW(), admin_note=? WHERE id=?",
            [$adminId, $reason, $id]
        );

        create_notification($invoice['user_id'], 'deposit', 'Deposit Not Confirmed', 'Your deposit of ' . fmt_currency((float)$invoice['amount']) . ' could not be confirmed. Reason: ' . $reason . '. Please contact support if you believe this is an error.');
        audit_log($adminId, 'deposit_rejected', "Rejected deposit {$invoice['reference']}: {$reason}", 'medium', 'user', $invoice['user_id'], $invoice['user_name']);

        json_response(['success' => true]);
    }

    // ── Support Tickets ────────────────────────────────────────
    public static function tickets(): void {
        AuthMiddleware::admin();
        $filter  = sanitize($_GET['status'] ?? 'open');
        $where   = $filter !== 'all' ? "WHERE st.status=?" : "WHERE 1=1";
        $params  = $filter !== 'all' ? [$filter] : [];
        $tickets = DB::fetchAll(
            "SELECT st.*, CONCAT(u.first_name,' ',u.last_name) AS user_name, u.email,
                    (SELECT COUNT(*) FROM ticket_messages WHERE ticket_id=st.id) AS msg_count
             FROM support_tickets st JOIN users u ON u.id=st.user_id {$where} ORDER BY st.updated_at DESC",
            $params
        );
        $active = null;
        if (isset($_GET['ticket'])) {
            $tid    = (int) $_GET['ticket'];
            $active = DB::fetch("SELECT st.*, CONCAT(u.first_name,' ',u.last_name) AS user_name, u.email FROM support_tickets st JOIN users u ON u.id=st.user_id WHERE st.id=?", [$tid]);
            if ($active) $active['messages'] = DB::fetchAll("SELECT * FROM ticket_messages WHERE ticket_id=? ORDER BY created_at", [$tid]);
        }
        view('admin.tickets', compact('tickets','active','filter'), 'admin');
    }

    public static function ticketDetail(): void {
        AuthMiddleware::admin();
        $id     = (int) ($_GET['id'] ?? 0);
        $ticket = DB::fetch("SELECT st.*, CONCAT(u.first_name,' ',u.last_name) AS user_name, u.email FROM support_tickets st JOIN users u ON u.id=st.user_id WHERE st.id=?", [$id]);
        if (!$ticket) redirect('/admin/tickets');
        $ticket['messages'] = DB::fetchAll("SELECT * FROM ticket_messages WHERE ticket_id=? ORDER BY created_at", [$id]);
        view('admin.ticket_detail', compact('ticket'), 'admin');
    }

    public static function replyTicket(): void {
        AuthMiddleware::admin();
        AuthMiddleware::verifyCsrf();
        $adminId  = current_admin_id();
        $ticketId = (int) ($_GET['id'] ?? 0);
        $message  = sanitize(input('message', ''));
        if (!$message) json_response(['success' => false, 'error' => 'Message is required.']);

        $ticket = DB::fetch("SELECT st.*, CONCAT(u.first_name,' ',u.last_name) AS user_name, u.email FROM support_tickets st JOIN users u ON u.id=st.user_id WHERE st.id=?", [$ticketId]);
        if (!$ticket) json_response(['success' => false, 'error' => 'Ticket not found.']);

        DB::query("INSERT INTO ticket_messages (ticket_id, sender_type, sender_id, message) VALUES (?,'admin',?,?)", [$ticketId, $adminId, $message]);
        DB::execute("UPDATE support_tickets SET status='in_progress', updated_at=NOW() WHERE id=?", [$ticketId]);

        $user = DB::fetch("SELECT * FROM users WHERE id=?", [$ticket['user_id']]);
        try { Mailer::sendTicketReplied($user, ['reference' => $ticket['reference'], 'subject' => $ticket['subject']], $message); } catch (\Throwable $e) {}
        audit_log($adminId, 'ticket_replied', "Replied to ticket {$ticket['reference']}", 'low', 'ticket', $ticketId, $ticket['subject']);
        json_response(['success' => true]);
    }

    public static function closeTicket(): void {
        AuthMiddleware::admin();
        AuthMiddleware::verifyCsrf();
        $adminId  = current_admin_id();
        $ticketId = (int) ($_GET['id'] ?? 0);
        DB::execute("UPDATE support_tickets SET status='closed', closed_at=NOW() WHERE id=?", [$ticketId]);
        $ticket = DB::fetch("SELECT reference FROM support_tickets WHERE id=?", [$ticketId]);
        audit_log($adminId, 'ticket_closed', "Closed ticket #{$ticketId}", 'low', 'ticket', $ticketId, $ticket['reference'] ?? '');
        json_response(['success' => true]);
    }

    // ── Audit Log ──────────────────────────────────────────────
    public static function auditLog(): void {
        AuthMiddleware::admin();
        $page     = max(1, (int)($_GET['page']     ?? 1));
        $search   = sanitize($_GET['q']            ?? '');
        $adminF   = (int) ($_GET['admin']          ?? 0);
        $actionF  = sanitize($_GET['action']       ?? '');
        $sevF     = sanitize($_GET['severity']     ?? '');

        $where  = "1=1";
        $params = [];
        if ($search)  { $where .= " AND (al.detail LIKE ? OR al.target_name LIKE ?)"; $like="%{$search}%"; $params=array_merge($params,[$like,$like]); }
        if ($adminF)  { $where .= " AND al.admin_id=?"; $params[] = $adminF; }
        if ($actionF) { $where .= " AND al.action=?";   $params[] = $actionF; }
        if ($sevF)    { $where .= " AND al.severity=?"; $params[] = $sevF; }

        $result = paginate(
            "SELECT COUNT(*) AS total FROM audit_logs al WHERE {$where}",
            "SELECT al.*, a.name AS admin_name, a.role AS admin_role FROM audit_logs al JOIN admins a ON a.id=al.admin_id WHERE {$where} ORDER BY al.created_at DESC",
            $params, $page, 25
        );

        $admins  = DB::fetchAll("SELECT id, name FROM admins WHERE is_active=1");
        $actions = DB::fetchAll("SELECT DISTINCT action FROM audit_logs ORDER BY action");

        view('admin.audit_log', array_merge(compact('admins','actions','search','adminF','actionF','sevF'), $result), 'admin');
    }

    // ── Settings ───────────────────────────────────────────────
    public static function settings(): void {
        AuthMiddleware::admin();
        $branding  = platform_settings_group('branding');
        $finance   = platform_settings_group('finance');
        $features  = platform_settings_group('features');
        $payments  = platform_settings_group('payments');
        $referrals = platform_settings_group('referrals');
        $smtp      = platform_settings_group('email');
        $legal     = platform_settings_group('legal');
        view('admin.settings', compact('branding','finance','features','payments','referrals','smtp','legal'), 'admin');
    }

    public static function saveSettings(): void {
        AuthMiddleware::admin();
        AuthMiddleware::verifyCsrf();
        $adminId = current_admin_id();

        $allowed = [
            // Branding
            'platform_name','platform_tagline','platform_initials','platform_email','platform_support_email','platform_phone',
            'platform_address','platform_website',
            // Branding — auth sidebar marketing copy
            'auth_headline','auth_subtext',
            'auth_stat1_value','auth_stat1_label','auth_stat2_value','auth_stat2_label','auth_stat3_value','auth_stat3_label',
            // Certificate signatories
            'cert_signatory1_name','cert_signatory1_title','cert_signatory2_name','cert_signatory2_title',
            // Finance
            'platform_currency','platform_symbol',
            // Features
            'kyc_enabled','two_fa_enabled','registration_open','maintenance_mode','email_verification_enabled',
            // Payments — toggles
            'payment_crypto','payment_paypal','payment_wire','invoice_wallet_payment',
            // Payments — crypto wallets
            'crypto_btc_address','crypto_eth_address','crypto_usdt_address','crypto_usdc_address',
            // Payments — PayPal
            'paypal_email','paypal_me_link',
            // Payments — wire
            'wire_bank_name','wire_account_name','wire_account_number','wire_routing','wire_swift','wire_bank_country',
            // Payments — misc
            'deposit_timeout','min_deposit','min_withdrawal',
            // Referrals
            'referral_commission',
            // SMTP
            'smtp_host','smtp_port','smtp_user','smtp_pass','smtp_secure','smtp_from_name',
            'admin_notification_email',
            // General
            'logout_redirect_url','maintenance_message',
            // Integrations
            'smartsupp_code',
            // Legal
            'legal_company_name','legal_registration_number','legal_regulator','legal_jurisdiction',
            'legal_terms','legal_privacy',
        ];

        $keyGroups = [
            'platform_name' => 'branding', 'platform_tagline' => 'branding', 'platform_initials' => 'branding',
            'platform_email' => 'branding', 'platform_support_email' => 'branding', 'platform_phone' => 'branding',
            'platform_address' => 'branding', 'platform_website' => 'branding',
            'auth_headline' => 'branding', 'auth_subtext' => 'branding',
            'auth_stat1_value' => 'branding', 'auth_stat1_label' => 'branding',
            'auth_stat2_value' => 'branding', 'auth_stat2_label' => 'branding',
            'auth_stat3_value' => 'branding', 'auth_stat3_label' => 'branding',
            'cert_signatory1_name' => 'branding', 'cert_signatory1_title' => 'branding',
            'cert_signatory2_name' => 'branding', 'cert_signatory2_title' => 'branding',
            'platform_currency' => 'finance', 'platform_symbol' => 'finance',
            'kyc_enabled' => 'features', 'two_fa_enabled' => 'features',
            'registration_open' => 'features', 'maintenance_mode' => 'features',
            'email_verification_enabled' => 'features',
            'payment_crypto' => 'payments', 'payment_paypal' => 'payments', 'payment_wire' => 'payments', 'invoice_wallet_payment' => 'payments',
            'crypto_btc_address' => 'payments', 'crypto_eth_address' => 'payments',
            'crypto_usdt_address' => 'payments', 'crypto_usdc_address' => 'payments',
            'paypal_email' => 'payments', 'paypal_me_link' => 'payments',
            'wire_bank_name' => 'payments', 'wire_account_name' => 'payments',
            'wire_account_number' => 'payments', 'wire_routing' => 'payments',
            'wire_swift' => 'payments', 'wire_bank_country' => 'payments',
            'deposit_timeout' => 'payments', 'min_deposit' => 'payments', 'min_withdrawal' => 'payments',
            'referral_commission' => 'referrals',
            'smtp_host' => 'email', 'smtp_port' => 'email', 'smtp_user' => 'email',
            'smtp_pass' => 'email', 'smtp_secure' => 'email', 'smtp_from_name' => 'email',
            'admin_notification_email' => 'email',
            'logout_redirect_url' => 'general', 'maintenance_message' => 'features',
            'smartsupp_code' => 'integrations',
            'legal_company_name' => 'legal', 'legal_registration_number' => 'legal',
            'legal_regulator' => 'legal', 'legal_jurisdiction' => 'legal',
            'legal_terms' => 'legal', 'legal_privacy' => 'legal',
        ];

        $rawKeys      = ['legal_terms', 'legal_privacy', 'smartsupp_code'];
        $checkboxKeys = ['kyc_enabled','two_fa_enabled','registration_open','maintenance_mode','email_verification_enabled','payment_crypto','payment_paypal','payment_wire','invoice_wallet_payment'];
        foreach ($allowed as $key) {
            if (isset($_POST[$key])) {
                // Hidden+checkbox pattern always submits a value ('0' or '1') — use it directly
                if (in_array($key, $checkboxKeys, true)) {
                    $val = $_POST[$key] === '1' ? '1' : '0';
                } elseif (in_array($key, $rawKeys, true)) {
                    $val = trim($_POST[$key]);
                } else {
                    $val = sanitize($_POST[$key]);
                }
                $group = $keyGroups[$key] ?? 'general';
                DB::query(
                    "INSERT INTO platform_settings (setting_key, setting_value, setting_group) VALUES (?,?,?) ON DUPLICATE KEY UPDATE setting_value=?, updated_at=NOW()",
                    [$key, $val, $group, $val]
                );
            } else if (in_array($key, $checkboxKeys, true)) {
                // Unchecked checkboxes — upsert so missing rows are created too
                $cbGroup = $keyGroups[$key] ?? 'features';
                DB::query("INSERT INTO platform_settings (setting_key, setting_value, setting_group) VALUES (?,?,?) ON DUPLICATE KEY UPDATE setting_value='0', updated_at=NOW()", [$key, '0', $cbGroup]);
            }
        }

        // Logo upload
        // Logo + favicon uploads (upsert so a missing row is created, not silently skipped)
        foreach (['platform_logo' => 'branding', 'platform_favicon' => 'branding'] as $field => $group) {
            if (empty($_FILES[$field]['name'])) continue;
            $types = CONFIG['upload']['img_types'];
            if ($field === 'platform_favicon') $types = array_merge($types, ['ico', 'svg']);
            $path = upload_file($_FILES[$field], CONFIG['upload']['logo_path'], $types);
            if ($path) {
                DB::query(
                    "INSERT INTO platform_settings (setting_key, setting_value, setting_group) VALUES (?,?,?)
                     ON DUPLICATE KEY UPDATE setting_value=?, updated_at=NOW()",
                    [$field, $path, $group, $path]
                );
            }
        }

        audit_log($adminId, 'settings_updated', 'Platform settings updated', 'high', 'platform', null, 'Platform Settings');
        json_response(['success' => true, 'message' => 'Settings saved successfully.']);
    }

    // ── Announcements ──────────────────────────────────────────
    public static function sendAnnouncement(): void {
        AuthMiddleware::admin();
        AuthMiddleware::verifyCsrf();
        $adminId = current_admin_id();
        $subject = sanitize($_POST['subject'] ?? '');
        $message = sanitize($_POST['message'] ?? '');
        $target  = sanitize($_POST['target']  ?? 'all');

        if (!$subject || !$message) json_response(['success' => false, 'error' => 'Subject and message are required.']);

        $where = match ($target) {
            'active'   => "status='active'",
            'verified' => "status='active' AND kyc_status='verified'",
            default    => "1=1",
        };
        $users = DB::fetchAll("SELECT * FROM users WHERE {$where} AND email_verified=1");

        $count = 0;
        foreach ($users as $user) {
            if (Mailer::sendAnnouncement($user, $subject, $message)) $count++;
        }

        DB::query(
            "INSERT INTO announcements (subject, message, sent_by, sent_to, recipient_count) VALUES (?,?,?,?,?)",
            [$subject, $message, $adminId, $target, $count]
        );
        audit_log($adminId, 'announcement_sent', "Announcement sent to {$count} investors: {$subject}", 'medium', 'platform', null, 'All Investors');
        json_response(['success' => true, 'count' => $count, 'message' => "Announcement sent to {$count} investors."]);
    }

    // ── SMTP Test ─────────────────────────────────────────────
    public static function testSmtp(): void {
        AuthMiddleware::admin();
        AuthMiddleware::verifyCsrf();
        $adminId  = current_admin_id();
        $sendTo   = sanitize(input('email', ''));
        if (!$sendTo || !filter_var($sendTo, FILTER_VALIDATE_EMAIL)) {
            json_response(['success' => false, 'error' => 'Please enter a valid email address.']);
        }
        $pName = platform_setting('platform_name', 'NexVest');
        $body  = "<div style='font-family:sans-serif;max-width:480px;margin:0 auto;padding:32px;background:#f9fafb;border-radius:12px'>
            <h2 style='color:#111;margin-bottom:8px'>SMTP test — {$pName}</h2>
            <p style='color:#444;line-height:1.6'>This is a test email sent from <strong>{$pName}</strong> admin panel.</p>
            <p style='color:#444;line-height:1.6'>If you received this, your SMTP configuration is working correctly.</p>
            <hr style='border:none;border-top:1px solid #e5e7eb;margin:24px 0'/>
            <p style='color:#9ca3af;font-size:12px'>Sent at " . date('Y-m-d H:i:s T') . " by admin #{$adminId}</p>
          </div>";
        $ok = Mailer::sendRaw($sendTo, 'Admin', "SMTP Test — {$pName}", $body);
        if ($ok) {
            json_response(['success' => true, 'message' => "Test email sent to {$sendTo}. Check your inbox."]);
        } else {
            json_response(['success' => false, 'error' => 'Failed to send. Check your SMTP settings and server error log for details.']);
        }
    }

    // ── Email individual investor ──────────────────────────────
    public static function emailUser(): void {
        AuthMiddleware::admin();
        AuthMiddleware::verifyCsrf();
        $adminId = current_admin_id();
        $id      = (int) ($_GET['id'] ?? 0);
        $subject = sanitize(input('subject', ''));
        $message = sanitize(input('message', ''));
        if (!$id || !$subject || !$message) {
            json_response(['success' => false, 'error' => 'Subject and message are required.']);
        }
        $user = DB::fetch("SELECT * FROM users WHERE id=?", [$id]);
        if (!$user) json_response(['success' => false, 'error' => 'User not found.']);

        $pName = platform_setting('platform_name', 'NexVest');
        $body  = "<div style='font-family:sans-serif;max-width:560px;margin:0 auto'>
            <div style='background:#0B1120;padding:24px 32px;border-radius:12px 12px 0 0'>
              <div style='color:#fff;font-size:18px;font-weight:700'>{$pName}</div>
            </div>
            <div style='background:#fff;padding:28px 32px;border:1px solid #e5e7eb;border-top:none;border-radius:0 0 12px 12px'>
              <p style='color:#111;font-size:15px;margin-bottom:16px'>Dear " . htmlspecialchars($user['first_name']) . ",</p>
              <div style='color:#374151;font-size:14px;line-height:1.7'>" . nl2br(htmlspecialchars($message)) . "</div>
              <hr style='border:none;border-top:1px solid #e5e7eb;margin:24px 0'/>
              <p style='color:#9ca3af;font-size:12px'>This message was sent from {$pName} admin. Please do not reply to this email.</p>
            </div>
          </div>";
        $ok = Mailer::sendRaw($user['email'], $user['first_name'] . ' ' . $user['last_name'], $subject, $body);
        if ($ok) {
            audit_log($adminId, 'user_emailed', "Emailed investor #{$id}: {$subject}", 'medium', 'user', $id, $user['first_name'] . ' ' . $user['last_name']);
            json_response(['success' => true, 'message' => 'Email sent successfully.']);
        } else {
            json_response(['success' => false, 'error' => 'Failed to send email. Check SMTP configuration.']);
        }
    }

    // ═══ Marketing / cold outreach (to potential clients) ═════

    /** Parse a pasted blob of emails (comma / space / newline / semicolon separated) → unique valid list. */
    private static function parseRecipients(string $raw): array {
        $parts = preg_split('/[\s,;]+/', trim($raw)) ?: [];
        $out = [];
        foreach ($parts as $p) {
            $p = strtolower(trim($p));
            if ($p !== '' && filter_var($p, FILTER_VALIDATE_EMAIL)) $out[$p] = true;
        }
        return array_keys($out);
    }

    /** Normalise featured_ids input (array or CSV) → clean list of ints, order preserved, de-duped. */
    private static function marketingIds(mixed $raw): array {
        if (is_array($raw)) $ids = $raw;
        else                $ids = preg_split('/[\s,]+/', (string) $raw) ?: [];
        $out = [];
        foreach ($ids as $v) { $i = (int) $v; if ($i > 0) $out[$i] = true; }
        return array_keys($out);
    }

    /** Load investment rows for the given ids, preserving the given order. */
    private static function marketingInvestments(array $ids): array {
        if (empty($ids)) return [];
        $in   = implode(',', array_fill(0, count($ids), '?'));
        $rows = DB::fetchAll("SELECT * FROM investments WHERE id IN ($in)", $ids);
        $byId = [];
        foreach ($rows as $r) $byId[(int) $r['id']] = $r;
        $ordered = [];
        foreach ($ids as $id) if (isset($byId[$id])) $ordered[] = $byId[$id];
        return $ordered;
    }

    public static function marketing(): void {
        AuthMiddleware::admin();
        $investments = DB::fetchAll("SELECT id, name, type, roi, status FROM investments ORDER BY is_featured DESC, id DESC");
        $recent      = DB::fetchAll("SELECT * FROM marketing_campaigns ORDER BY id DESC LIMIT 8");
        $unsubCount  = (int) ((DB::fetch("SELECT COUNT(*) c FROM marketing_unsubscribes") ?? [])['c'] ?? 0);
        $adminEmail  = platform_setting('admin_notification_email', platform_setting('smtp_user', ''));
        view('admin.marketing', compact('investments','recent','unsubCount','adminEmail'), 'admin');
    }

    /** Send one preview copy to the admin's own address. */
    public static function marketingTest(): void {
        AuthMiddleware::admin();
        AuthMiddleware::verifyCsrf();
        $to       = strtolower(trim((string) input('test_email', '')));
        $subject  = sanitize(input('subject', ''));
        $headline = sanitize(input('headline', ''));
        $body     = sanitize(input('body', ''));
        $ctaLabel = sanitize(input('cta_label', ''));
        $invs     = self::marketingInvestments(self::marketingIds(input('featured_ids', [])));

        if (!$to || !filter_var($to, FILTER_VALIDATE_EMAIL)) json_response(['success' => false, 'error' => 'Enter a valid test email address.']);
        if (!$subject || !$body) json_response(['success' => false, 'error' => 'Subject and message body are required.']);

        $ok = Mailer::sendMarketing($to, '[TEST] ' . $subject, $body, $headline, $invs, $ctaLabel);
        json_response($ok
            ? ['success' => true,  'message' => "Test sent to {$to}. Check your inbox."]
            : ['success' => false, 'error' => 'Failed to send. Check SMTP settings and the server error log.']);
    }

    /** Send the campaign to the pasted list of potential clients. */
    public static function marketingSend(): void {
        AuthMiddleware::admin();
        AuthMiddleware::verifyCsrf();
        $adminId  = current_admin_id();
        $subject  = sanitize(input('subject', ''));
        $headline = sanitize(input('headline', ''));
        $body     = sanitize(input('body', ''));
        $ctaLabel = sanitize(input('cta_label', ''));
        $batch    = max(0, (int) input('batch_size', 0));   // 0 = send to everyone this run
        $featIds  = self::marketingIds(input('featured_ids', []));
        $invs     = self::marketingInvestments($featIds);
        $emails   = self::parseRecipients((string) input('recipients', ''));

        if (!$subject || !$body) json_response(['success' => false, 'error' => 'Subject and message body are required.']);
        if (empty($emails))      json_response(['success' => false, 'error' => 'No valid recipient email addresses found.']);
        if (count($emails) > 500) json_response(['success' => false, 'error' => 'Please send to at most 500 recipients per campaign.']);

        // Drop anyone on the suppression list
        $suppressed = array_column(DB::fetchAll("SELECT email FROM marketing_unsubscribes"), 'email');
        $suppressed = array_flip(array_map('strtolower', $suppressed));
        $eligible = array_values(array_filter($emails, fn($e) => !isset($suppressed[$e])));
        $skipped  = count($emails) - count($eligible);

        // Batch throttle: send at most $batch this run, hand the rest back to the UI.
        $remaining = [];
        if ($batch > 0 && count($eligible) > $batch) {
            $remaining = array_slice($eligible, $batch);
            $eligible  = array_slice($eligible, 0, $batch);
        }

        // Sending many messages over SMTP is slow — don't let the request time out,
        // and keep going even if the browser disconnects. Pace sends to protect
        // deliverability / stay under provider rate limits.
        @set_time_limit(0);
        @ignore_user_abort(true);

        $sent = 0; $failed = 0; $total = count($eligible);
        foreach ($eligible as $i => $e) {
            if (Mailer::sendMarketing($e, $subject, $body, $headline, $invs, $ctaLabel)) $sent++; else $failed++;
            if ($i < $total - 1) usleep(400000); // ~0.4s between sends
        }

        DB::query(
            "INSERT INTO marketing_campaigns (subject, headline, body, featured_ids, cta_label, recipient_count, sent_count, failed_count, sent_by)
             VALUES (?,?,?,?,?,?,?,?,?)",
            [$subject, $headline ?: null, $body, ($featIds ? implode(',', $featIds) : null), $ctaLabel ?: null, $total, $sent, $failed, $adminId]
        );
        audit_log($adminId, 'marketing_sent', "Marketing campaign '{$subject}' sent to {$sent} recipient(s)" . ($skipped ? ", {$skipped} suppressed" : '') . (count($remaining) ? ', ' . count($remaining) . ' held for next batch' : ''), 'medium', 'platform', null, 'Marketing');

        $msg = "Sent to {$sent} recipient(s).";
        if ($failed)          $msg .= " {$failed} failed.";
        if ($skipped)         $msg .= " {$skipped} skipped (unsubscribed).";
        if (count($remaining)) $msg .= " " . count($remaining) . " loaded below for the next batch.";
        json_response(['success' => true, 'message' => $msg, 'sent' => $sent, 'failed' => $failed, 'skipped' => $skipped, 'remaining' => array_values($remaining)]);
    }

    // ── Reports ────────────────────────────────────────────────
    public static function reports(): void {
        AuthMiddleware::admin();
        $stats   = DB::fetch("SELECT * FROM v_platform_stats") ?: [];
        $monthly = DB::fetchAll(
            "SELECT DATE_FORMAT(created_at,'%Y-%m') AS month,
                    SUM(CASE WHEN type='deposit'    THEN amount ELSE 0 END) AS deposits,
                    SUM(CASE WHEN type='investment' THEN amount ELSE 0 END) AS investments,
                    SUM(CASE WHEN type='return'     THEN amount ELSE 0 END) AS returns,
                    SUM(CASE WHEN type='withdrawal' THEN amount ELSE 0 END) AS withdrawals,
                    COUNT(DISTINCT user_id) AS active_users
             FROM transactions WHERE status='completed' GROUP BY month ORDER BY month DESC LIMIT 12"
        );
        view('admin.reports', compact('stats','monthly'), 'admin');
    }

    // ── Admin Invoices ────────────────────────────────────────
    public static function adminInvoices(): void {
        AuthMiddleware::admin();
        $filter = sanitize($_GET['status'] ?? 'all');
        $valid  = ['pending','paid','cancelled','all'];
        if (!in_array($filter, $valid, true)) $filter = 'all';

        $where  = $filter === 'all' ? '1' : 'ai.status = ?';
        $params = $filter === 'all' ? [] : [$filter];

        $invoices = DB::fetchAll(
            "SELECT ai.*, u.first_name, u.last_name, u.email,
                    CONCAT(a.name) AS admin_name
             FROM admin_invoices ai
             JOIN users u  ON u.id  = ai.user_id
             JOIN admins a ON a.id  = ai.admin_id
             WHERE {$where}
             ORDER BY ai.created_at DESC LIMIT 200",
            $params
        );
        $counts = DB::fetch(
            "SELECT
                SUM(status='pending')   AS pending,
                SUM(status='paid')      AS paid,
                SUM(status='cancelled') AS cancelled,
                COUNT(*)                AS total
             FROM admin_invoices"
        ) ?: [];

        view('admin.invoices', compact('invoices','filter','counts'), 'admin');
    }

    public static function issueInvoice(): void {
        AuthMiddleware::admin();
        AuthMiddleware::verifyCsrf();

        $uid    = (int) ($_POST['user_id'] ?? 0);
        $title  = sanitize($_POST['title']  ?? '');
        $desc   = trim($_POST['description'] ?? '');
        $amount = (float) ($_POST['amount']  ?? 0);
        $due    = sanitize($_POST['due_date'] ?? '');
        $method = sanitize($_POST['payment_method'] ?? 'any');

        if (!$uid || !$title || $amount <= 0 || !$due) {
            json_response(['success' => false, 'error' => 'All fields are required.']);
        }
        $validMethods = ['any','crypto','paypal','wire'];
        if (!in_array($method, $validMethods, true)) $method = 'any';

        $user = DB::fetch("SELECT id, first_name, last_name, email FROM users WHERE id=?", [$uid]);
        if (!$user) json_response(['success' => false, 'error' => 'Investor not found.']);

        $adminId = (int) ($_SESSION['admin_id'] ?? 0);
        $ref     = generate_reference('INV');

        DB::query(
            "INSERT INTO admin_invoices (user_id, admin_id, reference, title, description, amount, due_date, payment_method)
             VALUES (?,?,?,?,?,?,?,?)",
            [$uid, $adminId, $ref, $title, $desc ?: null, $amount, $due, $method]
        );

        create_notification($uid, 'invoice', 'Payment Invoice Received',
            'You have a new payment invoice: ' . $title . ' — ' . fmt_currency($amount) . '. Due ' . date('d M Y', strtotime($due)) . '.');

        try {
            $platformName = platform_setting('platform_name', 'NexVest');
            $body = "You have received a new payment invoice from {$platformName}.\n\nTitle: {$title}\nAmount: " . fmt_currency($amount) . "\nDue: " . date('d M Y', strtotime($due)) . "\n\nPlease log in to your investor portal to view and pay this invoice.\n\nReference: {$ref}";
            Mailer::sendAnnouncement($user, 'Payment Invoice: ' . $title, $body);
        } catch (\Throwable $e) {}

        try { audit_log((int)($_SESSION['admin_id']??0), 'invoice_issued', "Issued invoice {$ref} to user #{$uid} for " . fmt_currency($amount), 'medium', 'user', $uid, $user['email']); } catch (\Throwable $e) {}

        json_response(['success' => true, 'message' => 'Invoice issued successfully.', 'reference' => $ref]);
    }

    public static function cancelInvoice(): void {
        AuthMiddleware::admin();
        AuthMiddleware::verifyCsrf();

        $id      = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
        $invoice = DB::fetch("SELECT * FROM admin_invoices WHERE id=?", [$id]);
        if (!$invoice || $invoice['status'] !== 'pending') {
            json_response(['success' => false, 'error' => 'Invoice not found or cannot be cancelled.']);
        }

        DB::execute("UPDATE admin_invoices SET status='cancelled', cancelled_at=NOW() WHERE id=?", [$id]);
        try { audit_log((int)($_SESSION['admin_id']??0), 'invoice_cancelled', "Cancelled invoice #{$id} ({$invoice['reference']})", 'medium', 'invoice', $id); } catch (\Throwable $e) {}

        json_response(['success' => true, 'message' => 'Invoice cancelled.']);
    }

    // ── Private helpers ────────────────────────────────────────
    private static function parseInvestmentPost(): array {
        $bool = fn($k) => isset($_POST[$k]) && $_POST[$k] ? '1' : '0';
        $str  = fn($k, $d='') => sanitize($_POST[$k] ?? $d) ?: null;
        $num  = fn($k) => isset($_POST[$k]) && $_POST[$k] !== '' ? (float)$_POST[$k] : null;
        $int  = fn($k) => isset($_POST[$k]) && $_POST[$k] !== '' ? (int)$_POST[$k] : null;
        $date = fn($k) => isset($_POST[$k]) && $_POST[$k] ? sanitize($_POST[$k]) : null;

        $validRisk = ['low','low_medium','medium','medium_high','high'];
        $riskVal   = sanitize($_POST['risk_level'] ?? '');
        $risk      = in_array($riskVal, $validRisk, true) ? $riskVal : null;

        $validFreq = ['daily','weekly','monthly','quarterly','semi_annual','at_maturity'];
        $freqVal   = sanitize($_POST['payout_frequency'] ?? 'monthly');
        $freq      = in_array($freqVal, $validFreq, true) ? $freqVal : 'monthly';

        $validUnit = ['days','weeks','months','years'];
        $unitVal   = sanitize($_POST['duration_unit'] ?? 'months');
        $unit      = in_array($unitVal, $validUnit, true) ? $unitVal : 'months';

        $validStatus = ['active','funded','closed','coming_soon'];
        $statusVal   = sanitize($_POST['status'] ?? 'active');
        $status      = in_array($statusVal, $validStatus, true) ? $statusVal : 'active';

        $type = sanitize($_POST['type'] ?? 'real_estate');
        $type = in_array($type, ['real_estate','index_fund'], true) ? $type : 'real_estate';

        return [
            'name'             => sanitize($_POST['name'] ?? ''),
            'type'             => $type,
            'status'           => $status,
            'short_desc'       => $str('short_desc'),
            'description'      => sanitize($_POST['description'] ?? ''),
            'roi'              => (float)($_POST['roi'] ?? 0),
            'duration_value'   => (int)($_POST['duration_value'] ?? 0),
            'duration_unit'    => $unit,
            'payout_frequency' => $freq,
            'min_investment'   => (float)($_POST['min_investment'] ?? 100),
            'max_investment'   => $num('max_investment'),
            'funding_target'   => $num('funding_target'),
            'property_type'    => $str('property_type'),
            'street_address'   => $str('street_address'),
            'city'             => $str('city'),
            'state_region'     => $str('state_region'),
            'country'          => $str('country'),
            'postcode'         => $str('postcode'),
            'maps_link'        => $str('maps_link'),
            'property_size'    => $str('property_size'),
            'total_units'      => $str('total_units'),
            'occupancy_rate'   => $num('occupancy_rate'),
            'year_built'       => $int('year_built'),
            'completion_date'  => $date('completion_date'),
            'ticker'           => $str('ticker'),
            'fund_category'    => $str('fund_category'),
            'risk_level'       => $risk,
            'management_fee'   => $num('management_fee'),
            'benchmark'        => $str('benchmark'),
            'fund_start_date'  => $date('fund_start_date'),
            'fund_end_date'    => $date('fund_end_date'),
            'is_featured'      => $bool('is_featured'),
            'is_verified'      => $bool('is_verified'),
            'notify_on_launch' => $bool('notify_on_launch'),
        ];
    }

    private static function generateSlug(string $name): string {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
        return trim($slug, '-');
    }

    private static function reArrayFiles(array $files): array {
        $result = [];
        foreach ($files['name'] as $i => $name) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $result[] = ['name' => $name, 'type' => $files['type'][$i], 'tmp_name' => $files['tmp_name'][$i], 'error' => $files['error'][$i], 'size' => $files['size'][$i]];
            }
        }
        return $result;
    }

    private static function notifyInvestorsNewInvestment(int $invId, string $invName): void {
        $users = DB::fetchAll("SELECT * FROM users WHERE status='active' AND email_verified=1");
        foreach ($users as $user) {
            try { Mailer::sendAnnouncement($user, "New Investment Opportunity: {$invName}", "A new investment opportunity is now available on the platform: {$invName}. Log in to view full details and invest today."); } catch (\Throwable $e) {}
        }
    }
}
