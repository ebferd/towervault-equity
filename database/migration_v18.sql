-- ============================================================
--  NexVest — Migration v18
--  Automated real-estate news (pulled daily from an RSS feed,
--  auto-removed after 7 days, editable/deletable in admin).
--  Run once in phpMyAdmin against the live database.
-- ============================================================

CREATE TABLE IF NOT EXISTS `news_posts` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`        VARCHAR(400)  NOT NULL,
  `summary`      TEXT          DEFAULT NULL,
  `source_name`  VARCHAR(120)  DEFAULT NULL,
  `source_url`   VARCHAR(700)  DEFAULT NULL,
  `category`     VARCHAR(60)   NOT NULL DEFAULT 'Global',
  `image`        VARCHAR(500)  DEFAULT NULL,
  `guid_hash`    CHAR(40)      DEFAULT NULL,      -- sha1 of the feed item id, to de-dupe
  `is_manual`    TINYINT(1)    NOT NULL DEFAULT 0, -- 1 = admin-created
  `status`       ENUM('published','hidden') NOT NULL DEFAULT 'published',
  `published_at` DATETIME      NOT NULL,
  `expires_at`   DATETIME      DEFAULT NULL,        -- auto-removed after this time
  `created_at`   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_guid` (`guid_hash`),
  KEY `idx_news_pub` (`status`, `published_at`),
  KEY `idx_news_exp` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default RSS feeds (top global outlets with real summaries + images) — editable in admin.
-- One feed per line; items across all feeds are merged and the newest is published.
INSERT INTO `platform_settings` (`setting_key`,`setting_value`,`setting_group`) VALUES
 ('news_rss_url','https://rss.nytimes.com/services/xml/rss/nyt/RealEstate.xml\nhttps://www.theguardian.com/money/property/rss','general'),
 ('news_enabled','1','general')
ON DUPLICATE KEY UPDATE `setting_value`=`setting_value`;
