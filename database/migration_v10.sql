-- Migration v10 — reminder email send-tracking (prevents duplicate reminders)
CREATE TABLE IF NOT EXISTS `email_reminders` (
  `id`       INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`  INT UNSIGNED NOT NULL,
  `type`     VARCHAR(40)  NOT NULL,
  `ref`      VARCHAR(60)  DEFAULT NULL,
  `sent_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_er_user_type` (`user_id`, `type`),
  KEY `idx_er_type_ref` (`type`, `ref`),
  CONSTRAINT `fk_er_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
