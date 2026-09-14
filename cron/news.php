<?php
/**
 * NexVest — Real-estate news cron
 * Run daily:  0 7 * * *  php /home/USER/DOCROOT/cron/news.php
 *
 * 1) Removes news posts older than 7 days (expired).
 * 2) Publishes ONE new auto post from the configured RSS feed (at most one
 *    auto post per day — re-running is safe/idempotent).
 */
declare(strict_types=1);

define('ROOT', dirname(__DIR__));
define('CRON', true);

require_once ROOT . '/config/config.php';
require_once ROOT . '/config/database.php';
require_once ROOT . '/app/helpers/helpers.php';

$log = fn(string $m) => print('[' . date('Y-m-d H:i:s') . '] ' . $m . PHP_EOL);
$log('=== News Cron Started ===');

// 1) Purge expired posts
$deleted = DB::execute("DELETE FROM news_posts WHERE expires_at IS NOT NULL AND expires_at <= NOW()");
$log("Removed {$deleted} expired post(s).");

if (platform_setting('news_enabled', '1') !== '1') {
    $log('News feature disabled — skipping fetch.');
    $log('=== News Cron Completed ==='); return;
}

// 2) One auto post per day
$todayCount = (int) (DB::fetch("SELECT COUNT(*) c FROM news_posts WHERE is_manual=0 AND DATE(published_at)=CURDATE()")['c'] ?? 0);
if ($todayCount > 0) {
    $log('An auto post already exists for today — nothing to publish.');
    $log('=== News Cron Completed ==='); return;
}

$n = news_pull(1);
$log($n > 0 ? "Published {$n} new post." : 'No new items to publish.');
$log('=== News Cron Completed ===');
