<?php
declare(strict_types=1);
// ============================================================
//  NexVest — Helper Functions
//  app/helpers/helpers.php
// Guard against double-loading (Composer + manual require)
// ============================================================
if (function_exists('csrf_token')) return;

// ── Security ──────────────────────────────────────────────────

function csrf_token(): string {
    if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_token_time']) ||
        (time() - $_SESSION['csrf_token_time']) > CONFIG['security']['csrf_lifetime']) {
        $_SESSION['csrf_token']      = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
}

function verify_csrf(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function sanitize(string $value): string {
    return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
}

function sanitize_email(string $email): string|false {
    return filter_var(trim($email), FILTER_SANITIZE_EMAIL) ?: false;
}

function hash_password(string $password): string {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => CONFIG['security']['bcrypt_rounds']]);
}

function verify_password(string $password, string $hash): bool {
    return password_verify($password, $hash);
}

function validate_password(string $password): bool {
    return strlen($password) >= 8 &&
           preg_match('/[A-Z]/', $password) &&
           preg_match('/[a-z]/', $password) &&
           preg_match('/[0-9]/', $password);
}

function generate_token(int $length = 64): string {
    return bin2hex(random_bytes($length / 2));
}

function generate_otp(): string {
    return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

function generate_reference(string $prefix = 'NV'): string {
    // Higher entropy (10 hex chars) + a uniqueness guard against existing
    // transaction references so we never trip the unique key and fail a transfer.
    for ($i = 0; $i < 8; $i++) {
        $ref = strtoupper($prefix . '-' . substr(bin2hex(random_bytes(6)), 0, 10));
        try {
            // References may be suffixed (e.g. -S / -R for transfers), so match the base as a prefix.
            $exists = DB::fetch("SELECT 1 FROM transactions WHERE reference LIKE ? LIMIT 1", [$ref . '%']);
        } catch (\Throwable $e) {
            return $ref; // table not available yet — high-entropy value is safe to use
        }
        if (!$exists) return $ref;
    }
    // Astronomically unlikely fallback: add extra entropy.
    return strtoupper($prefix . '-' . substr(bin2hex(random_bytes(8)), 0, 14));
}

function generate_referral_code(string $firstName, string $lastName): string {
    $base = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
    return 'NV-' . $base . strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));
}

function generate_cert_ref(): string {
    return 'INV-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
}

// ── Auth ──────────────────────────────────────────────────────

