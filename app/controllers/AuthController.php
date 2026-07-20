<?php
declare(strict_types=1);

class AuthController {

    // ── GET /login ────────────────────────────────────────────
    public static function showLogin(): void {
        AuthMiddleware::guest();
        view('auth.login', ['title' => 'Sign In'], 'auth');
    }

    // ── POST /login ───────────────────────────────────────────
    public static function login(): void {
        AuthMiddleware::init();
        AuthMiddleware::verifyCsrf();
        AuthMiddleware::rateLimit('login', (int)env('MAX_LOGIN_ATTEMPTS', 5), (int)env('LOGIN_LOCKOUT_MINUTES', 15) * 60);

        $email    = sanitize_email($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $ip       = get_ip();

        if (!$email || !$password) {
            json_response(['success' => false, 'error' => 'Email and password are required.']);
        }

        // Log attempt
        try { DB::query("INSERT INTO login_attempts (email, ip_address) VALUES (?,?)", [$email, $ip]); }
        catch (Exception $e) {}

        $user = DB::fetch("SELECT * FROM users WHERE email=? AND status != 'banned'", [$email]);

        if (!$user || !verify_password($password, $user['password'])) {
            json_response(['success' => false, 'error' => 'Invalid email or password.']);
        }

        if ($user['status'] === 'suspended') {
            json_response(['success' => false, 'error' => 'Your account has been suspended. Please contact support.']);
        }

        if (!$user['email_verified']) {
            // If verification is disabled globally, auto-verify and let them through
            if (platform_setting('email_verification_enabled', '1') !== '1') {
                DB::execute("UPDATE users SET email_verified=1, email_verified_at=NOW() WHERE id=?", [$user['id']]);
            } else {
                json_response(['success' => false, 'error' => 'Please verify your email address first. Check your inbox for the verification code.', 'action' => 'verify_email', 'email' => $email]);
            }
        }

        // Mark login as successful
        try { DB::execute("UPDATE login_attempts SET success=1 WHERE email=? AND ip_address=? ORDER BY id DESC LIMIT 1", [$email, $ip]); }
        catch (Exception $e) {}

        // 2FA check
        if ($user['two_fa_enabled'] && $user['two_fa_secret']) {
            $_SESSION['pending_2fa_user'] = $user['id'];
            json_response(['success' => true, 'action' => '2fa']);
        }

        // Create session
        self::createUserSession($user, $ip);
        json_response(['success' => true, 'redirect' => '/investor/dashboard']);
    }

    // ── POST /login/2fa ───────────────────────────────────────
    public static function verify2FA(): void {
        AuthMiddleware::init();
        AuthMiddleware::verifyCsrf();

        $userId = (int)($_SESSION['pending_2fa_user'] ?? 0);
        $code   = trim($_POST['code'] ?? '');

        if (!$userId || strlen($code) !== 6) {
            json_response(['success' => false, 'error' => 'Invalid request.']);
        }

        $user = DB::fetch("SELECT * FROM users WHERE id=?", [$userId]);
        if (!$user) json_response(['success' => false, 'error' => 'User not found.']);

        if (!self::verifyTotp($user['two_fa_secret'], $code)) {
            json_response(['success' => false, 'error' => 'Invalid authentication code. Please try again.']);
        }

        unset($_SESSION['pending_2fa_user']);
        self::createUserSession($user, get_ip());
        json_response(['success' => true, 'redirect' => '/investor/dashboard']);
    }

    // ── GET /register ─────────────────────────────────────────
    public static function showRegister(): void {
        AuthMiddleware::guest();
        if (platform_setting('registration_open', '1') !== '1') {
            view('auth.registration_closed', ['title' => 'Registration Closed'], 'auth');
            return;
        }
        $refCode = sanitize($_GET['ref'] ?? $_GET['referral_code'] ?? '');
        view('auth.register', ['title' => 'Create Account', 'refCode' => $refCode], 'auth');
    }

    // ── POST /register ────────────────────────────────────────
    public static function register(): void {
        AuthMiddleware::init();
        AuthMiddleware::verifyCsrf();

        if (platform_setting('registration_open', '1') !== '1') {
            json_response(['success' => false, 'error' => 'Registration is currently closed.']);
        }

        $firstName = sanitize($_POST['first_name']       ?? '');
        $lastName  = sanitize($_POST['last_name']        ?? '');
        $email     = sanitize_email($_POST['email']      ?? '');
        $phone     = sanitize($_POST['phone']            ?? '');
        $country   = sanitize($_POST['country']          ?? '');
        $password  = $_POST['password']                  ?? '';
        $confirm   = $_POST['confirm_password']          ?? '';
        $refCode   = sanitize($_POST['referral_code']    ?? '');

        $errors = [];
        if (!$firstName)                   $errors[] = 'First name is required.';
        if (!$lastName)                    $errors[] = 'Last name is required.';
        if (!$email)                       $errors[] = 'A valid email address is required.';
        if (!$country)                     $errors[] = 'Country is required.';
        if (!validate_password($password)) $errors[] = 'Password must be at least 8 characters with uppercase, lowercase and a number.';
        if ($password !== $confirm)        $errors[] = 'Passwords do not match.';
        if ($errors) json_response(['success' => false, 'errors' => $errors]);

        if (DB::fetch("SELECT id FROM users WHERE email=?", [$email])) {
            json_response(['success' => false, 'error' => 'An account with this email already exists.']);
        }

        // Referral
        $referredBy = null;
        if ($refCode) {
            $referrer = DB::fetch("SELECT id FROM users WHERE referral_code=?", [$refCode]);
            if ($referrer) $referredBy = $referrer['id'];
        }

        DB::beginTransaction();
        try {
            $referralCode = generate_referral_code($firstName, $lastName);
            while (DB::fetch("SELECT id FROM users WHERE referral_code=?", [$referralCode])) {
                $referralCode = generate_referral_code($firstName, $lastName);
            }

            $userId = (int)DB::insert(
                "INSERT INTO users (first_name, last_name, email, password, phone, country, referral_code, referred_by) VALUES (?,?,?,?,?,?,?,?)",
                [$firstName, $lastName, $email, hash_password($password), $phone, $country, $referralCode, $referredBy]
            );

            if ($referredBy) {
                $rate = (float)platform_setting('referral_commission', '5');
                DB::query("INSERT INTO referrals (referrer_id, referred_id, commission_rate) VALUES (?,?,?)", [$referredBy, $userId, $rate]);
                // Notify referrer by email (non-blocking)
                $referrer = DB::fetch("SELECT * FROM users WHERE id=?", [$referredBy]);
                $newUser  = ['first_name' => $firstName, 'last_name' => $lastName, 'email' => $email];
                try { Mailer::sendReferralSignup($referrer, $newUser); } catch (Throwable $e) {}
            }

            // Email verification can be turned off entirely from Admin → Settings → Features.
            // Even when on, it only runs if SMTP is actually configured — otherwise we'd lock
            // investors out waiting for a code that can never arrive.
            $verificationOn = platform_setting('email_verification_enabled', '1') === '1';
            $smtpHost       = platform_setting('smtp_host', env('SMTP_HOST', ''));
            $smtpPass       = platform_setting('smtp_pass', env('SMTP_PASS', ''));
            $smtpConfigured = !empty($smtpHost) && !empty($smtpPass);
            $needsVerification = $verificationOn && $smtpConfigured;

            $otp = null;
            if ($needsVerification) {
                // Just the DB write here — no network call inside the transaction.
                $otp   = generate_otp();
                $token = generate_token();
                $exp   = gmdate('Y-m-d H:i:s', time() + CONFIG['security']['verify_expire_minutes'] * 60);
                DB::execute("UPDATE email_verifications SET used=1 WHERE user_id=?", [$userId]);
                DB::query("INSERT INTO email_verifications (user_id, token, otp, expires_at) VALUES (?,?,?,?)", [$userId, $token, $otp, $exp]);
            } else {
                DB::execute("UPDATE users SET email_verified=1, email_verified_at=NOW() WHERE id=?", [$userId]);
            }

            // Commit immediately — the account now exists regardless of what happens next.
            DB::commit();

        } catch (Exception $e) {
            DB::rollback();
            error_log('Registration error: ' . $e->getMessage());
            json_response(['success' => false, 'error' => 'Registration failed. Please try again.']);
        }

        // ── Everything below runs AFTER commit, so a slow/broken mail server
        //    can never leave the registration half-finished or stuck. ──────────
        if ($needsVerification) {
            $emailData = ['first_name' => $firstName, 'email' => $email];
            register_shutdown_function(function() use ($emailData, $otp) {
                try { Mailer::sendEmailVerification($emailData, $otp); } catch (\Throwable $e) {}
            });
            json_response(['success' => true, 'redirect' => '/verify-email?email=' . urlencode($email)]);
        } else {
            $user = DB::fetch("SELECT * FROM users WHERE id=?", [$userId]);
            self::createUserSession($user, get_ip());
            register_shutdown_function(function() use ($user) {
                try { Mailer::sendWelcome($user); } catch (\Throwable $e) {}
            });
            json_response(['success' => true, 'redirect' => '/investor/setup-2fa']);
        }
    }

    // ── GET /verify-email ─────────────────────────────────────
    public static function showVerifyEmail(): void {
        AuthMiddleware::guest();
        $email = sanitize($_GET['email'] ?? '');
        view('auth.verify_email', ['title' => 'Verify Email', 'email' => $email], 'auth');
    }

    // ── POST /verify-email ────────────────────────────────────
    public static function verifyEmail(): void {
        AuthMiddleware::init();
        AuthMiddleware::verifyCsrf();

        $email = sanitize_email($_POST['email'] ?? '');
        $otp   = trim($_POST['otp'] ?? '');

        if (!$email || strlen($otp) !== 6) {
            json_response(['success' => false, 'error' => 'Invalid request.']);
        }

        $user = DB::fetch("SELECT * FROM users WHERE email=?", [$email]);
        if (!$user) json_response(['success' => false, 'error' => 'Account not found.']);

        if ($user['email_verified']) {
            // Already verified - just log them in
            self::createUserSession($user, get_ip());
            json_response(['success' => true, 'redirect' => '/investor/dashboard']);
        }

        // If admin disabled email verification after this account registered, auto-verify now
        if (platform_setting('email_verification_enabled', '1') !== '1') {
            DB::execute("UPDATE users SET email_verified=1, email_verified_at=NOW() WHERE id=?", [$user['id']]);
            self::createUserSession($user, get_ip());
            json_response(['success' => true, 'redirect' => '/investor/dashboard']);
        }

        $record = DB::fetch(
            "SELECT * FROM email_verifications WHERE user_id=? AND otp=? AND used=0 ORDER BY id DESC LIMIT 1",
            [$user['id'], $otp]
        );

        if (!$record || strtotime($record['expires_at'] . ' UTC') < time()) {
            json_response(['success' => false, 'error' => 'Invalid or expired code. Please try again or request a new code.']);
        }

        DB::beginTransaction();
        try {
            DB::execute("UPDATE email_verifications SET used=1 WHERE id=?", [$record['id']]);
            DB::execute("UPDATE users SET email_verified=1, email_verified_at=NOW() WHERE id=?", [$user['id']]);
            DB::commit();

            // Send welcome email - non-blocking
            try { Mailer::sendWelcome($user); } catch (\Throwable $e) {}
            try { Mailer::notifyAdminNewUser($user); } catch (\Throwable $e) {}

            self::createUserSession($user, get_ip());
            json_response(['success' => true, 'redirect' => '/investor/setup-2fa']);
        } catch (Exception $e) {
            DB::rollback();
            json_response(['success' => false, 'error' => 'Verification failed. Please try again.']);
        }
    }

    // ── POST /verify-email/resend ─────────────────────────────
    public static function resendVerification(): void {
        AuthMiddleware::init();
        AuthMiddleware::verifyCsrf();

        $email = sanitize_email($_POST['email'] ?? '');
        $user  = DB::fetch("SELECT * FROM users WHERE email=? AND email_verified=0", [$email]);

        if (!$user) {
            json_response(['success' => false, 'error' => 'Account not found or already verified.']);
        }

        if (platform_setting('email_verification_enabled', '1') !== '1') {
            // Verification was disabled after this account registered — just verify them now.
            DB::execute("UPDATE users SET email_verified=1, email_verified_at=NOW() WHERE id=?", [$user['id']]);
            self::createUserSession($user, get_ip());
            json_response(['success' => true, 'redirect' => '/investor/setup-2fa']);
        }

        self::sendEmailVerification($user['id'], $email, $user['first_name']);
        json_response(['success' => true, 'message' => 'Verification code resent. Check your inbox.']);
    }

    // ── GET /forgot-password ──────────────────────────────────
    public static function showForgotPassword(): void {
        AuthMiddleware::guest();
        view('auth.forgot_password', ['title' => 'Reset Password'], 'auth');
    }

    // ── POST /forgot-password ─────────────────────────────────
    public static function forgotPassword(): void {
        AuthMiddleware::init();
        AuthMiddleware::verifyCsrf();

        $email = sanitize_email($_POST['email'] ?? '');
        if (!$email) json_response(['success' => false, 'error' => 'Email address is required.']);

        $user = DB::fetch("SELECT * FROM users WHERE email=? AND email_verified=1", [$email]);

        if ($user) {
            $token     = generate_token(64);
            $expiresAt = gmdate('Y-m-d H:i:s', time() + CONFIG['security']['reset_expire_minutes'] * 60);
            DB::query("INSERT INTO password_resets (email, token, expires_at) VALUES (?,?,?)", [$email, $token, $expiresAt]);
            try { Mailer::sendPasswordReset($user, $token); } catch (Throwable $e) {}
        }

        json_response(['success' => true, 'message' => 'If an account exists with that email, a reset link has been sent.']);
    }

    // ── GET /reset-password ───────────────────────────────────
    public static function showResetPassword(): void {
        AuthMiddleware::guest();
        $token  = sanitize($_GET['token'] ?? '');
        $record = DB::fetch("SELECT * FROM password_resets WHERE token=? AND used=0 AND expires_at > UTC_TIMESTAMP()", [$token]);
        if (!$record) {
            flash('error', 'This password reset link is invalid or has expired.');
            redirect('/forgot-password');
        }
        view('auth.reset_password', ['title' => 'New Password', 'token' => $token], 'auth');
    }

    // ── POST /reset-password ──────────────────────────────────
    public static function resetPassword(): void {
        AuthMiddleware::init();
        AuthMiddleware::verifyCsrf();

        $token   = sanitize($_POST['token']           ?? '');
        $password= $_POST['password']                 ?? '';
        $confirm = $_POST['confirm_password']         ?? '';
        $record  = DB::fetch("SELECT * FROM password_resets WHERE token=? AND used=0 AND expires_at > UTC_TIMESTAMP()", [$token]);

        if (!$record) json_response(['success' => false, 'error' => 'Invalid or expired reset link.']);
        if (!validate_password($password)) json_response(['success' => false, 'error' => 'Password must be at least 8 characters with uppercase, lowercase and a number.']);
        if ($password !== $confirm) json_response(['success' => false, 'error' => 'Passwords do not match.']);

        DB::beginTransaction();
        try {
            $user = DB::fetch("SELECT * FROM users WHERE email=?", [$record['email']]);
            if (!$user) throw new Exception('User not found');
            DB::execute("UPDATE users SET password=? WHERE id=?", [hash_password($password), $user['id']]);
            DB::execute("UPDATE password_resets SET used=1 WHERE token=?", [$token]);
            DB::execute("DELETE FROM user_sessions WHERE user_id=?", [$user['id']]);
            DB::commit();
            try { Mailer::sendPasswordChanged($user); } catch (Throwable $e) {}
            json_response(['success' => true, 'redirect' => '/login?msg=password_changed']);
        } catch (Exception $e) {
            DB::rollback();
            json_response(['success' => false, 'error' => 'Failed to reset password. Please try again.']);
        }
    }

    // ── POST /logout ──────────────────────────────────────────
    public static function logout(): void {
        AuthMiddleware::init();
        AuthMiddleware::logout();
        $dest = platform_setting('logout_redirect_url', '');
        redirect($dest ?: '/login');
    }

    // ── Private helpers ───────────────────────────────────────

    private static function createUserSession(array $user, string $ip): void {
        // DO NOT call session_regenerate_id() - breaks sessions on shared cPanel hosting
        $token     = generate_token(64);
        $expiresAt = date('Y-m-d H:i:s', time() + CONFIG['session']['lifetime']);
        $ua        = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);

        // Save session to DB (non-blocking - failure won't break login)
        try {
            DB::query(
                "INSERT INTO user_sessions (user_id, session_token, ip_address, user_agent, last_active, expires_at) VALUES (?,?,?,?,NOW(),?)",
                [$user['id'], $token, $ip, $ua, $expiresAt]
            );
            DB::execute("UPDATE users SET last_login_at=NOW(), last_login_ip=? WHERE id=?", [$ip, $user['id']]);
        } catch (Throwable $e) {}

        // Set session vars - this is what actually keeps the user logged in
        $_SESSION['user_id']          = $user['id'];
        $_SESSION['user_name']        = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['user_email']       = $user['email'];
        $_SESSION['kyc_status']       = $user['kyc_status'];
        $_SESSION['session_token']    = $token;
        $_SESSION['_last_db_check']   = time();

        // Send login alert - non-blocking, never breaks login
        try { Mailer::sendLoginAlert($user, $ip, $ua); } catch (Throwable $e) {}
    }

