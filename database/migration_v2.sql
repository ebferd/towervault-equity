-- ============================================================
--  NexVest — Migration v2
--  Group 1: Payment Configuration + Deposit Management
--  Run this ONCE against your existing database after uploading v10
-- ============================================================

-- Extend deposit_invoices: add submitted/rejected statuses + proof upload + admin review
ALTER TABLE `deposit_invoices`
  MODIFY `status` ENUM('pending','submitted','paid','rejected','expired','cancelled') NOT NULL DEFAULT 'pending',
  ADD COLUMN `proof_of_payment` VARCHAR(255) DEFAULT NULL AFTER `wallet_address`,
  ADD COLUMN `admin_note`       TEXT         DEFAULT NULL AFTER `confirmed_at`,
  ADD COLUMN `reviewed_by`      INT UNSIGNED DEFAULT NULL AFTER `admin_note`,
  ADD COLUMN `reviewed_at`      DATETIME     DEFAULT NULL AFTER `reviewed_by`;

ALTER TABLE `deposit_invoices`
  ADD CONSTRAINT `fk_di_admin` FOREIGN KEY (`reviewed_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

-- New platform_settings: crypto wallet addresses, PayPal details, SMTP secure, platform_initials
INSERT IGNORE INTO `platform_settings` (`setting_key`, `setting_value`, `setting_group`) VALUES
('crypto_btc_address',  '', 'payments'),
('crypto_eth_address',  '', 'payments'),
('crypto_usdt_address', '', 'payments'),
('crypto_usdc_address', '', 'payments'),
('paypal_email',        '', 'payments'),
('paypal_me_link',      '', 'payments'),
('smtp_secure',         'tls', 'email');

-- Ensure platform_initials exists (may already exist in fresh installs)
INSERT IGNORE INTO `platform_settings` (`setting_key`, `setting_value`, `setting_group`) VALUES
('platform_initials', 'NV', 'branding'),
('platform_tagline',  'Capital Group', 'branding');
