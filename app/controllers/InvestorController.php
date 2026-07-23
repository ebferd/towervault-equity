<?php
// ============================================================
//  NexVest — Investor Controller
//  app/controllers/InvestorController.php
// ============================================================

declare(strict_types=1);

class InvestorController {

    // ── Dashboard ─────────────────────────────────────────────
    public static function dashboard(): void {
        AuthMiddleware::investor();
        $uid  = current_user_id();
        $user = DB::fetch("SELECT * FROM users WHERE id=?", [$uid]);

        $stats = [
            'balance'        => (float) $user['wallet_balance'],
            'total_invested' => (float) ((DB::fetch("SELECT COALESCE(SUM(amount),0) AS t FROM investment_holdings WHERE user_id=? AND status='active'", [$uid]) ?? [])['t'] ?? 0),
            'total_earned'   => (float) ((DB::fetch("SELECT COALESCE(SUM(total_earned),0) AS t FROM investment_holdings WHERE user_id=?", [$uid]) ?? [])['t'] ?? 0),
            'active_count'   => (int)   ((DB::fetch("SELECT COUNT(*) AS c FROM investment_holdings WHERE user_id=? AND status='active'", [$uid]) ?? [])['c'] ?? 0),
        ];

        $recent_tx      = DB::fetchAll("SELECT * FROM transactions WHERE user_id=? ORDER BY created_at DESC LIMIT 5", [$uid]);
        $notifications  = DB::fetchAll("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 5", [$uid]);
        $unread_count   = (int)((DB::fetch("SELECT COUNT(*) AS c FROM notifications WHERE user_id=? AND is_read=0", [$uid]) ?? [])['c'] ?? 0);
        $holdings       = DB::fetchAll(
            "SELECT ih.*, i.name, i.type, i.roi FROM investment_holdings ih
             JOIN investments i ON i.id=ih.investment_id
             WHERE ih.user_id=? AND ih.status='active' ORDER BY ih.created_at DESC",
            [$uid]
        );

        $chartData = self::buildEarningsSeries($uid);

        // Onboarding checklist
        $hasDeposit      = (bool) DB::fetch("SELECT id FROM transactions WHERE user_id=? AND type='deposit' AND status='completed' LIMIT 1", [$uid]);
        $hasInvestment   = (bool) DB::fetch("SELECT id FROM investment_holdings WHERE user_id=? LIMIT 1", [$uid]);
        $onboarding = [
            'email_verified'   => !empty($user['email_verified_at']),
            'kyc_verified'     => $user['kyc_status'] === 'verified',
            'funded_wallet'    => $hasDeposit,
            'first_investment' => $hasInvestment,
        ];
        $onboardingComplete = !in_array(false, $onboarding, true);

        $pendingInvoices = DB::fetchAll(
            "SELECT * FROM admin_invoices WHERE user_id=? AND status='pending' ORDER BY due_date ASC",
            [$uid]
        );

        view('investor.dashboard', compact('user','stats','recent_tx','notifications','unread_count','holdings','uid','chartData','onboarding','onboardingComplete','pendingInvoices') + ['title'=>'Dashboard']);
    }

    // Cumulative earnings (returns + referral commissions) over 7d / 30d / 1y windows.
    private static function buildEarningsSeries(int $uid): array {
        $buildDaily = function (int $days, string $labelFmt) use ($uid) {
            $rows = DB::fetchAll(
                "SELECT DATE(created_at) AS d, SUM(amount) AS amt FROM transactions
                 WHERE user_id=? AND type IN ('return','referral_commission') AND status='completed'
                   AND created_at >= DATE_SUB(CURDATE(), INTERVAL {$days} DAY)
                 GROUP BY DATE(created_at)",
                [$uid]
            );
            $byDate = [];
            foreach ($rows as $r) { $byDate[$r['d']] = (float)$r['amt']; }

            $labels = []; $data = []; $running = 0.0;
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-{$i} days"));
                $running += $byDate[$date] ?? 0;
                $labels[] = date($labelFmt, strtotime($date));
                $data[] = round($running, 2);
            }
            return ['labels' => $labels, 'data' => $data];
        };

        $buildMonthly = function () use ($uid) {
            $rows = DB::fetchAll(
                "SELECT DATE_FORMAT(created_at,'%Y-%m') AS m, SUM(amount) AS amt FROM transactions
                 WHERE user_id=? AND type IN ('return','referral_commission') AND status='completed'
                   AND created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                 GROUP BY DATE_FORMAT(created_at,'%Y-%m')",
                [$uid]
            );
            $byMonth = [];
            foreach ($rows as $r) { $byMonth[$r['m']] = (float)$r['amt']; }

            $labels = []; $data = []; $running = 0.0;
            for ($i = 11; $i >= 0; $i--) {
                $key = date('Y-m', strtotime("-{$i} months"));
                $running += $byMonth[$key] ?? 0;
                $labels[] = date('M', strtotime($key . '-01'));
                $data[] = round($running, 2);
            }
            return ['labels' => $labels, 'data' => $data];
        };