    private static function sendEmailVerification(int $userId, string $email, string $firstName): void {
        $otp   = generate_otp();
        $token = generate_token();
        $exp   = gmdate('Y-m-d H:i:s', time() + CONFIG['security']['verify_expire_minutes'] * 60);
        DB::execute("UPDATE email_verifications SET used=1 WHERE user_id=?", [$userId]);
        DB::query("INSERT INTO email_verifications (user_id, token, otp, expires_at) VALUES (?,?,?,?)", [$userId, $token, $otp, $exp]);
        try { Mailer::sendEmailVerification(['first_name' => $firstName, 'email' => $email], $otp); } catch (\Throwable $e) {}
    }

    public static function verifyTotpPublic(string $secret, string $code): bool {
        return self::verifyTotp($secret, $code);
    }

    private static function verifyTotp(string $secret, string $code): bool {
        $timeSlot = (int)floor(time() / 30);
        for ($i = -2; $i <= 2; $i++) {
            if (hash_equals(self::generateTotpCode($secret, $timeSlot + $i), $code)) return true;
        }
        return false;
    }

    private static function generateTotpCode(string $secret, int $timeSlot): string {
        $key    = self::base32Decode($secret);
        $msg    = pack('N*', 0) . pack('N*', $timeSlot);
        $hash   = hash_hmac('sha1', $msg, $key, true);
        $offset = ord($hash[19]) & 0xf;
        $code   = (((ord($hash[$offset]) & 0x7f) << 24)
                | ((ord($hash[$offset+1]) & 0xff) << 16)
                | ((ord($hash[$offset+2]) & 0xff) << 8)
                |  (ord($hash[$offset+3]) & 0xff)) % 1000000;
        return str_pad((string)$code, 6, '0', STR_PAD_LEFT);
    }

    private static function base32Decode(string $input): string {
        $map    = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $input  = strtoupper($input);
        $output = '';
        $v = 0; $vbits = 0;
        for ($i = 0, $l = strlen($input); $i < $l; $i++) {
            $p = strpos($map, $input[$i]);
            if ($p === false) continue;
            $v     = ($v << 5) | $p;
            $vbits += 5;
            if ($vbits >= 8) { $vbits -= 8; $output .= chr($v >> $vbits); }
        }
        return $output;
    }
}
