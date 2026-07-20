<?php
declare(strict_types=1);

function env(string $key, mixed $default = null): mixed {
    $val = $_ENV[$key] ?? getenv($key);
    return ($val !== false && $val !== null) ? $val : $default;
}

// Load .env from same folder as index.php
$envFile = (defined('ROOT') ? ROOT : __DIR__ . '/..') . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if (!$line || $line[0] === '#' || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $k = trim($k); $v = trim($v, " \t\"'");
        $_ENV[$k] = $v;
        putenv("$k=$v");
    }
}

if (env('APP_DEBUG', 'false') === 'true') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));

define('CONFIG', [
    'app' => [
        'name'     => env('APP_NAME', 'NexVest Capital Group'),
        'url'      => env('APP_URL',  'https://yourdomain.com'),
        'debug'    => env('APP_DEBUG','false') === 'true',
        'key'      => env('APP_KEY',  ''),
    ],
    'db' => [
        'host'    => env('DB_HOST', 'localhost'),
        'port'    => (int) env('DB_PORT', '3306'),
        'name'    => env('DB_NAME', ''),
        'user'    => env('DB_USER', ''),
        'pass'    => env('DB_PASS', ''),
        'charset' => 'utf8mb4',
    ],
    'mail' => [
        'host'       => env('SMTP_HOST', ''),
        'port'       => (int) env('SMTP_PORT', '587'),
        'secure'     => env('SMTP_SECURE', 'tls'),
        'user'       => env('SMTP_USER', ''),
        'pass'       => env('SMTP_PASS', ''),
        'from_email' => env('SMTP_FROM_EMAIL', ''),
        'from_name'  => env('SMTP_FROM_NAME',  'NexVest'),
        'support'    => env('MAIL_SUPPORT',     ''),
    ],
    'session' => [
        'name'      => env('SESSION_NAME',      'nexvest_sess'),
        'lifetime'  => (int) env('SESSION_LIFETIME', '7200'),
        'save_path' => env('SESSION_SAVE_PATH', ''),
    ],
    'security' => [
        'bcrypt_rounds'         => (int) env('BCRYPT_ROUNDS',         '12'),
        'max_login_attempts'    => (int) env('MAX_LOGIN_ATTEMPTS',    '5'),
        'lockout_minutes'       => (int) env('LOGIN_LOCKOUT_MINUTES', '15'),
        'reset_expire_minutes'  => (int) env('PASSWORD_RESET_EXPIRE', '30'),
        'verify_expire_minutes' => (int) env('EMAIL_VERIFY_EXPIRE',   '15'),
        'deposit_timeout'       => (int) env('DEPOSIT_TIMEOUT',       '1800'),
        'csrf_lifetime'         => (int) env('CSRF_LIFETIME',         '7200'),
    ],
    'upload' => [
        'max_size'  => 5242880,
        'kyc_path'  => ROOT . '/uploads/kyc/',
        'inv_path'  => ROOT . '/uploads/investments/',
        'logo_path' => ROOT . '/uploads/logos/',
        'img_types' => ['jpg','jpeg','png','webp'],
        'doc_types' => ['pdf','jpg','jpeg','png'],
    ],
    'referral' => [
        'commission_rate' => (float) env('REFERRAL_COMMISSION_RATE', '5'),
    ],
]);