        return [
            '7d'  => $buildDaily(7, 'D'),
            '30d' => $buildDaily(30, 'M j'),
            '1y'  => $buildMonthly(),
        ];
    }

    // ── KYC ───────────────────────────────────────────────────
    public static function showKyc(): void {
        AuthMiddleware::investor();
        if (platform_setting('kyc_enabled', '1') !== '1') {
            redirect('/investor/dashboard');
        }
        $uid        = current_user_id();
        $user       = DB::fetch("SELECT * FROM users WHERE id=?", [$uid]);
        $submission = DB::fetch("SELECT * FROM kyc_submissions WHERE user_id=? ORDER BY id DESC LIMIT 1", [$uid]);
        view('investor.kyc', compact('user','submission') + ['title'=>'Identity Verification']);
    }

    public static function submitKyc(): void {
        AuthMiddleware::investor();
        AuthMiddleware::verifyCsrf();
        $uid  = current_user_id();
        $user = DB::fetch("SELECT * FROM users WHERE id=?", [$uid]);

        if ($user['kyc_status'] === 'verified') {
            json_response(['success' => false, 'error' => 'Your identity is already verified.']);
        }

        $fullName = sanitize($_POST['full_name']    ?? '');
        $dob      = sanitize($_POST['date_of_birth'] ?? '');
        $idType   = sanitize($_POST['id_type']       ?? '');

        if (!$fullName || !$dob || !$idType) {
            json_response(['success' => false, 'error' => 'All fields are required.']);
        }

        $validTypes = ['passport', 'national_id', 'drivers_license'];
        if (!in_array($idType, $validTypes, true)) {
            json_response(['success' => false, 'error' => 'Invalid document type.']);
        }

        $docTypes = CONFIG['upload']['doc_types'];
        $paths    = [];

        foreach (['doc_front' => 'ID Front', 'doc_back' => 'ID Back'] as $field => $label) {
            if (empty($_FILES[$field]['name'])) {
                json_response(['success' => false, 'error' => "{$label} document is required."]);
            }
            $path = upload_file($_FILES[$field], CONFIG['upload']['kyc_path'], $docTypes);
            if (!$path) {
                json_response(['success' => false, 'error' => "{$label}: Invalid file. Please upload a JPG, PNG or PDF under 5MB."]);
            }
            $paths[$field] = $path;
        }

        DB::beginTransaction();
        try {
            // Supersede previous submissions so only the new one is pending
            DB::execute("UPDATE kyc_submissions SET status='superseded' WHERE user_id=? AND status IN ('pending','rejected')", [$uid]);

            $ref = 'KYC-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
            DB::query(
                "INSERT INTO kyc_submissions (user_id, full_legal_name, date_of_birth, id_type, doc_front, doc_back, status)
                 VALUES (?,?,?,?,?,?,'pending')",
                [$uid, $fullName, $dob, $idType, $paths['doc_front'], $paths['doc_back']]
            );
            DB::execute("UPDATE users SET kyc_status='pending' WHERE id=?", [$uid]);
            $_SESSION['kyc_status'] = 'pending';

            DB::commit();
            try { Mailer::sendKycSubmitted($user, $ref); } catch (\Throwable $e) {}
            try { Mailer::notifyAdminKycSubmission($user, $ref); } catch (\Throwable $e) {}
            create_notification($uid, 'kyc', 'KYC Submitted', 'Your identity documents have been received and are under review.');
            json_response(['success' => true, 'redirect' => '/investor/dashboard']);
        } catch (Exception $e) {
            DB::rollback();
            error_log('KYC submit error: ' . $e->getMessage());
            json_response(['success' => false, 'error' => 'Submission failed. Please try again.']);
        }
    }

    // ── 2FA Setup ──────────────────────────────────────────────
    public static function show2FASetup(): void {
        AuthMiddleware::investor();
        $uid  = current_user_id();
        $user = DB::fetch("SELECT * FROM users WHERE id=?", [$uid]);

        // Generate secret if not set
        if (empty($user['two_fa_secret'])) {
            $b32 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
            $secret = '';
            for ($i = 0; $i < 16; $i++) { $secret .= $b32[random_int(0, 31)]; }
            unset($b32, $i);
            DB::execute("UPDATE users SET two_fa_secret=? WHERE id=?", [$secret, $uid]);
            $user['two_fa_secret'] = $secret;
        }

        $pName = platform_setting('platform_name', 'NexVest');
        $qrUrl = 'otpauth://totp/' . rawurlencode($pName . ':' . $user['email'])
               . '?secret=' . $user['two_fa_secret']
               . '&issuer=' . rawurlencode($pName);

        // Generate QR code server-side as inline SVG (no CDN needed)
        $qrSvg = '';
        try {
            $renderer = new \BaconQrCode\Renderer\ImageRenderer(
                new \BaconQrCode\Renderer\RendererStyle\RendererStyle(200),
                new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
            );
            $qrSvg = (new \BaconQrCode\Writer($renderer))->writeString($qrUrl);
        } catch (\Throwable $e) {
            error_log('QR generation failed: ' . $e->getMessage());
        }
        view('investor.setup_2fa', compact('user','qrUrl','qrSvg') + ['title'=>'Set Up 2FA']);
    }

    public static function enable2FA(): void {
        AuthMiddleware::investor();
        AuthMiddleware::verifyCsrf();
        $uid    = current_user_id();
        $code   = trim($_POST['code'] ?? '');
        $action = sanitize($_POST['action'] ?? 'enable');

        $user = DB::fetch("SELECT * FROM users WHERE id=?", [$uid]);

        if ($action === 'disable') {
            DB::execute("UPDATE users SET two_fa_enabled=0, two_fa_secret=NULL WHERE id=?", [$uid]);
            json_response(['success' => true, 'message' => '2FA has been disabled.']);
        }

        if (strlen($code) !== 6 || !ctype_digit($code)) {
            json_response(['success' => false, 'error' => 'Please enter a valid 6-digit code.']);
        }

        if (!AuthController::verifyTotpPublic($user['two_fa_secret'], $code)) {
            json_response(['success' => false, 'error' => 'Invalid code. Please try again.']);
        }

        DB::execute("UPDATE users SET two_fa_enabled=1 WHERE id=?", [$uid]);
        try { Mailer::sendTwoFAEnabled($user); } catch (\Throwable $e) {}
        json_response(['success' => true, 'redirect' => '/investor/kyc']);
    }

    // ── Investments ────────────────────────────────────────────
    public static function investments(): void {
        AuthMiddleware::investor();
        $uid        = current_user_id();
        $type       = sanitize($_GET['type'] ?? 'all');
        $mine = !empty($_GET['mine']);

        $where = [];
        $params = [];

        if ($type !== 'all') {
            $where[] = "i.type = ?";
            $params[] = $type;
        }

        if ($mine) {
            $where[] = "EXISTS (SELECT 1 FROM investment_holdings ih WHERE ih.investment_id=i.id AND ih.user_id=? AND ih.status='active')";
            $params[] = $uid;
        } else {
            $where[] = "i.status IN ('active','funded')";
        }

        $sql   = "SELECT i.*, (SELECT SUM(ih.amount) FROM investment_holdings ih WHERE ih.investment_id=i.id AND ih.user_id=?) AS my_invested
                  FROM investments i"
               . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
               . " ORDER BY i.is_featured DESC, i.created_at DESC";

        $investments = DB::fetchAll($sql, array_merge([$uid], $params));
        view('investor.investments', compact('investments','type','mine') + ['title'=>'Investments']);
    }

    public static function investmentDetail(): void {
        AuthMiddleware::investor();
        $uid = current_user_id();
        $id  = (int) ($_GET['id'] ?? 0);
        if (!$id) redirect('/investor/investments');

        $investment = DB::fetch("SELECT * FROM investments WHERE id=? AND status != 'closed'", [$id]);
        if (!$investment) { http_response_code(404); view('errors.404', ['title' => 'Not Found'], 'minimal'); return; }

        $docs     = DB::fetchAll("SELECT * FROM investment_documents WHERE investment_id=?", [$id]);
        $holdings = DB::fetchAll("SELECT * FROM investment_holdings WHERE investment_id=? AND user_id=?", [$id, $uid]);
        $holding  = $holdings[0] ?? null;

        if ($investment['type'] === 'index_fund') {
            $fundHoldings = DB::fetchAll("SELECT * FROM fund_holdings WHERE investment_id=? ORDER BY sort_order", [$id]);
        } else {
            $fundHoldings = [];
        }

        $user = DB::fetch("SELECT wallet_balance, min_investment_override, min_investment_note FROM users WHERE id=?", [$uid]);
        $walletBalance = (float)($user['wallet_balance'] ?? 0);

        // Effective minimum: the higher of the plan minimum and any per-user override
        $userMinOverride = ($user && $user['min_investment_override'] !== null) ? (float) $user['min_investment_override'] : null;
        $userMinNote     = trim((string)($user['min_investment_note'] ?? ''));
        $effectiveMin    = max((float)$investment['min_investment'], $userMinOverride ?? 0);

        view('investor.investment_detail', compact('investment','docs','holding','fundHoldings','walletBalance','userMinOverride','userMinNote','effectiveMin') + ['title'=>$investment['name']]);
    }

    public static function invest(): void {
        AuthMiddleware::investor();
        AuthMiddleware::verifyCsrf();

        $uid          = current_user_id();
        $investmentId = (int) ($_POST['investment_id'] ?? 0);
        $amount       = (float) ($_POST['amount'] ?? 0);
        $method       = sanitize($_POST['method'] ?? '');

        if (!$investmentId || $amount <= 0 || !$method) {
            json_response(['success' => false, 'error' => 'Invalid investment details.']);
        }

        $validMethods = ['crypto', 'paypal', 'wire', 'wallet'];
        if (!in_array($method, $validMethods, true)) {
            json_response(['success' => false, 'error' => 'Invalid payment method.']);
        }

        // Check method enabled (wallet is always available)
        if ($method !== 'wallet' && platform_setting("payment_{$method}", '1') !== '1') {
            json_response(['success' => false, 'error' => 'This payment method is currently unavailable.']);
        }

        $investment = DB::fetch("SELECT * FROM investments WHERE id=? AND status='active'", [$investmentId]);
        if (!$investment) json_response(['success' => false, 'error' => 'Investment not found or no longer accepting funds.']);

        // Per-user minimum override takes precedence when higher than the plan minimum
        $me         = DB::fetch("SELECT min_investment_override, min_investment_note FROM users WHERE id=?", [$uid]);
        $userMin    = ($me && $me['min_investment_override'] !== null) ? (float) $me['min_investment_override'] : null;
        if ($userMin !== null && $amount < $userMin) {
            $note = trim((string)($me['min_investment_note'] ?? ''));
            $msg  = 'Your account has a minimum investment of ' . fmt_currency($userMin) . '.';
            if ($note !== '') $msg .= ' ' . $note;
            json_response(['success' => false, 'error' => $msg]);
        }

        if ($amount < (float) $investment['min_investment']) {
            json_response(['success' => false, 'error' => 'Amount is below the minimum investment of ' . fmt_currency((float)$investment['min_investment']) . '.']);
        }

        if ($investment['max_investment'] && $amount > (float) $investment['max_investment']) {
            json_response(['success' => false, 'error' => 'Amount exceeds the maximum investment of ' . fmt_currency((float)$investment['max_investment']) . '.']);
        }

        // Check funding target not exceeded
        if ($investment['funding_target']) {
            $remaining = (float)$investment['funding_target'] - (float)$investment['funding_raised'];
            if ($amount > $remaining) {
                json_response(['success' => false, 'error' => 'Amount exceeds remaining funding capacity of ' . fmt_currency($remaining) . '.']);
            }
        }

        $user = DB::fetch("SELECT * FROM users WHERE id=?", [$uid]);

        // Wallet payment: instant activation
        if ($method === 'wallet') {
            if ((float)$user['wallet_balance'] < $amount) {
                json_response(['success' => false, 'error' => 'Insufficient wallet balance. Available: ' . fmt_currency((float)$user['wallet_balance']) . '.']);
            }
            DB::beginTransaction();
            try {
                $certRef = generate_cert_ref();
                while (DB::fetch("SELECT id FROM investment_holdings WHERE certificate_ref=?", [$certRef])) {
                    $certRef = generate_cert_ref();
                }
                $startDate = date('Y-m-d');
                $endDate   = calc_maturity_date($startDate, (int)$investment['duration_value'], $investment['duration_unit']);

                $holdingId = (int) DB::insert(
                    "INSERT INTO investment_holdings (user_id, investment_id, amount, payment_method, status, start_date, end_date, roi, certificate_ref)
                     VALUES (?,?,?,?,'active',?,?,?,?)",
                    [$uid, $investmentId, $amount, 'wallet', $startDate, $endDate, $investment['roi'], $certRef]
                );
                $balBefore = (float)$user['wallet_balance'];
                DB::execute("UPDATE users SET wallet_balance = wallet_balance - ? WHERE id=?", [$amount, $uid]);
                DB::execute("UPDATE investments SET funding_raised = funding_raised + ? WHERE id=?", [$amount, $investmentId]);
                $ref = generate_reference('NV');
                DB::query(
                    "INSERT INTO transactions (user_id, type, amount, balance_before, balance_after, status, method, reference, description)
                     VALUES (?,?,?,?,?,'completed','wallet',?,?)",
                    [$uid, 'investment', $amount, $balBefore, $balBefore - $amount, $ref, 'Investment: ' . $investment['name']]
                );
                // Referral commission on wallet-paid investments
                $referral = DB::fetch(
                    "SELECT r.*, u2.wallet_balance AS ref_bal FROM referrals r JOIN users u2 ON u2.id=r.referrer_id WHERE r.referred_id=?",
                    [$uid]
                );
                if ($referral) {
                    $commRate   = (float) platform_setting('referral_commission', '5');
                    $commission = round($amount * $commRate / 100, 2);
                    if ($commission > 0) {
                        $commRef = generate_reference('COM');
                        $rBefore = (float)$referral['ref_bal'];
                        $rAfter  = $rBefore + $commission;
                        DB::execute("UPDATE users SET wallet_balance=? WHERE id=?", [$rAfter, $referral['referrer_id']]);
                        DB::query(
                            "INSERT INTO transactions (user_id,type,amount,balance_before,balance_after,status,reference,description)
                             VALUES (?,'referral_commission',?,?,?,'completed',?,?)",
                            [$referral['referrer_id'], $commission, $rBefore, $rAfter, $commRef, 'Referral commission for investor #' . $uid]
                        );
                        DB::execute("UPDATE referrals SET commission_amount = commission_amount + ?, status='commission_paid' WHERE id=?", [$commission, $referral['id']]);
                        create_notification($referral['referrer_id'], 'referral', 'Referral Commission Received', fmt_currency($commission) . ' commission credited for your referral.');
                        $referrer = DB::fetch("SELECT * FROM users WHERE id=?", [$referral['referrer_id']]);
                        try { Mailer::sendReferralCommission($referrer, $user, $commission); } catch (\Throwable $e) {}
                    }
                }

                DB::commit();
                create_notification($uid, 'investment', 'Investment Activated', 'Your investment of ' . fmt_currency($amount) . ' in ' . $investment['name'] . ' is now active.');
                json_response(['success' => true, 'wallet_paid' => true, 'redirect' => '/investor/portfolio']);
            } catch (Exception $e) {
                DB::rollback();
                error_log('Wallet invest error: ' . $e->getMessage());
                json_response(['success' => false, 'error' => 'Failed to process investment. Please try again.']);
            }
        }

        DB::beginTransaction();
        try {
            $certRef   = generate_cert_ref();
            while (DB::fetch("SELECT id FROM investment_holdings WHERE certificate_ref=?", [$certRef])) {
                $certRef = generate_cert_ref();
            }

            $startDate = date('Y-m-d');
            $endDate   = calc_maturity_date($startDate, (int)$investment['duration_value'], $investment['duration_unit']);

            // Create holding as pending until payment confirmed
            $holdingId = (int) DB::insert(
                "INSERT INTO investment_holdings (user_id, investment_id, amount, payment_method, status, start_date, end_date, roi, certificate_ref)
                 VALUES (?,?,?,?,'pending',?,?,?,?)",
                [$uid, $investmentId, $amount, $method, $startDate, $endDate, $investment['roi'], $certRef]
            );

            // Create deposit invoice
            $invoiceRef = generate_reference('NV');
            $timeout    = (int) platform_setting('deposit_timeout', '1800');
            $expiry     = date('Y-m-d H:i:s', time() + $timeout);

            DB::query(
                "INSERT INTO deposit_invoices (user_id, holding_id, reference, amount, method, status, expires_at) VALUES (?,?,?,?,?,'pending',?)",
                [$uid, $holdingId, $invoiceRef, $amount, $method, $expiry]
            );

            DB::commit();
            json_response([
                'success'     => true,
                'invoice_ref' => $invoiceRef,
                'holding_id'  => $holdingId,
                'expires_at'  => $expiry,
                'method'      => $method,
                'amount'      => $amount,
            ]);
        } catch (Exception $e) {
            DB::rollback();
            error_log('Invest error: ' . $e->getMessage());
            json_response(['success' => false, 'error' => 'Failed to create investment. Please try again.']);
        }
    }

    // ── Portfolio ──────────────────────────────────────────────
    public static function portfolio(): void {
        AuthMiddleware::investor();
        $uid      = current_user_id();
        $holdings = DB::fetchAll(
            "SELECT ih.*, i.name, i.type, i.roi AS inv_roi, i.duration_value, i.duration_unit, i.payout_frequency, i.min_investment
             FROM investment_holdings ih
             JOIN investments i ON i.id = ih.investment_id
             WHERE ih.user_id = ?
             ORDER BY ih.status, ih.created_at DESC",
            [$uid]
        );
        $total_invested = array_sum(array_column(array_filter($holdings, fn($h) => $h['status']==='active'), 'amount'));
        $total_earned   = array_sum(array_column($holdings, 'total_earned'));
        view('investor.portfolio', compact('holdings','total_invested','total_earned') + ['title'=>'My Portfolio']);
    }

    // ── Wallet ─────────────────────────────────────────────────
    public static function wallet(): void {
        AuthMiddleware::investor();
        $uid  = current_user_id();
        $user = DB::fetch("SELECT * FROM users WHERE id=?", [$uid]);
        $page = max(1, (int)($_GET['page'] ?? 1));
        $filter = sanitize($_GET['filter'] ?? $_GET['type'] ?? 'all');

        $where  = "user_id = ?";
        $params = [$uid];
        if ($filter !== 'all') { $where .= " AND type = ?"; $params[] = $filter; }

        $result = paginate(
            "SELECT COUNT(*) AS total FROM transactions WHERE {$where}",
            "SELECT * FROM transactions WHERE {$where} ORDER BY created_at DESC",
            $params, $page, 15
        );

        $totals = DB::fetch(
            "SELECT
                COALESCE(SUM(CASE WHEN type='deposit'    AND status='completed' THEN amount ELSE 0 END),0) AS total_deposited,
                COALESCE(SUM(CASE WHEN type='return'     AND status='completed' THEN amount ELSE 0 END),0) AS total_returns,
                COALESCE(SUM(CASE WHEN type='withdrawal' AND status='completed' THEN amount ELSE 0 END),0) AS total_withdrawn
             FROM transactions WHERE user_id=?",
            [$uid]
        );

        view('investor.wallet', array_merge(['title'=>'Wallet'], compact('user','filter'), $result, is_array($totals) ? $totals : []));
    }

    public static function initiateDeposit(): void {
        AuthMiddleware::investor();
        AuthMiddleware::verifyCsrf();
        $uid    = current_user_id();
        $amount = (float) ($_POST['amount'] ?? 0);
        $method = sanitize($_POST['method'] ?? '');
        $coin   = sanitize($_POST['coin'] ?? '');

        $minDeposit = (float) platform_setting('min_deposit', '100');
        if ($amount < $minDeposit) json_response(['success' => false, 'error' => 'Minimum deposit is ' . fmt_currency($minDeposit) . '.']);
        if (!in_array($method, ['crypto','paypal','wire'], true)) {
            json_response(['success' => false, 'error' => 'Invalid payment method.']);
        }
        if (platform_setting("payment_{$method}", '1') !== '1') {
            json_response(['success' => false, 'error' => 'This payment method is not enabled.']);
        }

        $timeout   = (int) platform_setting('deposit_timeout', '1800');
        $ref       = generate_reference('NV');
        $expiresAt = date('Y-m-d H:i:s', time() + $timeout);

        // Crypto wallet addresses — configured by admin in Settings
        $wallets = [
            'btc'  => platform_setting('crypto_btc_address',  ''),
            'eth'  => platform_setting('crypto_eth_address',  ''),
            'usdt' => platform_setting('crypto_usdt_address', ''),
            'usdc' => platform_setting('crypto_usdc_address', ''),
        ];
        if ($method === 'crypto') {
            if (!$coin || !isset($wallets[$coin])) {
                json_response(['success' => false, 'error' => 'Please select a valid cryptocurrency.']);
            }
            if (empty($wallets[$coin])) {
                json_response(['success' => false, 'error' => 'Crypto deposits are not configured yet. Please use a different method or contact support.']);
            }
        }
        $walletAddr = $method === 'crypto' ? $wallets[$coin] : null;

        DB::query(
            "INSERT INTO deposit_invoices (user_id, reference, amount, method, coin, wallet_address, status, expires_at) VALUES (?,?,?,?,?,?,'pending',?)",
            [$uid, $ref, $amount, $method, $coin ?: null, $walletAddr, $expiresAt]
        );

        json_response(['success' => true, 'reference' => $ref, 'expires_at' => $expiresAt, 'wallet_address' => $walletAddr, 'timeout' => $timeout]);
    }

    public static function confirmDeposit(): void {
        AuthMiddleware::investor();
        AuthMiddleware::verifyCsrf();
        $uid = current_user_id();
        $ref = sanitize($_POST['reference'] ?? '');

        $invoice = DB::fetch("SELECT * FROM deposit_invoices WHERE reference=? AND user_id=? AND status='pending'", [$ref, $uid]);
        if (!$invoice) json_response(['success' => false, 'error' => 'Invoice not found or already processed.']);

        if (strtotime($invoice['expires_at']) < time()) {
            DB::execute("UPDATE deposit_invoices SET status='expired' WHERE id=?", [$invoice['id']]);
            json_response(['success' => false, 'error' => 'This invoice has expired. Please start a new deposit.']);
        }

        DB::execute(
            "UPDATE deposit_invoices SET status='submitted' WHERE id=?",
            [$invoice['id']]
        );

        $user = DB::fetch("SELECT * FROM users WHERE id=?", [$uid]);
        create_notification($uid, 'deposit', 'Deposit Submitted', 'Your deposit of ' . fmt_currency((float)$invoice['amount']) . ' is under review. Reference: ' . $ref);
        try { Mailer::sendDepositSubmitted($user, $invoice); } catch (\Throwable $e) {}
        try { Mailer::notifyAdminDeposit($user, $invoice); } catch (\Throwable $e) {}

        json_response(['success' => true, 'message' => 'Payment submitted for review. You\'ll receive an email once confirmed.']);
    }

    // ── Look up a recipient by email (AJAX) ───────────────────
    public static function lookupTransferRecipient(): void {
        AuthMiddleware::investor();
        $uid   = current_user_id();
        $email = sanitize($_GET['email'] ?? '');
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            json_response(['success' => false, 'error' => 'Enter a valid email address.']);
        }
        $recipient = DB::fetch(
            "SELECT id, first_name, last_name, email FROM users WHERE email=? AND id!=? AND status='active'",
            [$email, $uid]
        );
        if (!$recipient) {
            json_response(['success' => false, 'error' => 'No active investor found with that email address.']);
        }
        json_response([
            'success' => true,
            'id'      => $recipient['id'],
            'name'    => $recipient['first_name'] . ' ' . $recipient['last_name'],
            'initials'=> strtoupper(substr($recipient['first_name'],0,1) . substr($recipient['last_name'],0,1)),
        ]);
    }

    // ── Execute peer-to-peer wallet transfer ──────────────────
    public static function walletTransfer(): void {
        AuthMiddleware::investor();
        AuthMiddleware::verifyCsrf();
        $uid         = current_user_id();
        $receiverId  = (int) ($_POST['receiver_id'] ?? 0);
        $amount      = (float) ($_POST['amount'] ?? 0);
        $note        = sanitize($_POST['note'] ?? '');

        if (!$receiverId || $amount <= 0) {
            json_response(['success' => false, 'error' => 'Invalid transfer details.']);
        }
        if ($receiverId === $uid) {
            json_response(['success' => false, 'error' => 'You cannot transfer to yourself.']);
        }

        $minTransfer = (float) platform_setting('min_transfer', '1');
        if ($amount < $minTransfer) {
            json_response(['success' => false, 'error' => 'Minimum transfer amount is ' . fmt_currency($minTransfer) . '.']);
        }

        $sender = DB::fetch("SELECT id, first_name, last_name, email, wallet_balance FROM users WHERE id=?", [$uid]);
        if ((float)$sender['wallet_balance'] < $amount) {
            json_response(['success' => false, 'error' => 'Insufficient wallet balance. Available: ' . fmt_currency((float)$sender['wallet_balance']) . '.']);
        }

        $receiver = DB::fetch("SELECT id, first_name, last_name, email, wallet_balance FROM users WHERE id=? AND status='active'", [$receiverId]);
        if (!$receiver) {
            json_response(['success' => false, 'error' => 'Recipient account not found or inactive.']);
        }

        $ref         = generate_reference('TRF');
        $senderRef   = $ref . '-S';
        $receiverRef = $ref . '-R';

        DB::beginTransaction();
        try {
            $senderBefore   = (float) $sender['wallet_balance'];
            $senderAfter    = $senderBefore - $amount;
            $receiverBefore = (float) $receiver['wallet_balance'];
            $receiverAfter  = $receiverBefore + $amount;

            // Debit sender
            DB::execute(
                "UPDATE users SET wallet_balance = wallet_balance - ? WHERE id = ? AND wallet_balance >= ?",
                [$amount, $uid, $amount]
            );
            $senderTxId = DB::insert(
                "INSERT INTO transactions (user_id, type, amount, balance_before, balance_after, status, reference, description)
                 VALUES (?,?,?,?,?,'completed',?,?)",
                [$uid, 'transfer_sent', $amount, $senderBefore, $senderAfter, $senderRef,
                 'Transfer to ' . $receiver['first_name'] . ' ' . $receiver['last_name'] . ($note ? ' — ' . $note : '')]
            );

            // Credit receiver
            DB::execute("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?", [$amount, $receiverId]);
            $receiverTxId = DB::insert(
                "INSERT INTO transactions (user_id, type, amount, balance_before, balance_after, status, reference, description)
                 VALUES (?,?,?,?,?,'completed',?,?)",
                [$receiverId, 'transfer_received', $amount, $receiverBefore, $receiverAfter, $receiverRef,
                 'Transfer from ' . $sender['first_name'] . ' ' . $sender['last_name'] . ($note ? ' — ' . $note : '')]
            );

            // Record in wallet_transfers
            DB::insert(
                "INSERT INTO wallet_transfers (reference, sender_id, receiver_id, amount, note, sender_tx_id, receiver_tx_id)
                 VALUES (?,?,?,?,?,?,?)",
                [$ref, $uid, $receiverId, $amount, $note ?: null, $senderTxId, $receiverTxId]
            );

            DB::commit();

            // Notify receiver
            $receiverNew = (float) DB::fetch("SELECT wallet_balance FROM users WHERE id=?", [$receiverId])['wallet_balance'];
            create_notification(
                $receiverId, 'transfer_received',
                'Transfer received — ' . fmt_currency($amount),
                $sender['first_name'] . ' ' . substr($sender['last_name'],0,1) . '. sent you ' . fmt_currency($amount) . '. Funds added to your wallet.'
            );
            try { Mailer::sendTransferReceived($receiver, $sender, $amount, $ref, $note, $receiverNew); } catch (\Throwable $e) { error_log('Transfer mailer error: ' . $e->getMessage()); }

            json_response([
                'success'      => true,
                'reference'    => $ref,
                'amount'       => $amount,
                'receiver_name'=> $receiver['first_name'] . ' ' . $receiver['last_name'],
                'receiver_initials' => strtoupper(substr($receiver['first_name'],0,1).substr($receiver['last_name'],0,1)),
                'new_balance'  => $senderAfter,
                'timestamp'    => date('M j, Y · g:i A'),
            ]);

        } catch (\Throwable $e) {
            DB::rollback();
            error_log('Transfer error: ' . $e->getMessage());
            json_response(['success' => false, 'error' => 'Transfer failed. Please try again.']);
        }
    }

    public static function requestWithdrawal(): void {
        AuthMiddleware::investor();
        AuthMiddleware::verifyCsrf();
        $uid    = current_user_id();
        $amount = (float) ($_POST['amount'] ?? 0);
        $method = sanitize($_POST['method'] ?? '');

        $minWithdrawal = (float) platform_setting('min_withdrawal', '50');
        if ($amount < $minWithdrawal) json_response(['success' => false, 'error' => 'Minimum withdrawal is ' . fmt_currency($minWithdrawal) . '.']);

        $user = DB::fetch("SELECT * FROM users WHERE id=?", [$uid]);
        if (!empty($user['withdrawals_disabled'])) {
            json_response(['success' => false, 'error' => 'Withdrawals are currently disabled on your account. Please contact support for assistance.']);
        }
        if ($amount > (float) $user['wallet_balance']) {
            json_response(['success' => false, 'error' => 'Insufficient wallet balance.']);
        }

        if (!in_array($method, ['crypto','paypal','wire'], true)) {
            json_response(['success' => false, 'error' => 'Invalid withdrawal method.']);
        }
        if (platform_setting("payment_{$method}", '1') !== '1') {
            json_response(['success' => false, 'error' => 'This withdrawal method is currently unavailable.']);
        }

        // Collect method-specific details
        $details = match ($method) {
            'wire'   => ['bank_name' => sanitize($_POST['bank_name'] ?? ''), 'account_name' => sanitize($_POST['account_name'] ?? ''), 'account_number' => sanitize($_POST['account_number'] ?? ''), 'routing' => sanitize($_POST['routing'] ?? ''), 'bank_address' => sanitize($_POST['bank_address'] ?? '')],
            'crypto' => ['coin' => sanitize($_POST['coin'] ?? ''), 'wallet_address' => sanitize($_POST['wallet_address'] ?? ''), 'memo' => sanitize($_POST['memo'] ?? '')],
            'paypal' => ['paypal_email' => sanitize_email($_POST['paypal_email'] ?? '')],
            default  => [],
        };

        DB::beginTransaction();
        try {
            $ref = generate_reference('WD');
            DB::query(
                "INSERT INTO withdrawal_requests (user_id, reference, amount, method, details) VALUES (?,?,?,?,?)",
                [$uid, $ref, $amount, $method, json_encode($details)]
            );
            // Reserve funds
            DB::execute("UPDATE users SET wallet_balance = wallet_balance - ? WHERE id=?", [$amount, $uid]);

            // Record transaction as pending
            $bal = (float)$user['wallet_balance'] - $amount;
            DB::query(
                "INSERT INTO transactions (user_id, type, amount, balance_before, balance_after, status, method, reference, description)
                 VALUES (?,?,?,?,?,'pending',?,?,?)",
                [$uid, 'withdrawal', $amount, (float)$user['wallet_balance'], $bal, $method, $ref, 'Withdrawal request']
            );

            DB::commit();
            create_notification($uid, 'withdrawal', 'Withdrawal Requested', "Your withdrawal of " . fmt_currency($amount) . " is under review.");
            register_shutdown_function(function() use ($user, $ref, $amount, $method) {
                try { Mailer::sendWithdrawalSubmitted($user, ['reference' => $ref, 'amount' => $amount, 'method' => $method]); } catch (\Throwable $e) {}
                try { Mailer::notifyAdminWithdrawal($user, ['reference' => $ref, 'amount' => $amount, 'method' => $method]); } catch (\Throwable $e) {}
            });
            json_response(['success' => true, 'reference' => $ref]);
        } catch (Exception $e) {
            DB::rollback();
            json_response(['success' => false, 'error' => 'Failed to process withdrawal request.']);
        }
    }

    // ── Transactions ───────────────────────────────────────────
    public static function transactions(): void {
        AuthMiddleware::investor();
        $uid    = current_user_id();
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $filter = sanitize($_GET['type'] ?? 'all');

        $where  = "user_id = ?";
        $params = [$uid];
        if ($filter !== 'all') { $where .= " AND type = ?"; $params[] = $filter; }

        $result = paginate(
            "SELECT COUNT(*) AS total FROM transactions WHERE {$where}",
            "SELECT * FROM transactions WHERE {$where} ORDER BY created_at DESC",
            $params, $page, 20
        );
        view('investor.transactions', array_merge(['title'=>'Transactions'], compact('filter'), $result));
    }

    // ── Notifications ──────────────────────────────────────────
    public static function notifications(): void {
        AuthMiddleware::investor();
        $uid  = current_user_id();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $result = paginate(
            "SELECT COUNT(*) AS total FROM notifications WHERE user_id=?",
            "SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC",
            [$uid], $page, 20
        );
        view('investor.notifications', $result + ['title'=>'Notifications']);
    }

    public static function markNotificationsRead(): void {
        AuthMiddleware::investor();
        AuthMiddleware::verifyCsrf();
        $uid = current_user_id();
        $id  = (int) input('id', 0);
        if ($id) {
            DB::execute("UPDATE notifications SET is_read=1, read_at=NOW() WHERE id=? AND user_id=?", [$id, $uid]);
        } else {
            DB::execute("UPDATE notifications SET is_read=1, read_at=NOW() WHERE user_id=? AND is_read=0", [$uid]);
        }
        json_response(['success' => true]);
    }

    // ── Support ────────────────────────────────────────────────
    public static function support(): void {
        AuthMiddleware::investor();
        $uid     = current_user_id();
        $tickets = DB::fetchAll(
            "SELECT st.*, (SELECT COUNT(*) FROM ticket_messages WHERE ticket_id=st.id) AS msg_count
             FROM support_tickets st WHERE st.user_id=? ORDER BY st.updated_at DESC",
            [$uid]
        );
        // Pre-load messages for all tickets to avoid N+1 queries in the view
        if ($tickets) {
            $ticketIds   = array_column($tickets, 'id');
            $placeholders = implode(',', array_fill(0, count($ticketIds), '?'));
            $allMessages = DB::fetchAll("SELECT * FROM ticket_messages WHERE ticket_id IN ({$placeholders}) ORDER BY created_at", $ticketIds);
            $msgsByTicket = [];
            foreach ($allMessages as $m) { $msgsByTicket[$m['ticket_id']][] = $m; }
            foreach ($tickets as &$t) { $t['messages'] = $msgsByTicket[$t['id']] ?? []; }
            unset($t);
        }
        $active = null;
        if (isset($_GET['ticket'])) {
            $ticketId = (int) $_GET['ticket'];
            $active   = DB::fetch("SELECT * FROM support_tickets WHERE id=? AND user_id=?", [$ticketId, $uid]);
            if ($active) {
                $active['messages'] = DB::fetchAll("SELECT * FROM ticket_messages WHERE ticket_id=? ORDER BY created_at", [$ticketId]);
            }
        }
        view('investor.support', compact('tickets','active') + ['title'=>'Support']);
    }

    public static function createTicket(): void {
        AuthMiddleware::investor();
        AuthMiddleware::verifyCsrf();
        $uid     = current_user_id();
        $subject = sanitize($_POST['subject'] ?? '');
        $message = sanitize($_POST['message'] ?? '');

        if (!$subject || !$message) {
            json_response(['success' => false, 'error' => 'Subject and message are required.']);
        }

        DB::beginTransaction();
        try {
            $ref = 'TKT-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
            $ticketId = (int) DB::insert(
                "INSERT INTO support_tickets (user_id, reference, subject) VALUES (?,?,?)",
                [$uid, $ref, $subject]
            );
            DB::query(
                "INSERT INTO ticket_messages (ticket_id, sender_type, sender_id, message) VALUES (?,'user',?,?)",
                [$ticketId, $uid, $message]
            );
            DB::commit();

            $user = DB::fetch("SELECT * FROM users WHERE id=?", [$uid]);
            try { Mailer::sendTicketReceived($user, ['reference' => $ref, 'subject' => $subject]); } catch (\Throwable $e) {}
            try { Mailer::notifyAdminTicket($user, ['id' => $ticketId, 'reference' => $ref, 'subject' => $subject, 'priority' => 'normal']); } catch (\Throwable $e) {}
            json_response(['success' => true, 'ticket_id' => $ticketId, 'reference' => $ref]);
        } catch (Exception $e) {
            DB::rollback();
            json_response(['success' => false, 'error' => 'Failed to create ticket.']);
        }
    }

    public static function wireRequest(): void {
        AuthMiddleware::investor();
        AuthMiddleware::verifyCsrf();
        $uid     = current_user_id();
        $user    = DB::fetch("SELECT * FROM users WHERE id=?", [$uid]);
        $country = sanitize($_POST['country'] ?? $user['country'] ?? '');
        $note    = sanitize($_POST['note'] ?? '');

        if (!$country) json_response(['success' => false, 'error' => 'Please specify your country.']);

        DB::beginTransaction();
        try {
            $subject = 'Wire Transfer Details Request — ' . $country;
            $message = 'I would like to receive wire transfer details for my country: ' . $country . '.';
            if ($note) $message .= "\n\nAdditional note: " . $note;

            $ref = 'TKT-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
            $ticketId = (int) DB::insert(
                "INSERT INTO support_tickets (user_id, reference, subject, priority) VALUES (?,?,?,'high')",
                [$uid, $ref, $subject]
            );
            DB::query(
                "INSERT INTO ticket_messages (ticket_id, sender_type, sender_id, message) VALUES (?,'user',?,?)",
                [$ticketId, $uid, $message]
            );
            DB::commit();

            try { Mailer::notifyAdminTicket($user, ['id' => $ticketId, 'reference' => $ref, 'subject' => $subject, 'priority' => 'high']); } catch (\Throwable $e) {}
            json_response(['success' => true, 'reference' => $ref]);
        } catch (\Exception $e) {
            DB::rollback();
            json_response(['success' => false, 'error' => 'Failed to submit request. Please try again.']);
        }
    }

    public static function replyTicket(): void {
        AuthMiddleware::investor();
        AuthMiddleware::verifyCsrf();
        $uid      = current_user_id();
        $ticketId = (int) input('ticket_id', 0);
        $message  = sanitize(input('message', ''));

        if (!$ticketId || !$message) json_response(['success' => false, 'error' => 'Message is required.']);

        $ticket = DB::fetch("SELECT * FROM support_tickets WHERE id=? AND user_id=? AND status IN ('open','in_progress')", [$ticketId, $uid]);
        if (!$ticket) json_response(['success' => false, 'error' => 'Ticket not found or closed.']);

        DB::query("INSERT INTO ticket_messages (ticket_id, sender_type, sender_id, message) VALUES (?,'user',?,?)", [$ticketId, $uid, $message]);
        DB::execute("UPDATE support_tickets SET updated_at=NOW() WHERE id=?", [$ticketId]);
        json_response(['success' => true]);
    }

    // ── GDPR / Data export ────────────────────────────────────
    public static function dataExport(): void {
        AuthMiddleware::investor();
        $uid  = current_user_id();
        $user = DB::fetch(
            "SELECT id, first_name, last_name, email, phone, country, kyc_status, status, wallet_balance, created_at FROM users WHERE id=?",
            [$uid]
        );
        $transactions = DB::fetchAll("SELECT * FROM transactions WHERE user_id=?", [$uid]);
        $holdings     = DB::fetchAll(
            "SELECT ih.*, i.name AS investment_name FROM investment_holdings ih JOIN investments i ON i.id=ih.investment_id WHERE ih.user_id=?",
            [$uid]
        );
        $tickets = DB::fetchAll("SELECT id, reference, subject, status, created_at FROM support_tickets WHERE user_id=?", [$uid]);

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="nexvest-data-export-' . date('Y-m-d') . '.json"');
        header('Cache-Control: no-cache');
        echo json_encode([
            'exported_at'  => date('c'),
            'profile'      => $user,
            'transactions' => $transactions,
            'holdings'     => $holdings,
            'tickets'      => $tickets,
        ], JSON_PRETTY_PRINT);
        exit;
    }

    public static function requestDeletion(): void {
        AuthMiddleware::investor();
        AuthMiddleware::verifyCsrf();
        $uid  = current_user_id();
        $user = DB::fetch("SELECT * FROM users WHERE id=?", [$uid]);

        $ref = 'TKT-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
        DB::beginTransaction();
        try {
            $ticketId = (int) DB::insert(
                "INSERT INTO support_tickets (user_id, reference, subject, priority) VALUES (?,?,?,'high')",
                [$uid, $ref, 'Account Deletion Request (GDPR/Right to Erasure)']
            );
            DB::query(
                "INSERT INTO ticket_messages (ticket_id, sender_type, sender_id, message) VALUES (?,'user',?,?)",
                [$ticketId, $uid, 'I am requesting deletion of my account and all associated personal data under GDPR Article 17 (Right to Erasure). Please confirm receipt and timeline.']
            );
            DB::commit();
            try { Mailer::notifyAdminTicket($user, ['id' => $ticketId, 'reference' => $ref, 'subject' => 'Account Deletion Request (GDPR)', 'priority' => 'high']); } catch (\Throwable $e) {}
            json_response(['success' => true, 'reference' => $ref]);
        } catch (\Exception $e) {
            DB::rollback();
            json_response(['success' => false, 'error' => 'Failed to submit deletion request.']);
        }
    }

    // ── Auto-reinvest toggle ──────────────────────────────────
    public static function toggleReinvest(): void {
        AuthMiddleware::investor();
        AuthMiddleware::verifyCsrf();
        $uid     = current_user_id();
        $hid     = (int) ($_GET['id'] ?? input('holding_id', 0));
        $holding = DB::fetch("SELECT * FROM investment_holdings WHERE id=? AND user_id=? AND status='active'", [$hid, $uid]);
        if (!$holding) json_response(['success' => false, 'error' => 'Holding not found.']);
        $new = $holding['auto_reinvest'] ? 0 : 1;
        DB::execute("UPDATE investment_holdings SET auto_reinvest=? WHERE id=?", [$new, $hid]);
        json_response(['success' => true, 'auto_reinvest' => $new, 'message' => $new ? 'Auto-reinvest enabled.' : 'Auto-reinvest disabled.']);
    }

    // ── Investment top-up (tranche) ───────────────────────────
    public static function topUp(): void {
        AuthMiddleware::investor();
        AuthMiddleware::verifyCsrf();
        $uid       = current_user_id();
        $holdingId = (int) input('holding_id', 0);
        $amount    = (float) input('amount', 0);

        if (!$holdingId || $amount <= 0) json_response(['success' => false, 'error' => 'Invalid request.']);

        $holding = DB::fetch(
            "SELECT ih.*, i.name AS investment_name, i.min_investment, i.max_investment, i.funding_target, i.funding_raised
             FROM investment_holdings ih JOIN investments i ON i.id=ih.investment_id
             WHERE ih.id=? AND ih.user_id=? AND ih.status='active'",
            [$holdingId, $uid]
        );
        if (!$holding) json_response(['success' => false, 'error' => 'Active holding not found.']);

        if ($amount < (float) $holding['min_investment']) {
            json_response(['success' => false, 'error' => 'Minimum top-up is ' . fmt_currency((float)$holding['min_investment']) . '.']);
        }

        $user = DB::fetch("SELECT * FROM users WHERE id=?", [$uid]);
        if ((float)$user['wallet_balance'] < $amount) {
            json_response(['success' => false, 'error' => 'Insufficient wallet balance. Available: ' . fmt_currency((float)$user['wallet_balance']) . '.']);
        }

        if ($holding['funding_target']) {
            $remaining = (float)$holding['funding_target'] - (float)$holding['funding_raised'];
            if ($amount > $remaining) {
                json_response(['success' => false, 'error' => 'Amount exceeds remaining funding capacity of ' . fmt_currency($remaining) . '.']);
            }
        }

        DB::beginTransaction();
        try {
            $balBefore = (float)$user['wallet_balance'];
            DB::execute("UPDATE investment_holdings SET amount = amount + ? WHERE id=?", [$amount, $holdingId]);
            DB::execute("UPDATE users SET wallet_balance = wallet_balance - ? WHERE id=?", [$amount, $uid]);
            DB::execute("UPDATE investments SET funding_raised = funding_raised + ? WHERE id=?", [$amount, $holding['investment_id']]);
            $ref = generate_reference('TOP');
            DB::query(
                "INSERT INTO transactions (user_id, type, amount, balance_before, balance_after, status, method, reference, description)
                 VALUES (?,?,?,?,?,'completed','wallet',?,?)",
                [$uid, 'investment', $amount, $balBefore, $balBefore - $amount, $ref, 'Top-up: ' . $holding['investment_name']]
            );
            DB::commit();
            create_notification($uid, 'investment', 'Investment Top-up Successful',
                fmt_currency($amount) . ' added to your ' . $holding['investment_name'] . ' holding.');
            json_response(['success' => true, 'message' => 'Top-up of ' . fmt_currency($amount) . ' applied successfully.']);
        } catch (\Exception $e) {
            DB::rollback();
            json_response(['success' => false, 'error' => 'Top-up failed. Please try again.']);
        }
    }

    // ── Compliance / Legal ────────────────────────────────────
    public static function compliance(): void {
        AuthMiddleware::investor();
        view('investor.compliance', ['title' => 'Legal & Compliance']);
    }

    // ── Admin Invoices (investor side) ────────────────────────
    public static function invoices(): void {
        AuthMiddleware::investor();
        $uid          = current_user_id();
        $activeTab    = in_array($_GET['status'] ?? '', ['pending','paid','cancelled'], true) ? $_GET['status'] : 'all';
        $invoices_all = DB::fetchAll(
            "SELECT * FROM admin_invoices WHERE user_id=? ORDER BY created_at DESC",
            [$uid]
        );
        $invoices = $activeTab === 'all'
            ? $invoices_all
            : array_values(array_filter($invoices_all, fn($r) => $r['status'] === $activeTab));
        view('investor.invoices', compact('invoices','invoices_all','activeTab') + ['title' => 'My Invoices']);
    }

    public static function invoiceDetail(): void {
        AuthMiddleware::investor();
        $uid = current_user_id();
        $ref = sanitize($_GET['ref'] ?? '');
        if (!$ref) redirect('/investor/invoices');

        $invoice = DB::fetch(
            "SELECT ai.*, a.name AS admin_name, p.platform_name
             FROM admin_invoices ai
             JOIN admins a ON a.id = ai.admin_id
             CROSS JOIN (SELECT setting_value AS platform_name FROM platform_settings WHERE setting_key='platform_name' LIMIT 1) p
             WHERE ai.reference=? AND ai.user_id=?",
            [$ref, $uid]
        );
        if (!$invoice) redirect('/investor/invoices');

        $user        = DB::fetch("SELECT * FROM users WHERE id=?", [$uid]);
        $walletBal   = (float)($user['wallet_balance'] ?? 0);
        $cryptoAddrs = [
            'btc'  => platform_setting('crypto_btc_address',  ''),
            'eth'  => platform_setting('crypto_eth_address',  ''),
            'usdt' => platform_setting('crypto_usdt_address', ''),
            'usdc' => platform_setting('crypto_usdc_address', ''),
        ];
        $paypalEmail = platform_setting('paypal_email', platform_setting('platform_email',''));
        $paypalMe    = platform_setting('paypal_me_link', '');
        $wireDetails = [
            'Bank Name'      => platform_setting('wire_bank_name',''),
            'Account Name'   => platform_setting('wire_account_name',''),
            'Account Number' => platform_setting('wire_account_number',''),
            'Routing Number' => platform_setting('wire_routing',''),
            'SWIFT / BIC'    => platform_setting('wire_swift',''),
        ];
        $depositTimeout = (int) platform_setting('deposit_timeout', '1800');
        view('investor.invoice_detail', compact('invoice','user','walletBal','cryptoAddrs','paypalEmail','paypalMe','wireDetails','depositTimeout') + ['title' => 'Invoice ' . $ref]);
    }

    public static function payInvoice(): void {
        AuthMiddleware::investor();
        AuthMiddleware::verifyCsrf();
        $uid    = current_user_id();
        $ref    = sanitize($_GET['ref'] ?? '');
        $method = sanitize($_POST['method'] ?? '');
        $coin   = sanitize($_POST['coin']   ?? '');

        $invoice = DB::fetch("SELECT * FROM admin_invoices WHERE reference=? AND user_id=? AND status='pending'", [$ref, $uid]);
        if (!$invoice) json_response(['success' => false, 'error' => 'Invoice not found or already processed.']);

        $invoiceAllowed = $invoice['payment_method'] === 'any'
            ? ['crypto','paypal','wire']
            : [$invoice['payment_method']];
        $enabledGlobally = [];
        if (platform_setting('payment_crypto','1') === '1') $enabledGlobally[] = 'crypto';
        if (platform_setting('payment_paypal','1') === '1') $enabledGlobally[] = 'paypal';
        if (platform_setting('payment_wire','1')   === '1') $enabledGlobally[] = 'wire';
        $allowedMethods = array_intersect($invoiceAllowed, $enabledGlobally);
        if (!in_array($method, $allowedMethods, true)) {
            json_response(['success' => false, 'error' => 'Selected payment method is not available.']);
        }

        // Build deposit invoice linked to this admin invoice
        $timeout    = (int) platform_setting('deposit_timeout', '1800');
        $depositRef = generate_reference('NV');
        $expiresAt  = date('Y-m-d H:i:s', time() + $timeout);

        $wallets = [
            'btc'  => platform_setting('crypto_btc_address', ''),
            'eth'  => platform_setting('crypto_eth_address', ''),
            'usdt' => platform_setting('crypto_usdt_address', ''),
            'usdc' => platform_setting('crypto_usdc_address', ''),
        ];
        if ($method === 'crypto') {
            if (!$coin || !isset($wallets[$coin]) || empty($wallets[$coin])) {
                json_response(['success' => false, 'error' => 'Please select a cryptocurrency.']);
            }
        }
        $walletAddr = $method === 'crypto' ? $wallets[$coin] : null;

        DB::query(
            "INSERT INTO deposit_invoices (user_id, reference, amount, method, coin, wallet_address, status, expires_at, admin_note)
             VALUES (?,?,?,?,?,?,'pending',?,?)",
            [$uid, $depositRef, $invoice['amount'], $method, $coin ?: null, $walletAddr, $expiresAt, 'Invoice: ' . $invoice['reference']]
        );

        // Link deposit ref to admin invoice
        DB::execute("UPDATE admin_invoices SET deposit_ref=? WHERE id=?", [$depositRef, $invoice['id']]);

        json_response([
            'success'      => true,
            'reference'    => $depositRef,
            'expires_at'   => $expiresAt,
            'wallet_address' => $walletAddr,
            'timeout'      => $timeout,
            'redirect'     => '/investor/wallet',
        ]);
    }

    public static function payInvoiceBalance(): void {
        AuthMiddleware::investor();
        AuthMiddleware::verifyCsrf();
        $uid     = current_user_id();
        $ref     = sanitize($_GET['ref'] ?? '');

        if (platform_setting('invoice_wallet_payment', '1') !== '1') {
            json_response(['success' => false, 'error' => 'Wallet payment for invoices is currently disabled.']);
        }

        $invoice = DB::fetch("SELECT * FROM admin_invoices WHERE reference=? AND user_id=? AND status='pending'", [$ref, $uid]);
        if (!$invoice) json_response(['success' => false, 'error' => 'Invoice not found or already processed.']);

        $amount = (float) $invoice['amount'];
        $user   = DB::fetch("SELECT id, wallet_balance, first_name, last_name, email FROM users WHERE id=?", [$uid]);
        $balance = (float) $user['wallet_balance'];

        if ($balance < $amount) {
            json_response(['success' => false, 'error' => 'Insufficient wallet balance. Available: ' . fmt_currency($balance) . '.']);
        }

        DB::beginTransaction();
        try {
            $balBefore = $balance;
            $balAfter  = $balance - $amount;
            DB::execute("UPDATE users SET wallet_balance = wallet_balance - ? WHERE id = ? AND wallet_balance >= ?", [$amount, $uid, $amount]);

            $txRef = generate_reference('INV');
            DB::query(
                "INSERT INTO transactions (user_id, type, amount, balance_before, balance_after, status, reference, description)
                 VALUES (?,?,?,?,?,'completed',?,?)",
                [$uid, 'investment', $amount, $balBefore, $balAfter, $txRef, 'Invoice payment — ' . $invoice['title'] . ' (' . $invoice['reference'] . ')']
            );

            DB::execute(
                "UPDATE admin_invoices SET status='paid', paid_at=NOW(), deposit_ref=? WHERE id=?",
                [$txRef, $invoice['id']]
            );

            DB::commit();

            create_notification($uid, 'invoice', 'Invoice Paid',
                fmt_currency($amount) . ' deducted from your wallet for: ' . $invoice['title'] . '.');

            json_response(['success' => true, 'message' => 'Invoice paid successfully.', 'new_balance' => $balAfter]);
        } catch (\Throwable $e) {
            DB::rollback();
            json_response(['success' => false, 'error' => 'Payment failed. Please try again.']);
        }
    }

    public static function terms(): void {
        $content = platform_setting('legal_terms', '');
        $name    = platform_setting('platform_name', 'NexVest');
        view('public.legal', ['title' => 'Terms of Service', 'heading' => 'Terms of Service', 'content' => $content, 'platform_name' => $name], '');
    }

    public static function privacy(): void {
        $content = platform_setting('legal_privacy', '');
        $name    = platform_setting('platform_name', 'NexVest');
        view('public.legal', ['title' => 'Privacy Policy', 'heading' => 'Privacy Policy', 'content' => $content, 'platform_name' => $name], '');
    }

    // ── Profile ────────────────────────────────────────────────
    public static function profile(): void {
        AuthMiddleware::investor();
        $uid  = current_user_id();
        $user = DB::fetch("SELECT * FROM users WHERE id=?", [$uid]);
        $sessions = DB::fetchAll("SELECT * FROM user_sessions WHERE user_id=? ORDER BY last_active DESC", [$uid]);
        view('investor.profile', compact('user','sessions') + ['title'=>'My Profile']);
    }

    public static function updateProfile(): void {
        AuthMiddleware::investor();
        AuthMiddleware::verifyCsrf();
        $uid        = current_user_id();
        $firstName  = trim(sanitize($_POST['first_name']  ?? ''));
        $lastName   = trim(sanitize($_POST['last_name']   ?? ''));
        $phone      = sanitize($_POST['phone']             ?? '');
        $country    = sanitize($_POST['country']           ?? '');
        $dob        = sanitize($_POST['dob'] ?? '');

        if (!$firstName || !$lastName) {
            json_response(['success' => false, 'error' => 'First name and last name are required.']);
        }

        $dobVal = ($dob && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) ? $dob : null;

        DB::execute(
            "UPDATE users SET first_name=?, last_name=?, phone=?, country=?, date_of_birth=?, updated_at=NOW() WHERE id=?",
            [$firstName, $lastName, $phone, $country, $dobVal, $uid]
        );
        $_SESSION['user_name'] = $firstName . ' ' . $lastName;
        json_response(['success' => true, 'message' => 'Profile updated successfully.']);
    }

    public static function changePassword(): void {
        AuthMiddleware::investor();
        AuthMiddleware::verifyCsrf();
        $uid     = current_user_id();
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password']     ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $user = DB::fetch("SELECT * FROM users WHERE id=?", [$uid]);
        if (!verify_password($current, $user['password'])) {
            json_response(['success' => false, 'error' => 'Current password is incorrect.']);
        }
        if (!validate_password($new)) {
            json_response(['success' => false, 'error' => 'New password must be at least 8 characters with uppercase, lowercase and a number.']);
        }
        if ($new !== $confirm) {
            json_response(['success' => false, 'error' => 'New passwords do not match.']);
        }

        DB::execute("UPDATE users SET password=?, updated_at=NOW() WHERE id=?", [hash_password($new), $uid]);
        try { Mailer::sendPasswordChanged($user); } catch (\Throwable $e) {}
        json_response(['success' => true, 'message' => 'Password changed successfully.']);
    }

    // ── Referrals ──────────────────────────────────────────────
    public static function referrals(): void {
        AuthMiddleware::investor();
        $uid       = current_user_id();
        $user      = DB::fetch("SELECT * FROM users WHERE id=?", [$uid]);
        $referrals = DB::fetchAll(
            "SELECT r.*, CONCAT(u.first_name,' ',u.last_name) AS referred_name, u.email AS referred_email, u.country AS referred_country, u.created_at AS joined,
             r.commission_amount,
             (SELECT COALESCE(SUM(ih.amount),0) FROM investment_holdings ih WHERE ih.user_id=r.referred_id AND ih.status='active') AS invested_amount
             FROM referrals r JOIN users u ON u.id=r.referred_id WHERE r.referrer_id=? ORDER BY r.created_at DESC",
            [$uid]
        );
        $commissions = DB::fetchAll(
            "SELECT t.*, r.commission_amount FROM transactions t
             LEFT JOIN referrals r ON r.referrer_id=? AND t.description LIKE CONCAT('%',CONCAT(r.referred_id,'%'))
             WHERE t.user_id=? AND t.type='referral_commission' ORDER BY t.created_at DESC",
            [$uid, $uid]
        );
        $stats = [
            'total'      => count($referrals),
            'invested'   => count(array_filter($referrals, fn($r) => $r['status'] !== 'registered')),
            'total_comm' => array_sum(array_column($referrals, 'commission_amount')),
            'pending'    => array_sum(array_column(array_filter($referrals, fn($r) => !$r['paid_at']), 'commission_amount')),
        ];
        view('investor.referrals', compact('user','referrals','commissions','stats') + ['title'=>'Referrals']);
    }

    // ── Certificates ───────────────────────────────────────────
    public static function certificates(): void {
        AuthMiddleware::investor();
        $uid      = current_user_id();
        $holdings = DB::fetchAll(
            "SELECT ih.*, i.name, i.type, i.roi AS inv_roi, i.duration_value, i.duration_unit,
                    i.city, i.country AS inv_country
             FROM investment_holdings ih
             JOIN investments i ON i.id=ih.investment_id
             WHERE ih.user_id=? AND ih.status IN ('active','matured')
             ORDER BY ih.created_at DESC",
            [$uid]
        );
        view('investor.certificates', compact('holdings') + ['title'=>'Certificates']);
    }

    public static function downloadCertificate(): void {
        AuthMiddleware::investor();
        $uid = current_user_id();
        $ref = sanitize($_GET['ref'] ?? '');
        if (!$ref) redirect('/investor/certificates');

        $holding = DB::fetch(
            "SELECT ih.*, i.name AS inv_name, i.type, i.roi AS inv_roi, i.duration_value, i.duration_unit,
                    i.city, i.country AS inv_country, i.property_type, i.fund_category
             FROM investment_holdings ih
             JOIN investments i ON i.id=ih.investment_id
             WHERE ih.certificate_ref=? AND ih.user_id=?",
            [$ref, $uid]
        );
        if (!$holding) redirect('/investor/certificates');

        $user   = DB::fetch("SELECT * FROM users WHERE id=?", [$uid]);
        $pName  = platform_setting('platform_name',     'NexVest');
        $pTagline = platform_setting('platform_tagline','Capital Group');
        $pInit  = platform_setting('platform_initials', 'NV');
        $pUrl   = platform_setting('platform_website',  '');
        $pAddr  = platform_setting('platform_address',  '');
        $sym    = platform_setting('platform_symbol',   '$');

        $roi    = (float)($holding['roi'] ?? $holding['inv_roi'] ?? 0);
        $dv     = (int)($holding['duration_value']  ?? 0);
        $du     = $holding['duration_unit'] ?? 'months';
        // ROI is the total return over the full duration
        $total  = (float)$holding['amount'] * $roi / 100;

        // Brand + signatories — all configurable (never hardcoded)
        $brand      = $pName;
        $initials   = htmlspecialchars(mb_strtoupper($pInit));      // dynamic monogram
        $brandSp    = htmlspecialchars(mb_strtoupper($brand));      // masthead wordmark
        // Scale the masthead down for longer names so it never runs off the page
        $bl         = mb_strlen($brand);
        $brandFs    = $bl > 18 ? 15 : ($bl > 13 ? 18 : 22);
        $brandLs    = $bl > 18 ? 2  : ($bl > 13 ? 4  : 6);
        $tagline    = $pTagline;
        $sig1n      = platform_setting('cert_signatory1_name',  '');
        $sig1t      = platform_setting('cert_signatory1_title', 'Chief Investment Officer');
        $sig2n      = platform_setting('cert_signatory2_name',  '');
        $sig2t      = platform_setting('cert_signatory2_title', 'Head of Compliance');

        $fullName   = htmlspecialchars(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')));
        $email      = htmlspecialchars($user['email'] ?? '');
        $country    = htmlspecialchars($user['country'] ?? '');
        $invName    = htmlspecialchars($holding['inv_name'] ?? $holding['name'] ?? '');
        $certRef    = htmlspecialchars($holding['certificate_ref'] ?? '');
        $startDate  = fmt_date($holding['start_date']);
        $endDate    = fmt_date($holding['end_date']);
        $issueDate  = date('F j, Y');
        $typeLabel  = $holding['type'] === 'real_estate' ? 'Real Estate' : 'Index Fund';
        $durLabel   = $dv . ' ' . ucfirst($du);
        $fmtAmt     = $sym . number_format((float)$holding['amount'], 2);
        $fmtTotal   = $sym . number_format($total,  2);
        $fmtMaturity= $sym . number_format((float)$holding['amount'] + $total, 2);
        $verifyHost = preg_replace('#^https?://#', '', rtrim($pUrl, '/')) . '/verify';

        // Decorative assets (generated as SVG, embedded as data URIs)
        $frameData  = 'data:image/svg+xml;base64,' . base64_encode(self::certFrameSvg());
        $sealData   = 'data:image/svg+xml;base64,' . base64_encode(self::certSealSvg(mb_strtoupper($brand), 'OFFICIAL · CERTIFIED · SECURE', mb_strtoupper($pInit)));
        $flourishSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="150" height="14" viewBox="0 0 150 14"><g fill="none" stroke="#8f7230" stroke-width="1"><path d="M6 7H60"/><path d="M144 7H90"/><path d="M66 7 Q71 1.5 76 7 Q81 12.5 86 7"/></g><circle cx="76" cy="7" r="2" fill="#8f7230"/></svg>';
        $flourishData = 'data:image/svg+xml;base64,' . base64_encode($flourishSvg);

        $sig1ink    = $sig1n !== '' ? '<div class="ink">' . htmlspecialchars($sig1n) . '</div>' : '<div class="ink">&nbsp;</div>';
        $sig2ink    = $sig2n !== '' ? '<div class="ink">' . htmlspecialchars($sig2n) . '</div>' : '<div class="ink">&nbsp;</div>';
        $sig1t      = htmlspecialchars($sig1t); $sig2t = htmlspecialchars($sig2t);
        $brandTxt   = htmlspecialchars($brand); $taglineTxt = htmlspecialchars($tagline);

        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8"/>
<style>
  @page { margin: 0; }
  body { margin:0; color:#0F2A20; font-family:'dejavuserif',serif; background-color:#EEF0E6; }
  .frame-layer { position:fixed; top:0; left:0; width:210mm; height:297mm; }
  .sheet { padding: 44px 44px 30px; }
  .tc { text-align:center; }
  .brandmark { width:34px; height:34px; border:1.4px solid #0F2A20; border-radius:17px;
               font-size:14px; font-weight:bold; text-align:center; padding-top:8px; margin:0 auto 7px; }
  .brand { font-size:21px; font-weight:bold; letter-spacing:6px; }
  .bsub { font-size:8.5px; letter-spacing:3px; color:#8f7230; margin-top:5px; text-transform:uppercase; }
  .hr { border-top:1px solid #b79a56; width:40%; margin:12px auto 0; }
  .eyebrow { font-size:9px; letter-spacing:3px; color:#3a4f42; text-transform:uppercase; margin-top:15px; }
  .title { font-size:32px; font-weight:bold; letter-spacing:2px; margin-top:7px; }
  .flr { color:#8f7230; font-size:13px; margin-top:1px; }
  .iname { font-size:26px; letter-spacing:1px; padding-bottom:6px; border-bottom:2px solid #b79a56; }
  .imeta { font-size:10px; letter-spacing:1.5px; color:#3a4f42; margin-top:9px; text-transform:uppercase; }
  .recital { font-size:12.5px; line-height:1.7; color:#3a4f42; font-style:italic; text-align:center; margin:15px 8% 0; }
  table.part { width:100%; border-collapse:collapse; border:1px solid #b79a56; margin-top:19px; }
  table.part td { width:50%; padding:9px 15px; border:1px solid #DfE3d2; }
  .l { font-size:8px; letter-spacing:1.5px; color:#8f7230; font-weight:bold; text-transform:uppercase; }
  .v { font-size:13.5px; font-weight:bold; color:#0F2A20; }
  .vm { font-family:'dejavusansmono',monospace; font-size:11.5px; }
  table.fin { width:100%; border-collapse:collapse; margin-top:15px; background-color:#0F2A20; }
  table.fin td { width:33%; text-align:center; padding:13px 6px; color:#EEF0E6; border-right:1px solid #33513f; }
  table.fin td.last { border-right:none; }
  .fl { font-size:8px; letter-spacing:2px; color:#BFA05E; text-transform:uppercase; }
  .fv { font-size:20px; margin-top:6px; }
  .fv.em { color:#8fd3ac; }
  table.att { width:100%; margin-top:24px; }
  .sig { text-align:center; vertical-align:bottom; }
  .ink { font-style:italic; font-size:19px; height:26px; }
  .sigln { border-top:1.2px solid #0F2A20; padding-top:6px; }
  .role { font-size:10.5px; font-weight:bold; letter-spacing:.5px; }
  .org { font-size:8px; letter-spacing:1.5px; color:#3a4f42; text-transform:uppercase; margin-top:2px; }
  .sealcell { text-align:center; vertical-align:bottom; }
  table.foot { width:100%; margin-top:16px; border-top:1px solid #b79a56; }
  .verify { font-size:9px; color:#3a4f42; line-height:1.6; padding-top:9px; text-align:center; }
  .verify b { color:#0F2A20; letter-spacing:1px; }
  .micro { font-size:4.5px; letter-spacing:.4px; color:#8f7230; text-transform:uppercase; }
</style>
</head>
<body>
<div class="frame-layer"><img src="$frameData" style="width:210mm;height:297mm;"/></div>
<div class="sheet">
  <div class="tc">
    <div class="brandmark">$initials</div>
    <div class="brand" style="font-size:{$brandFs}px;letter-spacing:{$brandLs}px">$brandSp</div>
    <div class="bsub">$taglineTxt</div>
    <div class="hr"></div>
  </div>

  <div class="tc">
    <div class="eyebrow">This is to certify that the registered holder is the owner of the</div>
    <div class="title">Certificate of Investment</div>
    <div class="flr"><img src="$flourishData" width="130"/></div>
  </div>

  <div class="tc" style="margin-top:12px">
    <span class="iname">$fullName</span>
    <div class="imeta">$country</div>
  </div>

  <p class="recital">has made a confirmed and legally binding subscription to the investment product particularised below, offered through $brandTxt $taglineTxt, subject to the terms of the executed investment agreement.</p>

  <table class="part">
    <tr>
      <td><div class="l">Investment Product</div><div class="v">$invName</div></td>
      <td><div class="l">Instrument Type</div><div class="v">$typeLabel</div></td>
    </tr>
    <tr>
      <td><div class="l">Total ROI</div><div class="v">{$roi}% over the full term</div></td>
      <td><div class="l">Duration</div><div class="v">$durLabel</div></td>
    </tr>
    <tr>
      <td><div class="l">Commencement</div><div class="v">$startDate</div></td>
      <td><div class="l">Maturity</div><div class="v">$endDate</div></td>
    </tr>
    <tr>
      <td><div class="l">Certificate Reference</div><div class="v vm">$certRef</div></td>
      <td><div class="l">Date of Issue</div><div class="v">$issueDate</div></td>
    </tr>
  </table>

  <table class="fin">
    <tr>
      <td><div class="fl">Principal Invested</div><div class="fv">$fmtAmt</div></td>
      <td><div class="fl">Total Return &middot; {$roi}%</div><div class="fv em">$fmtTotal</div></td>
      <td class="last"><div class="fl">Value at Maturity</div><div class="fv em">$fmtMaturity</div></td>
    </tr>
  </table>

  <table class="att">
    <tr>
      <td class="sig" style="width:34%">
        $sig1ink
        <div class="sigln"><div class="role">$sig1t</div><div class="org">$brandTxt</div></div>
      </td>
      <td class="sealcell" style="width:32%"><img src="$sealData" width="128" height="128"/></td>
      <td class="sig" style="width:34%">
        $sig2ink
        <div class="sigln"><div class="role">$sig2t</div><div class="org">$brandTxt</div></div>
      </td>
    </tr>
  </table>

  <table class="foot">
    <tr>
      <td class="verify">Verify the authenticity of this certificate at<br/><b>$verifyHost</b> &nbsp;&middot;&nbsp; Ref $certRef
        <div class="micro">$brandTxt &middot; CERTIFIED INVESTMENT &middot; $brandTxt &middot; CERTIFIED INVESTMENT</div>
      </td>
    </tr>
  </table>
</div>
</body>
</html>
HTML;

        // Try mPDF (Composer); fall back to browser-print HTML if not installed
        $autoload = ROOT . '/vendor/autoload.php';
        if (file_exists($autoload)) {
            require_once $autoload;
            if (class_exists('Mpdf\Mpdf')) {
                try {
                    $tmpDir = ROOT . '/storage/tmp';
                    if (!is_dir($tmpDir)) @mkdir($tmpDir, 0755, true);
                    $mpdf = new \Mpdf\Mpdf([
                        'mode'          => 'utf-8',
                        'format'        => 'A4',
                        'margin_left'   => 0,
                        'margin_right'  => 0,
                        'margin_top'    => 0,
                        'margin_bottom' => 0,
                        'tempDir'       => ROOT . '/storage/tmp',
                    ]);
                    $mpdf->SetTitle("Investment Certificate — {$certRef}");
                    $mpdf->SetWatermarkText(mb_strtoupper($brand), 0.045);
                    $mpdf->showWatermarkText = true;
                    $mpdf->watermark_font    = 'dejavuserif';
                    $mpdf->WriteHTML($html);
                    $mpdf->Output("Certificate-{$certRef}.pdf", 'D');
                    exit;
                } catch (\Throwable $e) {
                    error_log('mPDF error: ' . $e->getMessage());
                }
            }
        }

        // Fallback: browser print
        echo $html;
        exit;
    }

    // ── Certificate decorative assets (generated SVG) ──────────
    private static function certFrameSvg(): string {
        $gold = '#8f7230'; $gold2 = '#b79a56';
        $W = 600; $H = 849;
        $s = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $W . '" height="' . $H . '" viewBox="0 0 ' . $W . ' ' . $H . '">';
        $s .= '<rect x="18" y="18" width="' . ($W - 36) . '" height="' . ($H - 36) . '" fill="none" stroke="' . $gold . '" stroke-width="2"/>';
        $s .= '<rect x="26" y="26" width="' . ($W - 52) . '" height="' . ($H - 52) . '" fill="none" stroke="' . $gold2 . '" stroke-width="1"/>';
        $band = function ($x0, $y0, $len, $horiz, $amp, $waves, $lines) use ($gold2) {
            $out = '';
            for ($k = 0; $k < $lines; $k++) {
                $ph = $k / $lines * 2 * M_PI; $d = '';
                for ($i = 0; $i <= $len; $i += 3) {
                    $t   = $i / $len * 2 * M_PI * $waves;
                    $off = sin($t + $ph) * $amp * 0.5 + sin($t * 2.7 + $ph) * $amp * 0.16;
                    if ($horiz) { $x = $x0 + $i; $y = $y0 + $off; } else { $x = $x0 + $off; $y = $y0 + $i; }
                    $d .= ($i == 0 ? 'M' : 'L') . round($x, 1) . ' ' . round($y, 1) . ' ';
                }
                $out .= '<path d="' . $d . '" fill="none" stroke="' . $gold2 . '" stroke-width="0.4" opacity="0.7"/>';
            }
            return $out;
        };
        $s .= $band(34, 40, $W - 68, true, 9, 40, 4);
        $s .= $band(34, $H - 40, $W - 68, true, 9, 40, 4);
        $s .= $band(40, 34, $H - 68, false, 9, 56, 4);
        $s .= $band($W - 40, 34, $H - 68, false, 9, 56, 4);
        foreach ([[30, 30], [$W - 30, 30], [30, $H - 30], [$W - 30, $H - 30]] as $c) {
            $s .= '<circle cx="' . $c[0] . '" cy="' . $c[1] . '" r="6" fill="none" stroke="' . $gold . '" stroke-width="1"/>';
            $s .= '<circle cx="' . $c[0] . '" cy="' . $c[1] . '" r="2.5" fill="' . $gold . '"/>';
        }
        return $s . '</svg>';
    }

    private static function certSealSvg(string $brandUpper, string $subUpper, string $initials = 'NV'): string {
        $gold = '#8f7230'; $ink = '#0F2A20'; $cx = 75; $cy = 75;
        $s = '<svg xmlns="http://www.w3.org/2000/svg" width="150" height="150" viewBox="0 0 150 150">';
        foreach ([[72, 1.5], [66, 1], [47, 1], [44, 3]] as $p) {
            $s .= '<circle cx="75" cy="75" r="' . $p[0] . '" fill="none" stroke="' . $gold . '" stroke-width="' . $p[1] . '"/>';
        }
        for ($i = 0; $i < 72; $i++) {
            $a = $i / 72 * 2 * M_PI;
            $s .= '<circle cx="' . round($cx + 69 * cos($a), 1) . '" cy="' . round($cy + 69 * sin($a), 1) . '" r="0.9" fill="' . $gold . '"/>';
        }
        $R = 30; $r = 7; $dd = 16; $d = '';
        for ($t = 0; $t <= 2 * M_PI * 7; $t += 0.06) {
            $x = $cx + (($R - $r) * cos($t) + $dd * cos(($R - $r) / $r * $t));
            $y = $cy + (($R - $r) * sin($t) - $dd * sin(($R - $r) / $r * $t));
            $d .= ($t == 0 ? 'M' : 'L') . round($x, 1) . ' ' . round($y, 1) . ' ';
        }
        $s .= '<path d="' . $d . '" fill="none" stroke="' . $gold . '" stroke-width="0.5"/>';
        $arc = function (string $txt, float $radius, float $startDeg, float $endDeg, float $size, bool $flip) use (&$s, $cx, $cy, $ink) {
            $chars = preg_split('//u', $txt, -1, PREG_SPLIT_NO_EMPTY);
            $n = count($chars); if ($n < 2) return;
            for ($i = 0; $i < $n; $i++) {
                $deg = $startDeg + ($endDeg - $startDeg) * ($i / ($n - 1));
                $rad = deg2rad($deg);
                $x = $cx + $radius * cos($rad); $y = $cy + $radius * sin($rad);
                $rot = $flip ? $deg - 90 : $deg + 90;
                $ch  = htmlspecialchars($chars[$i], ENT_QUOTES);
                $s .= '<text x="' . round($x, 1) . '" y="' . round($y, 1) . '" font-family="dejavuserif,serif" font-size="' . $size . '" font-weight="bold" fill="' . $ink . '" text-anchor="middle" transform="rotate(' . round($rot, 1) . ' ' . round($x, 1) . ' ' . round($y, 1) . ')">' . $ch . '</text>';
            }
        };
        $arc($brandUpper, 56, 180, 360, 8, false);
        $arc($subUpper,   54, 180, 0,   7, true);
        $mono = htmlspecialchars($initials !== '' ? $initials : 'NV', ENT_QUOTES);
        $monoFs = mb_strlen($mono) >= 4 ? 16 : (mb_strlen($mono) === 3 ? 21 : 26);
        $s .= '<text x="75" y="' . ($cy + 6) . '" font-family="dejavuserif,serif" font-size="' . $monoFs . '" font-weight="bold" fill="' . $ink . '" text-anchor="middle">' . $mono . '</text>';
        return $s . '</svg>';
    }

    // ── Calculator ─────────────────────────────────────────────
    public static function calculator(): void {
        AuthMiddleware::investor();
        $investments = DB::fetchAll("SELECT id, name, type, roi, duration_value, duration_unit, min_investment, max_investment FROM investments WHERE status='active' ORDER BY type, name");
        view('investor.calculator', compact('investments') + ['title'=>'Earnings Calculator']);
    }

    // ── How It Works ───────────────────────────────────────────
    public static function howItWorks(): void {
        AuthMiddleware::investor();
        // Everything on the page reads from platform settings so it stays accurate
        view('investor.how_it_works', [
            'title'       => 'How It Works',
            'minDeposit'  => (float) platform_setting('min_deposit',    '100'),
            'minWithdraw' => (float) platform_setting('min_withdrawal', '50'),
            'kycOn'       => platform_setting('kyc_enabled', '1') === '1',
            'refRate'     => (float) platform_setting('referral_commission', '5'),
            'payCrypto'   => platform_setting('payment_crypto', '1') === '1',
            'payPaypal'   => platform_setting('payment_paypal', '1') === '1',
            'payWire'     => platform_setting('payment_wire',   '1') === '1',
            'sampleRoi'   => (float) (DB::fetch("SELECT roi FROM investments WHERE status='active' ORDER BY id LIMIT 1")['roi'] ?? 20),
        ]);
    }

    // ── Terminate Investment ────────────────────────────────────
    public static function terminateInvestment(): void {
        AuthMiddleware::investor();
        AuthMiddleware::verifyCsrf();

        $uid       = current_user_id();
        $holdingId = (int) input('holding_id', 0);

        if (!$holdingId) {
            json_response(['success' => false, 'error' => 'Invalid request.']);
        }

        $holding = DB::fetch(
            "SELECT ih.*, i.name AS inv_name FROM investment_holdings ih
             JOIN investments i ON i.id=ih.investment_id
             WHERE ih.id=? AND ih.user_id=? AND ih.status='active'",
            [$holdingId, $uid]
        );

        if (!$holding) {
            json_response(['success' => false, 'error' => 'Active investment not found.']);
        }

        // Interest earned to date has ALREADY been distributed (credited to the wallet for
        // standard payouts, or folded into the principal for auto-reinvest holdings). So an
        // early termination returns the principal only — adding total_earned would pay the
        // already-distributed interest a second time.
        $capital  = (float) $holding['amount'];
        $payout   = round($capital, 2);

        DB::beginTransaction();
        try {
            $user    = DB::fetch("SELECT * FROM users WHERE id=?", [$uid]);
            $balBefore = (float) $user['wallet_balance'];
            $balAfter  = $balBefore + $payout;

            DB::execute("UPDATE investment_holdings SET status='cancelled', updated_at=NOW() WHERE id=?", [$holdingId]);
            DB::execute("UPDATE users SET wallet_balance=? WHERE id=?", [$balAfter, $uid]);
            DB::execute("UPDATE investments SET funding_raised = GREATEST(0, funding_raised - ?) WHERE id=?", [$capital, $holding['investment_id']]);

            $ref = generate_reference('TRM');
            DB::query(
                "INSERT INTO transactions (user_id,type,amount,balance_before,balance_after,status,reference,description)
                 VALUES (?,'withdrawal',?,?,?,'completed',?,?)",
                [$uid, $payout, $balBefore, $balAfter, $ref,
                 'Early termination — ' . $holding['inv_name'] . ' (principal returned)']
            );

            DB::commit();

            create_notification($uid, 'investment', 'Investment Terminated',
                'Your investment in ' . $holding['inv_name'] . ' has been terminated. Your principal of ' .
                fmt_currency($payout) . ' has been returned to your wallet. Interest already paid during the term is yours to keep.');

            try { Mailer::sendInvestmentTerminated($user, $holding, $payout); } catch (\Throwable $e) {}

            json_response(['success' => true, 'message' => fmt_currency($payout) . ' returned to your wallet.']);
        } catch (\Throwable $e) {
            DB::rollback();
            error_log('Terminate investment error: ' . $e->getMessage());
            json_response(['success' => false, 'error' => 'Failed to terminate investment. Please try again.']);
        }
    }
}
