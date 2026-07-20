-- NexVest Migration v5 — Run on existing installations
-- Adds: auto_reinvest column, legal platform settings, schema enum fixes

-- Auto-reinvest toggle per holding
ALTER TABLE `investment_holdings`
  ADD COLUMN IF NOT EXISTS `auto_reinvest` TINYINT(1) NOT NULL DEFAULT '0' AFTER `total_earned`;

-- Add 'weeks' to investments duration_unit ENUM
ALTER TABLE `investments`
  MODIFY COLUMN `duration_unit` ENUM('days','weeks','months','years') NOT NULL DEFAULT 'months';

-- Add 'superseded' to kyc_submissions status ENUM
ALTER TABLE `kyc_submissions`
  MODIFY COLUMN `status` ENUM('pending','approved','rejected','superseded') NOT NULL DEFAULT 'pending';

-- Add 'debit' to transactions type ENUM
ALTER TABLE `transactions`
  MODIFY COLUMN `type` ENUM('deposit','withdrawal','investment','return','referral_commission','adjustment','debit') NOT NULL;

-- Missing platform settings
INSERT IGNORE INTO `platform_settings` (`setting_key`, `setting_value`, `setting_group`) VALUES
  ('maintenance_message',     'We are performing scheduled maintenance. We will be back shortly.', 'features'),
  ('legal_company_name',      '',   'legal'),
  ('legal_registration_number', '', 'legal'),
  ('legal_regulator',         '',   'legal'),
  ('legal_jurisdiction',      '',   'legal'),
  ('legal_terms',             '',   'legal'),
  ('legal_privacy',           '',   'legal'),
  ('admin_notification_email','',   'email'),
  ('min_deposit',             '100','payments'),
  ('min_withdrawal',          '50', 'payments'),
  ('logout_redirect_url',     '',   'general');
