-- NexVest Migration Script
-- Run this ONCE on existing installations to add missing columns.
-- Safe to run multiple times (uses IF NOT EXISTS / IGNORE).

-- ── deposit_invoices: add missing columns ─────────────────────────────────

ALTER TABLE `deposit_invoices`
  MODIFY COLUMN `status` ENUM('pending','submitted','paid','rejected','expired','cancelled') NOT NULL DEFAULT 'pending';

ALTER TABLE `deposit_invoices`
  ADD COLUMN IF NOT EXISTS `holding_id`   INT UNSIGNED  DEFAULT NULL   AFTER `wallet_address`,
  ADD COLUMN IF NOT EXISTS `admin_note`   TEXT          DEFAULT NULL   AFTER `confirmed_at`,
  ADD COLUMN IF NOT EXISTS `reviewed_by`  INT UNSIGNED  DEFAULT NULL   AFTER `admin_note`,
  ADD COLUMN IF NOT EXISTS `reviewed_at`  DATETIME      DEFAULT NULL   AFTER `reviewed_by`;

-- Add FK for holding_id (skip if already exists)
SET @fk := (
  SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME   = 'deposit_invoices'
    AND COLUMN_NAME  = 'holding_id'
    AND REFERENCED_TABLE_NAME IS NOT NULL
  LIMIT 1
);
SET @sql := IF(@fk IS NULL,
  'ALTER TABLE `deposit_invoices` ADD CONSTRAINT `fk_di_holding` FOREIGN KEY (`holding_id`) REFERENCES `investment_holdings` (`id`) ON DELETE SET NULL',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Add FK for reviewed_by (skip if already exists)
SET @fk2 := (
  SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME   = 'deposit_invoices'
    AND COLUMN_NAME  = 'reviewed_by'
    AND REFERENCED_TABLE_NAME IS NOT NULL
  LIMIT 1
);
SET @sql2 := IF(@fk2 IS NULL,
  'ALTER TABLE `deposit_invoices` ADD CONSTRAINT `fk_di_admin` FOREIGN KEY (`reviewed_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL',
  'SELECT 1'
);
PREPARE stmt2 FROM @sql2; EXECUTE stmt2; DEALLOCATE PREPARE stmt2;

-- ── investment_holdings: fix ENUM and add payment_ref if missing ──────────

ALTER TABLE `investment_holdings`
  MODIFY COLUMN `payment_method` ENUM('crypto','paypal','wire','wallet') NOT NULL;

ALTER TABLE `investment_holdings`
  ADD COLUMN IF NOT EXISTS `payment_ref` VARCHAR(100) DEFAULT NULL AFTER `certificate_ref`;

-- ── platform_settings: add missing keys ───────────────────────────────────

INSERT IGNORE INTO `platform_settings` (`key`, `value`, `group`, `label`) VALUES
  ('email_verification_enabled', '1',   'features', 'Email Verification Required'),
  ('smtp_secure',                'tls',  'smtp',     'SMTP Encryption'),
  ('crypto_btc_address',         '',     'payment',  'BTC Wallet Address'),
  ('crypto_eth_address',         '',     'payment',  'ETH Wallet Address'),
  ('crypto_usdt_address',        '',     'payment',  'USDT Wallet Address'),
  ('crypto_usdc_address',        '',     'payment',  'USDC Wallet Address');
