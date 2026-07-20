<?php
// ============================================================
//  NexVest — Router
//  routes/web.php
// ============================================================

declare(strict_types=1);

class Router {
    private static array $routes = [];

    public static function get(string $path, callable|array $handler): void {
        self::$routes['GET'][$path] = $handler;
    }

    public static function post(string $path, callable|array $handler): void {
        self::$routes['POST'][$path] = $handler;
    }

    public static function dispatch(): void {
        $method   = $_SERVER['REQUEST_METHOD'];
        $uri      = strtok(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '?');
        $uri      = '/' . trim($uri, '/');
        // Strip subdirectory base path so app works in both root and subdir installs
        $base = rtrim(parse_url(CONFIG['app']['url'] ?? '', PHP_URL_PATH) ?? '', '/');
        if ($base !== '' && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base)) ?: '/';
        }
        $uri = '/' . trim($uri, '/');

        // Exact match
        if (isset(self::$routes[$method][$uri])) {
            self::call(self::$routes[$method][$uri]);
            return;
        }

        // Pattern match (e.g. /admin/users/{id})
        foreach (self::$routes[$method] ?? [] as $route => $handler) {
            $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $route);
            $pattern = '#^' . $pattern . '$#';
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                // Inject matches into $_GET
                preg_match_all('/\{([^}]+)\}/', $route, $keys);
                foreach ($keys[1] as $i => $key) {
                    $_GET[$key] = $matches[$i] ?? null;
                }
                self::call($handler);
                return;
            }
        }

        http_response_code(404);
        view('errors.404', ['title' => 'Page Not Found'], 'minimal');
    }

    private static function call(callable|array $handler): void {
        if (is_array($handler)) {
            [$class, $method] = $handler;
            (new $class())->$method();
        } else {
            $handler();
        }
    }
}

// ─────────────────────────────────────────────────────────────
//  ROUTES
// ─────────────────────────────────────────────────────────────

// ── Auth (Guest) ──────────────────────────────────────────────
Router::get('/login',                    [AuthController::class, 'showLogin']);
Router::post('/login',                   [AuthController::class, 'login']);
Router::post('/login/2fa',               [AuthController::class, 'verify2FA']);
Router::get('/register',                 [AuthController::class, 'showRegister']);
Router::post('/register',                [AuthController::class, 'register']);
Router::get('/verify-email',             [AuthController::class, 'showVerifyEmail']);
Router::post('/verify-email',            [AuthController::class, 'verifyEmail']);
Router::post('/verify-email/resend',     [AuthController::class, 'resendVerification']);
Router::get('/forgot-password',          [AuthController::class, 'showForgotPassword']);
Router::post('/forgot-password',         [AuthController::class, 'forgotPassword']);
Router::get('/reset-password',           [AuthController::class, 'showResetPassword']);
Router::post('/reset-password',          [AuthController::class, 'resetPassword']);
Router::post('/logout',                  [AuthController::class, 'logout']);

// ── Public legal pages (no auth required) ─────────────────────
Router::get('/terms',                    [InvestorController::class, 'terms']);
Router::get('/privacy',                  [InvestorController::class, 'privacy']);

// ── Investor (Authenticated) ──────────────────────────────────
Router::get('/investor/dashboard',       [InvestorController::class, 'dashboard']);
Router::get('/investor/kyc',             [InvestorController::class, 'showKyc']);
Router::post('/investor/kyc',            [InvestorController::class, 'submitKyc']);
Router::get('/investor/setup-2fa',       [InvestorController::class, 'show2FASetup']);
Router::post('/investor/setup-2fa',      [InvestorController::class, 'enable2FA']);
Router::get('/investor/investments',     [InvestorController::class, 'investments']);
Router::get('/investor/investments/{id}',[InvestorController::class, 'investmentDetail']);
Router::post('/investor/invest',         [InvestorController::class, 'invest']);
Router::get('/investor/portfolio',       [InvestorController::class, 'portfolio']);
Router::get('/investor/wallet',          [InvestorController::class, 'wallet']);
Router::post('/investor/deposit',        [InvestorController::class, 'initiateDeposit']);
Router::post('/investor/deposit/confirm',[InvestorController::class, 'confirmDeposit']);
Router::post('/investor/withdraw',       [InvestorController::class, 'requestWithdrawal']);
Router::get('/investor/transfer/lookup',[InvestorController::class, 'lookupTransferRecipient']);
Router::post('/investor/transfer',      [InvestorController::class, 'walletTransfer']);
Router::get('/investor/transactions',    [InvestorController::class, 'transactions']);
Router::get('/investor/notifications',   [InvestorController::class, 'notifications']);
Router::post('/investor/notifications/read', [InvestorController::class, 'markNotificationsRead']);
Router::get('/investor/support',         [InvestorController::class, 'support']);
Router::post('/investor/support/ticket', [InvestorController::class, 'createTicket']);
Router::post('/investor/wire-request',   [InvestorController::class, 'wireRequest']);
Router::post('/investor/support/reply',  [InvestorController::class, 'replyTicket']);
Router::get('/investor/profile',         [InvestorController::class, 'profile']);
Router::post('/investor/profile',        [InvestorController::class, 'updateProfile']);
Router::post('/investor/profile/password',[InvestorController::class, 'changePassword']);
Router::get('/investor/referrals',       [InvestorController::class, 'referrals']);
Router::get('/investor/certificates',    [InvestorController::class, 'certificates']);
Router::get('/investor/certificate/{ref}',[InvestorController::class, 'downloadCertificate']);
Router::get('/investor/calculator',      [InvestorController::class, 'calculator']);
Router::post('/investor/terminate',      [InvestorController::class, 'terminateInvestment']);
Router::get('/investor/data-export',     [InvestorController::class, 'dataExport']);
Router::post('/investor/request-deletion',[InvestorController::class, 'requestDeletion']);
Router::post('/investor/reinvest',       [InvestorController::class, 'toggleReinvest']);
Router::post('/investor/topup',          [InvestorController::class, 'topUp']);
Router::get('/investor/compliance',      [InvestorController::class, 'compliance']);
Router::get('/legal',                    [InvestorController::class, 'compliance']);
Router::get('/investor/invoices',        [InvestorController::class, 'invoices']);
Router::get('/investor/invoices/{ref}',  [InvestorController::class, 'invoiceDetail']);
Router::post('/investor/invoices/{ref}/pay',         [InvestorController::class, 'payInvoice']);
Router::post('/investor/invoices/{ref}/pay-balance', [InvestorController::class, 'payInvoiceBalance']);

