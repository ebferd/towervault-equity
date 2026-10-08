-- ============================================================
--  NexVest — Migration v19
--  Marketing improvements:
--   • Bounces: addresses the mail server rejects are added to the
--     suppression list (reason = 'bounced') and never emailed again.
--   • Click tracking on the email's buttons.
--   • Frequency limit: skip people emailed in the last N days.
--  Run once in phpMyAdmin against the live database.
-- ============================================================

ALTER TABLE `marketing_unsubscribes`
  ADD COLUMN `reason` ENUM('unsubscribed','bounced') NOT NULL DEFAULT 'unsubscribed' AFTER `email`,
  ADD COLUMN `detail` VARCHAR(255) DEFAULT NULL AFTER `reason`;

ALTER TABLE `marketing_recipients`
  ADD COLUMN `clicked_at`  DATETIME     DEFAULT NULL AFTER `open_count`,
  ADD COLUMN `click_count` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `clicked_at`,
  ADD KEY `idx_mr_email_sent` (`email`, `sent_at`);

-- 0 turns the frequency limit off. Editable on the Marketing page.
INSERT IGNORE INTO `platform_settings` (`setting_key`, `setting_value`, `setting_group`) VALUES
 ('marketing_frequency_days', '3', 'email');