function is_logged_in(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function is_admin(): bool {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function is_super_admin(): bool {
    return is_admin() && ($_SESSION['admin_role'] ?? '') === 'super_admin';
}

function current_user_id(): ?int {
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

function current_admin_id(): ?int {
    return isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null;
}

function require_login(): void {
    if (!is_logged_in()) redirect('/login');
}

function require_admin(): void {
    if (!is_admin()) redirect('/admin/login');
}

function require_super_admin(): void {
    if (!is_super_admin()) {
        http_response_code(403);
        die('Access denied.');
    }
}

function require_kyc(): void {
    if (!is_logged_in()) redirect('/login');
    $setting = platform_setting('kyc_enabled', '1');
    if ($setting === '1' && ($_SESSION['kyc_status'] ?? '') !== 'verified') {
        redirect('/investor/kyc');
    }
}

// ── Redirect & Response ───────────────────────────────────────

function redirect(string $url, int $code = 302): never {
    header('Location: ' . $url, true, $code);
    exit;
}

function json_response(array $data, int $code = 200): never {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function abort(int $code, string $message = ''): never {
    http_response_code($code);
    echo $message ?: "HTTP Error $code";
    exit;
}

/**
 * Read a request input value from either $_POST or a JSON request body.
 * app.js post() sends plain objects as JSON (Content-Type: application/json),
 * which does not populate $_POST. This helper transparently handles both cases.
 */
function input(string $key, mixed $default = null): mixed {
    static $jsonBody = null;
    if (!isset($jsonBody)) {
        $raw = file_get_contents('php://input');
        $jsonBody = ($raw && str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json'))
            ? (json_decode($raw, true) ?? [])
            : [];
    }
    return $_POST[$key] ?? $jsonBody[$key] ?? $default;
}

// ── Flash Messages ────────────────────────────────────────────

function flash(string $key, string $message): void {
    $_SESSION['flash'][$key] = $message;
}

function get_flash(string $key): ?string {
    $msg = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $msg;
}

function has_flash(string $key): bool {
    return isset($_SESSION['flash'][$key]);
}

// ── Formatting ────────────────────────────────────────────────

function fmt_currency(float $amount, ?string $symbol = null): string {
    $sym = $symbol ?? platform_setting('platform_symbol', '$');
    return $sym . number_format($amount, 2);
}

function fmt_date(?string $date, string $format = 'M j, Y'): string {
    if (empty($date)) return '—';
    $ts = strtotime($date);
    return $ts ? date($format, $ts) : '—';
}

function fmt_datetime(?string $datetime, string $format = 'M j, Y g:i A'): string {
    if (empty($datetime)) return '—';
    $ts = strtotime($datetime);
    return $ts ? date($format, $ts) : '—';
}

function time_ago(?string $datetime): string {
    if (empty($datetime)) return '—';
    $diff = time() - strtotime($datetime);
    if ($diff < 60)     return 'Just now';
    if ($diff < 3600)   return floor($diff / 60) . 'm ago';
    if ($diff < 86400)  return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return fmt_date($datetime);
}

function truncate(string $str, int $length = 100, string $append = '…'): string {
    return strlen($str) > $length ? substr($str, 0, $length) . $append : $str;
}

// ── Platform Settings ─────────────────────────────────────────

function platform_setting(string $key, mixed $default = null): mixed {
    static $cache = [];
    if (!isset($cache[$key])) {
        $row = DB::fetch("SELECT setting_value FROM platform_settings WHERE setting_key = ?", [$key]);
        $cache[$key] = $row ? $row['setting_value'] : $default;
    }
    return $cache[$key] ?? $default;
}

function platform_settings_group(string $group): array {
    $rows = DB::fetchAll("SELECT setting_key, setting_value FROM platform_settings WHERE setting_group = ?", [$group]);
    return array_column($rows, 'setting_value', 'setting_key');
}

/**
 * Output third-party live-chat / widget code (e.g. Smartsupp) saved in admin
 * settings. Printed raw and unescaped — it is trusted admin-entered markup.
 */
function render_live_chat(): void {
    $code = platform_setting('smartsupp_code', '');
    if (is_string($code) && trim($code) !== '') {
        echo "\n" . $code . "\n";
    }
}

// ── File Upload ───────────────────────────────────────────────

function is_absolute_path(string $path): bool {
    return str_starts_with($path, '/') || (strlen($path) > 1 && $path[1] === ':');
}

function upload_file(array $file, string $destDir, array $allowedTypes): string|false {
    if ($file['error'] !== UPLOAD_ERR_OK) return false;
    if ($file['size'] > CONFIG['upload']['max_size']) return false;

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedTypes, true)) return false;

    // Verify MIME type
    $allowedMimes = ['jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png',
                     'webp'=>'image/webp','pdf'=>'application/pdf'];
    if (!isset($allowedMimes[$ext])) return false;
    if (function_exists('finfo_open')) {
        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if ($allowedMimes[$ext] !== $mimeType) return false;
    } elseif (function_exists('mime_content_type')) {
        $mimeType = mime_content_type($file['tmp_name']);
        if ($allowedMimes[$ext] !== $mimeType) return false;
    }

    $fullPath = (is_absolute_path($destDir) ? $destDir : ROOT . '/' . trim($destDir, '/'));
    if (!is_dir($fullPath)) mkdir($fullPath, 0755, true);

    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $target   = $fullPath . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $target)) return false;
    // Always return a web-relative path (relative to ROOT) so it can be used directly in URLs.
    $rootNorm = rtrim(str_replace('\\', '/', ROOT), '/');
    $fullNorm = str_replace('\\', '/', $fullPath);
    $relDir   = ltrim(str_replace($rootNorm, '', $fullNorm), '/');
    return $relDir . '/' . $filename;
}

// Convert a stored file path (may be absolute filesystem path or web-relative) into a URL path.
function file_url(string $path): string {
    $pathNorm = str_replace('\\', '/', $path);
    $rootNorm = rtrim(str_replace('\\', '/', ROOT), '/');
    // Strip ROOT prefix if present
    if (str_starts_with($pathNorm, $rootNorm)) {
        $pathNorm = ltrim(substr($pathNorm, strlen($rootNorm)), '/');
    }
    // Get base path from APP_URL (e.g. "/nexvest")
    $baseUrl  = CONFIG['app']['url'];
    $basePath = rtrim(parse_url($baseUrl, PHP_URL_PATH) ?? '', '/');
    return $basePath . '/' . ltrim($pathNorm, '/');
}

// ── Investment calculation helpers ────────────────────────────

function calc_maturity_date(string $startDate, int $durationValue, string $durationUnit): string {
    $date = new DateTime($startDate);
    match ($durationUnit) {
        'days'   => $date->modify("+{$durationValue} days"),
        'weeks'  => $date->modify("+{$durationValue} weeks"),
        'months' => $date->modify("+{$durationValue} months"),
        'years'  => $date->modify("+{$durationValue} years"),
        default  => $date->modify("+{$durationValue} months"),
    };
    return $date->format('Y-m-d');
}

/**
 * ROI model: `roiPercent` is the TOTAL return earned over the FULL duration
 * (e.g. 30% for a 2-month plan means 30% total across those 2 months — NOT
 * per year). Payouts simply distribute that total across the duration
 * according to the chosen frequency.
 */

// Convert a duration to a number of days.
function duration_to_days(int $durationValue, string $durationUnit): float {
    return match ($durationUnit) {
        'years'  => $durationValue * 365,
        'months' => $durationValue * 30.4375,
        'weeks'  => $durationValue * 7,
        'days'   => $durationValue,
        default  => $durationValue * 30.4375,
    };
}

// How many payout periods fit in the duration for a given frequency.
function payout_periods(int $durationValue, string $durationUnit, string $frequency): float {
    if ($frequency === 'at_maturity') return 1.0;
    $days = duration_to_days($durationValue, $durationUnit);
    $periodDays = match ($frequency) {
        'daily'       => 1,
        'weekly'      => 7,
        'monthly'     => 30.4375,
        'quarterly'   => 91.3125,
        'semi_annual' => 182.625,
        default       => 30.4375,
    };
    return max(1.0, $days / $periodDays);
}

// The amount paid at each payout: total return spread across the periods.
function calc_period_return(float $amount, float $roiPercent, string $frequency, int $durationValue, string $durationUnit): float {
    $total   = $amount * ($roiPercent / 100);
    $periods = payout_periods($durationValue, $durationUnit, $frequency);
    return $periods > 0 ? $total / $periods : $total;
}

// Total return over the full duration = amount × roi/100 (roi is already the total).
function calc_total_return(float $amount, float $roiPercent): float {
    return $amount * ($roiPercent / 100);
}

// ── Pagination ────────────────────────────────────────────────

function paginate(string $countSql, string $dataSql, array $params, int $page, int $perPage = 20): array {
    $row   = DB::fetch($countSql, $params);
    $total = $row ? (int) ($row['total'] ?? 0) : 0;
    $pages = $total > 0 ? (int) ceil($total / $perPage) : 1;
    $page  = max(1, min($page, $pages));
    $offset = ($page - 1) * $perPage;

    $data = DB::fetchAll($dataSql . " LIMIT ? OFFSET ?", [...$params, $perPage, $offset]);

    return compact('data', 'total', 'pages', 'page', 'perPage');
}

// ── IP Address ────────────────────────────────────────────────

function get_ip(): string {
    foreach (['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','REMOTE_ADDR'] as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = explode(',', $_SERVER[$key])[0];
            if (filter_var(trim($ip), FILTER_VALIDATE_IP)) return trim($ip);
        }
    }
    return '0.0.0.0';
}

