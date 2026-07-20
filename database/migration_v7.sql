-- ============================================================
--  NexVest Migration v7 — Wallet Transfers
--  Run this on existing installations via phpMyAdmin
-- ============================================================

-- 1. Extend transactions ENUM to include transfer types
ALTER TABLE `transactions`
  MODIFY COLUMN `type`
    ENUM('deposit','withdrawal','investment','return','referral_commission','adjustment','debit','transfer_sent','transfer_received')
    NOT NULL;

-- 2. Create wallet_transfers table
CREATE TABLE IF NOT EXISTS `wallet_transfers` (
  `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `reference`   VARCHAR(60)     NOT NULL,
  `sender_id`   INT UNSIGNED    NOT NULL,
  `receiver_id` INT UNSIGNED    NOT NULL,
  `amount`      DECIMAL(15,2)   NOT NULL,
  `note`        VARCHAR(255)    DEFAULT NULL,
  `sender_tx_id`   INT UNSIGNED DEFAULT NULL,
  `receiver_tx_id` INT UNSIGNED DEFAULT NULL,
  `created_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_wt_reference` (`reference`),
  KEY `idx_wt_sender`   (`sender_id`),
  KEY `idx_wt_receiver` (`receiver_id`),
  CONSTRAINT `fk_wt_sender`   FOREIGN KEY (`sender_id`)      REFERENCES `users`        (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_wt_receiver` FOREIGN KEY (`receiver_id`)    REFERENCES `users`        (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_wt_stx`      FOREIGN KEY (`sender_tx_id`)   REFERENCES `transactions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_wt_rtx`      FOREIGN KEY (`receiver_tx_id`) REFERENCES `transactions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