// ── Admin ─────────────────────────────────────────────────────
Router::get('/admin/login',              [AdminController::class, 'showLogin']);
Router::post('/admin/login',             [AdminController::class, 'login']);
Router::post('/admin/logout',            [AdminController::class, 'logout']);
Router::get('/admin/dashboard',          [AdminController::class, 'dashboard']);
Router::get('/admin/users',              [AdminController::class, 'users']);
Router::get('/admin/users/{id}',         [AdminController::class, 'userDetail']);
Router::post('/admin/users/{id}',        [AdminController::class, 'updateUser']);
Router::post('/admin/users/{id}/credit', [AdminController::class, 'creditWallet']);
Router::post('/admin/users/{id}/suspend',[AdminController::class, 'suspendUser']);
Router::get('/admin/ghost/{id}',         [AdminController::class, 'ghostLogin']);
Router::get('/admin/ghost/exit',         [AdminController::class, 'exitGhost']);
Router::get('/admin/kyc',                [AdminController::class, 'kycQueue']);
Router::get('/admin/kyc/{id}',           [AdminController::class, 'kycDetail']);
Router::post('/admin/kyc/{id}/approve',  [AdminController::class, 'approveKyc']);
Router::post('/admin/kyc/{id}/reject',   [AdminController::class, 'rejectKyc']);
Router::get('/admin/investments',        [AdminController::class, 'investments']);
Router::get('/admin/investments/create', [AdminController::class, 'createInvestment']);
Router::post('/admin/investments/create',[AdminController::class, 'storeInvestment']);
Router::get('/admin/investments/{id}/edit',[AdminController::class, 'editInvestment']);
Router::post('/admin/investments/{id}',  [AdminController::class, 'updateInvestment']);
Router::post('/admin/investments/{id}/delete',[AdminController::class, 'deleteInvestment']);
Router::get('/admin/deposits',                      [AdminController::class, 'deposits']);
Router::post('/admin/deposits/{id}/approve',        [AdminController::class, 'approveDeposit']);
Router::post('/admin/deposits/{id}/reject',         [AdminController::class, 'rejectDeposit']);
Router::get('/admin/withdrawals',        [AdminController::class, 'withdrawals']);
Router::post('/admin/withdrawals/{id}/approve',[AdminController::class, 'approveWithdrawal']);
Router::post('/admin/withdrawals/{id}/reject', [AdminController::class, 'rejectWithdrawal']);
Router::post('/admin/withdrawals/{id}/complete',[AdminController::class, 'completeWithdrawal']);
Router::get('/admin/tickets',            [AdminController::class, 'tickets']);
Router::get('/admin/tickets/{id}',       [AdminController::class, 'ticketDetail']);
Router::post('/admin/tickets/{id}/reply',[AdminController::class, 'replyTicket']);
Router::post('/admin/tickets/{id}/close',[AdminController::class, 'closeTicket']);
Router::get('/admin/audit',              [AdminController::class, 'auditLog']);
Router::get('/admin/settings',           [AdminController::class, 'settings']);
Router::post('/admin/settings',          [AdminController::class, 'saveSettings']);
Router::post('/admin/announce',          [AdminController::class, 'sendAnnouncement']);
Router::post('/admin/settings/test-smtp',[AdminController::class, 'testSmtp']);
Router::post('/admin/users/{id}/email',  [AdminController::class, 'emailUser']);
Router::get('/admin/reports',            [AdminController::class, 'reports']);
Router::get('/admin/invoices',           [AdminController::class, 'adminInvoices']);
Router::post('/admin/invoices',          [AdminController::class, 'issueInvoice']);
Router::post('/admin/invoices/{id}/cancel', [AdminController::class, 'cancelInvoice']);

// ── Public ────────────────────────────────────────────────────
Router::get('/',         fn() => redirect(platform_setting('logout_redirect_url','') ?: '/login'));
Router::get('/verify',   [PublicController::class, 'verifyCertificate']);