// ── Notifications (in-app) ────────────────────────────────────

function create_notification(int $userId, string $type, string $title, string $message, array $data = []): void {
    DB::query(
        "INSERT INTO notifications (user_id, type, title, message, data) VALUES (?,?,?,?,?)",
        [$userId, $type, $title, $message, $data ? json_encode($data) : null]
    );
}

// ── Audit log ────────────────────────────────────────────────

function audit_log(
    int     $adminId,
    string  $action,
    string  $detail,
    string  $severity   = 'medium',
    ?string $targetType = null,
    ?int    $targetId   = null,
    ?string $targetName = null,
    array  $oldValue   = [],
    array  $newValue   = []
): void {
    try {
        DB::query(
            "INSERT INTO audit_logs (admin_id, action, target_type, target_id, target_name, detail, old_value, new_value, severity, ip_address, user_agent)
             VALUES (?,?,?,?,?,?,?,?,?,?,?)",
            [
                $adminId, $action, $targetType, $targetId, $targetName, $detail,
                $oldValue ? json_encode($oldValue) : null,
                $newValue ? json_encode($newValue) : null,
                $severity, get_ip(),
                substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            ]
        );
    } catch (\Throwable $e) {
        error_log('audit_log failed: ' . $e->getMessage());
    }
}

// ── View renderer ─────────────────────────────────────────────

function view(string $template, array $_viewData = [], string $layout = 'main'): void {
    $base = defined('ROOT') ? ROOT : dirname(__DIR__, 2);

    // Always load component helpers first so svgIcon(), badge() etc
    // are available in ALL templates and layouts
    $componentHelpers = $base . '/views/components/helpers.php';
    if (file_exists($componentHelpers) && !function_exists('svgIcon')) {
        require_once $componentHelpers;
    }

    extract($_viewData, EXTR_SKIP);

    ob_start();
    $tplFile = $base . '/views/' . str_replace('.', '/', $template) . '.php';
    if (!file_exists($tplFile)) abort(500, "View not found: {$template}");
    require $tplFile;
    $_rendered = ob_get_clean();

    if ($layout) {
        $content   = $_rendered; // layouts reference $content for the page body
        $layoutFile = $base . '/views/layouts/' . $layout . '.php';
        if (file_exists($layoutFile)) { require $layoutFile; }
        else echo $_rendered;
    } else {
        echo $_rendered;
    }
}
