-- ============================================================
--  NexVest — Migration v11
--  Marketing / cold-outreach campaigns (to potential clients)
--  Run once in phpMyAdmin against the live database.
-- ============================================================

-- Log of every campaign sent from Admin → Marketing
CREATE TABLE IF NOT EXISTS `marketing_campaigns` (
  `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `subject`            VARCHAR(255) NOT NULL,
  `headline`           VARCHAR(255) DEFAULT NULL,
  `body`               LONGTEXT     NOT NULL,
  `featured_id`        INT UNSIGNED DEFAULT NULL,
  `cta_label`          VARCHAR(120) DEFAULT NULL,
  `cta_url`            VARCHAR(500) DEFAULT NULL,
  `recipient_count`    INT UNSIGNED NOT NULL DEFAULT 0,
  `sent_count`         INT UNSIGNED NOT NULL DEFAULT 0,
  `failed_count`       INT UNSIGNED NOT NULL DEFAULT 0,
  `sent_by`            INT UNSIGNED DEFAULT NULL,
  `created_at`         TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_mc_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Suppression list: anyone who unsubscribes from marketing outreach
CREATE TABLE IF NOT EXISTS `marketing_unsubscribes` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email`       VARCHAR(255) NOT NULL,
  `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_mu_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
