<?php
declare(strict_types=1);

class AuthMiddleware {

    public static function init(): void {
        if (session_status() !== PHP_SESSION_NONE) return;

        // Auto-use project storage/sessions directory to avoid cPanel /tmp issues
        $savePath = env('SESSION_SAVE_PATH', '');
        if (!$savePath && defined('ROOT')) {
            $autoPath = ROOT . '/storage/sessions';
            if (!is_dir($autoPath)) @mkdir($autoPath, 0700, true);
            if (is_dir($autoPath) && is_writable($autoPath)) {
                $savePath = $autoPath;
            }
        }
        if ($savePath) {
            if (!is_dir($savePath)) @mkdir($savePath, 0700, true);
            if (is_dir($savePath) && is_writable($savePath)) {
                session_save_path($savePath);
            }
        }

        $sessionName = env('SESSION_NAME', 'nexvest_sess');
        $lifetime    = (int) env('SESSION_LIFETIME', 7200);

        // Auto-detect HTTPS — covers cPanel proxies that set HTTP_X_FORWARDED_PROTO
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (int)($_SERVER['SERVER_PORT'] ?? 80) === 443
                || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'
                || ($_SERVER['HTTP_CF_VISITOR'] ?? '') === '{"scheme":"https"}'; // Cloudflare

        ini_set('session.cookie_httponly', '1');
        ini_set('session.use_strict_mode', '1');
        ini_set('session.cookie_samesite', 'Lax');
        ini_set('session.gc_maxlifetime', (string)$lifetime);

        session_name($sessionName);
        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path'     => '/',
            'domain'   => '',
            'secure'   => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();

        if (!headers_sent()) {
            header('X-Frame-Options: SAMEORIGIN');
            header('X-Content-Type-Options: nosniff');
            header('X-XSS-Protection: 1; mode=block');
        }
    }

    public static function investor(): void {
        self::init();
        if (!is_logged_in()) {
            flash('error', 'Please sign in to continue.');
            redirect('/login');
        }
        self::softCheckInvestor();
    }

    public static function admin(): void {
        self::init();
        if (!is_admin()) {
            flash('error', 'Administrator access required. Please sign in.');
            redirect('/admin/login');
        }
        self::softCheckAdmin();
    }

    public static function guest(): void {
        self::init();
        if (is_logged_in()) redirect('/investor/dashboard');
        if (is_admin())     redirect('/admin/dashboard');
    }

    public static function verifyCsrf(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $token = $_POST['_token']
              ?? $_SERVER['HTTP_X_CSRF_TOKEN']
              ?? '';

        if (empty($_SESSION['csrf_token'])) {
            json_response(['success' => false, 'error' => 'Session expired. Please refresh the page and try again.'], 419);
        }

        if (empty($token) || !hash_equals((string)$_SESSION['csrf_token'], (string)$token)) {
            json_response(['success' => false, 'error' => 'Security check failed. Please refresh and try again.'], 419);
        }
    }

    public static function rateLimit(string $key, int $max, int $windowSeconds = 60): void {
        $ip    = get_ip();
        $now   = time();
        $since = date('Y-m-d H:i:s', $now - $windowSeconds);

        if ($key === 'login') {
            $row   = DB::fetch("SELECT COUNT(*) as cnt FROM login_attempts WHERE ip_address=? AND created_at > ? AND success=0", [$ip, $since]);
            $count = (int)($row['cnt'] ?? 0);
            if ($count >= $max) {
                $mins = (int)env('LOGIN_LOCKOUT_MINUTES', 15);
                json_response(['success' => false, 'error' => "Too many failed attempts. Try again in {$mins} minutes."], 429);
            }
        } else {
            $cKey = 'rate_' . $key . '_' . md5($ip);
            if (!isset($_SESSION[$cKey]) || time() > ($_SESSION[$cKey]['reset'] ?? 0)) {
                $_SESSION[$cKey] = ['count' => 0, 'reset' => $now + $windowSeconds];
            }
            $_SESSION[$cKey]['count']++;
            if ($_SESSION[$cKey]['count'] > $max) {
                json_response(['success' => false, 'error' => 'Too many requests. Please slow down.'], 429);
            }
        }
    }

    private static function softCheckInvestor(): void {
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) { self::logout(); redirect('/login'); }

        $lastCheck = $_SESSION['_last_db_check'] ?? 0;
        if (time() - $lastCheck < 60) return;

        $user = DB::fetch("SELECT status, kyc_status FROM users WHERE id=?", [$userId]);
        if (!$user || in_array($user['status'], ['suspended', 'banned'])) {
            self::logout();
            flash('error', 'Your account has been suspended.');
            redirect('/login');
        }
        $_SESSION['kyc_status']     = $user['kyc_status'];
        $_SESSION['_last_db_check'] = time();
    }

    private static function softCheckAdmin(): void {
        $adminId = (int)($_SESSION['admin_id'] ?? 0);
        if (!$adminId) redirect('/admin/login');

        $lastCheck = $_SESSION['_admin_last_check'] ?? 0;
        if (time() - $lastCheck < 60) return;

        $admin = DB::fetch("SELECT id, is_active, role FROM admins WHERE id=?", [$adminId]);
        if (!$admin || !$admin['is_active']) { self::adminLogout(); redirect('/admin/login'); }
        $_SESSION['admin_role']        = $admin['role'];
        $_SESSION['_admin_last_check'] = time();
    }

    public static function logout(): void {
        $token = $_SESSION['session_token'] ?? null;
        if ($token) {
            try { DB::execute("DELETE FROM user_sessions WHERE session_token=?", [$token]); }
            catch (Exception $e) {}
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        if (session_status() === PHP_SESSION_ACTIVE) session_destroy();
    }

    public static function adminLogout(): void {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) session_destroy();
    }
}
