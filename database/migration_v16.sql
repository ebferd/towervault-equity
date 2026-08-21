-- ============================================================
--  NexVest — Migration v16
--  Marketing open-tracking. One row per recipient per campaign,
--  with a unique token embedded in a tracking pixel so we can
--  record when (and how often) an email is opened.
--  Run once in phpMyAdmin against the live database.
-- ============================================================

CREATE TABLE IF NOT EXISTS `marketing_recipients` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `campaign_id`    INT UNSIGNED NOT NULL,
  `email`          VARCHAR(255) NOT NULL,
  `token`          CHAR(32)     NOT NULL,
  `sent_at`        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `opened_at`      DATETIME     DEFAULT NULL,
  `last_opened_at` DATETIME     DEFAULT NULL,
  `open_count`     INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_mr_token` (`token`),
  KEY `idx_mr_campaign` (`campaign_id`),
  KEY `idx_mr_opened` (`opened_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
