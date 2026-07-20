-- NexVest Migration v6 — Admin-issued payment invoices
CREATE TABLE IF NOT EXISTS `admin_invoices` (
  `id`             INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  `user_id`        INT UNSIGNED   NOT NULL,
  `admin_id`       INT UNSIGNED   NOT NULL,
  `reference`      VARCHAR(20)    NOT NULL,
  `title`          VARCHAR(180)   NOT NULL,
  `description`    TEXT           DEFAULT NULL,
  `amount`         DECIMAL(15,2)  NOT NULL,
  `due_date`       DATE           NOT NULL,
  `payment_method` ENUM('any','crypto','paypal','wire') NOT NULL DEFAULT 'any',
  `status`         ENUM('pending','paid','cancelled')   NOT NULL DEFAULT 'pending',
  `deposit_ref`    VARCHAR(20)    DEFAULT NULL,
  `paid_at`        DATETIME       DEFAULT NULL,
  `cancelled_at`   DATETIME       DEFAULT NULL,
  `created_at`     TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ai_ref` (`reference`),
  KEY `idx_ai_user`   (`user_id`),
  KEY `idx_ai_status` (`status`),
  CONSTRAINT `fk_ai_user`  FOREIGN KEY (`user_id`)  REFERENCES `users`  (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ai_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
