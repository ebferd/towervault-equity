<?php
/**
 * NexVest — Entry Point
 * Upload this entire folder to your domain root and visit /install.php
 * No path editing required.
 */
declare(strict_types=1);

define('ROOT', __DIR__);

// Error logging — write to storage/logs, never display on screen
@ini_set('display_errors', '0');
@ini_set('log_errors', '1');
@ini_set('error_log', __DIR__ . '/storage/logs/php_error.log');
error_reporting(E_ALL);

// Global exception handler — JSON for AJAX, HTML page for browser requests
set_exception_handler(function (Throwable $e): void {
    $code = http_response_code();
    if ($code < 400) http_response_code(500);
    error_log('[NexVest] Uncaught: ' . get_class($e) . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());

    $isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest'
           || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');

    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'A server error occurred. Please try again.']);
        exit;
    }

    $msg  = htmlspecialchars($e->getMessage());
    $file = htmlspecialchars(str_replace(__DIR__, '', $e->getFile()));
    $line = $e->getLine();
    echo <<<HTML
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"/>
<title>Error — NexVest</title>
<style>body{font-family:system-ui,sans-serif;background:#0d1117;color:#e6edf3;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}
.box{max-width:640px;padding:2.5rem;background:#161b22;border:1px solid #30363d;border-radius:8px}
h1{color:#f85149;margin:0 0 1rem;font-size:1.25rem}
.badge{display:inline-block;background:#21262d;border:1px solid #30363d;border-radius:4px;font-family:monospace;font-size:11px;padding:2px 8px;color:#8b949e;margin-top:1rem}</style>
</head><body><div class="box">
<h1>&#9888; An error occurred</h1>
<p style="color:#8b949e;font-size:14px">{$msg}</p>
<div class="badge">{$file} : {$line}</div>
<p style="font-size:12px;color:#6e7681;margin-top:1.5rem">Check <code>storage/logs/php_error.log</code> for the full stack trace.</p>
</div></body></html>
HTML;
    exit;
});

// Autoloader
spl_autoload_register(function (string $class): void {
    foreach ([
        ROOT . '/app/controllers/' . $class . '.php',
        ROOT . '/app/middleware/'  . $class . '.php',
        ROOT . '/app/mail/'        . $class . '.php',
    ] as $path) {
        if (file_exists($path)) { require_once $path; return; }
    }
});

// Redirect to installer if not configured
$_envFile = ROOT . '/.env';
if (!file_exists($_envFile)) {
    header('Location: /install.php');
    exit;
}

// Bootstrap
require_once ROOT . '/config/config.php';
require_once ROOT . '/config/database.php';
require_once ROOT . '/app/helpers/helpers.php';
require_once ROOT . '/app/middleware/Auth.php';

// Composer (PHPMailer etc)
if (file_exists(ROOT . '/vendor/autoload.php')) {
    require_once ROOT . '/vendor/autoload.php';
}

// Start
AuthMiddleware::init();

// Maintenance mode — block non-admin requests
if (platform_setting('maintenance_mode', '0') === '1') {
    $uri      = strtok(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/', '?');
    $isAdmin  = str_starts_with($uri, '/admin') || !empty($_SESSION['admin_id']);
    $isAsset  = preg_match('/\.(css|js|png|jpg|ico|svg|woff2?)$/i', $uri);
    if (!$isAdmin && !$isAsset) {
        http_response_code(503);
        header('Retry-After: 3600');
        require ROOT . '/views/maintenance.php';
        exit;
    }
}

require_once ROOT . '/routes/web.php';
Router::dispatch();
