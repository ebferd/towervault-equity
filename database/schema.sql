-- ============================================================
--  NexVest Capital Group — Full Database Schema
--  MySQL 8.0+
--  Stage 12 — PHP/MySQL Conversion
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ─────────────────────────────────────────────
--  DATABASE
-- ─────────────────────────────────────────────
CREATE DATABASE IF NOT EXISTS `nexvest` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `nexvest`;


-- ─────────────────────────────────────────────
--  PLATFORM SETTINGS
-- ─────────────────────────────────────────────
CREATE TABLE `platform_settings` (
  `id`                   INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `setting_key`          VARCHAR(100)     NOT NULL,
  `setting_value`        TEXT             DEFAULT NULL,
  `setting_group`        VARCHAR(50)      NOT NULL DEFAULT 'general',
  `updated_at`           TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `platform_settings` (`setting_key`, `setting_value`, `setting_group`) VALUES
('platform_name',         'NexVest',                                      'branding'),
('platform_tagline',      'Capital Group',                                 'branding'),
('platform_initials',     'NV',                                            'branding'),
('platform_logo',         NULL,                                            'branding'),
('platform_email',        'noreply@nexvest.com',                           'branding'),
('platform_support_email','support@nexvest.com',                           'branding'),
('platform_phone',        '+1 (212) 555-0190',                             'branding'),
('platform_address',      '350 Fifth Avenue, New York, NY 10118, USA',     'branding'),
('platform_website',      'https://nexvest.com',                           'branding'),
('platform_currency',     'USD',                                            'finance'),
('platform_symbol',       '$',                                              'finance'),
('kyc_enabled',           '1',                                              'features'),
('two_fa_enabled',        '1',                                              'features'),
('registration_open',     '1',                                              'features'),
('maintenance_mode',      '0',                                              'features'),
('email_verification_enabled', '1',                                         'features'),
('payment_crypto',        '1',                                              'payments'),
('payment_paypal',        '1',                                              'payments'),
('payment_wire',          '1',                                              'payments'),
('crypto_btc_address',    '',                                               'payments'),
('crypto_eth_address',    '',                                               'payments'),
('crypto_usdt_address',   '',                                               'payments'),
('crypto_usdc_address',   '',                                               'payments'),
('paypal_email',          '',                                               'payments'),
('paypal_me_link',        '',                                               'payments'),
('wire_bank_name',        'Citibank N.A.',                                  'payments'),
('wire_account_name',     'NexVest Capital LLC',                            'payments'),
('wire_account_number',   '4821 0093 7751',                                 'payments'),
('wire_routing',          '021000089',                                      'payments'),
('wire_swift',            'CITIUS33',                                       'payments'),
('wire_bank_country',     'United States',                                  'payments'),
('referral_commission',   '5',                                              'referrals'),
('deposit_timeout',       '1800',                                           'payments'),
('smtp_host',             'smtp.nexvest.com',                               'email'),
('smtp_port',             '587',                                            'email'),
('smtp_user',             'noreply@nexvest.com',                            'email'),
('smtp_pass',             '',                                               'email'),
('smtp_secure',           'tls',                                            'email'),
('smtp_from_name',        'NexVest Capital Group',                          'email'),
('admin_notification_email', '',                                               'email'),
('min_deposit',          '100',                                               'payments'),
('min_withdrawal',       '50',                                                'payments'),
('logout_redirect_url',  '',                                                  'general'),
('maintenance_message',  'We are performing scheduled maintenance. We will be back shortly.', 'features'),
('legal_company_name',   '',                                                  'legal'),
('legal_registration_number', '',                                             'legal'),
('legal_regulator',      '',                                                  'legal'),
('legal_jurisdiction',   '',                                                  'legal'),
('legal_terms',          '',                                                  'legal'),
('legal_privacy',        '',                                                  'legal');


-- ─────────────────────────────────────────────
--  ADMINS
-- ─────────────────────────────────────────────
CREATE TABLE `admins` (
  `id`              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `name`            VARCHAR(120)  NOT NULL,
  `email`           VARCHAR(191)  NOT NULL,
  `password`        VARCHAR(255)  NOT NULL,
  `role`            ENUM('super_admin','compliance','support','finance') NOT NULL DEFAULT 'support',
  `avatar`          VARCHAR(255)  DEFAULT NULL,
  `is_active`       TINYINT(1)    NOT NULL DEFAULT 1,
  `last_login_at`   DATETIME      DEFAULT NULL,
  `last_login_ip`   VARCHAR(45)   DEFAULT NULL,
  `created_at`      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admin_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default super admin (password: Admin@123456 — change immediately)
INSERT INTO `admins` (`name`, `email`, `password`, `role`) VALUES
('Super Admin', 'admin@nexvest.com', '$2y$12$z3MgYUVCZnFzaCmgULU/JOD0/MNsXzDsdVfZYhx.BD8NBV/wjjury', 'super_admin');


-- ─────────────────────────────────────────────
--  USERS (INVESTORS)
-- ─────────────────────────────────────────────
CREATE TABLE `users` (
  `id`                   INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `first_name`           VARCHAR(80)     NOT NULL,
  `last_name`            VARCHAR(80)     NOT NULL,
  `email`                VARCHAR(191)    NOT NULL,
  `password`             VARCHAR(255)    NOT NULL,
  `phone`                VARCHAR(30)     DEFAULT NULL,
  `country`              VARCHAR(100)    DEFAULT NULL,
  `date_of_birth`        DATE            DEFAULT NULL,
  `avatar`               VARCHAR(255)    DEFAULT NULL,
  `referral_code`        VARCHAR(20)     NOT NULL,
  `referred_by`          INT UNSIGNED    DEFAULT NULL,
  `wallet_balance`       DECIMAL(18,2)   NOT NULL DEFAULT '0.00',
  `status`               ENUM('active','suspended','banned') NOT NULL DEFAULT 'active',
  `kyc_status`           ENUM('not_submitted','pending','verified','rejected') NOT NULL DEFAULT 'not_submitted',
  `email_verified`       TINYINT(1)      NOT NULL DEFAULT 0,
  `email_verified_at`    DATETIME        DEFAULT NULL,
  `two_fa_enabled`       TINYINT(1)      NOT NULL DEFAULT 0,
  `two_fa_secret`        VARCHAR(64)     DEFAULT NULL,
  `withdrawals_disabled`    TINYINT(1)   NOT NULL DEFAULT 0,
  `min_investment_override` DECIMAL(15,2) DEFAULT NULL,
  `min_investment_note`     VARCHAR(500) DEFAULT NULL,
  `last_login_at`        DATETIME        DEFAULT NULL,
  `last_login_ip`        VARCHAR(45)     DEFAULT NULL,
  `created_at`           TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`           TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_email` (`email`),
  UNIQUE KEY `uq_referral_code` (`referral_code`),
  KEY `idx_referred_by` (`referred_by`),
  KEY `idx_status` (`status`),
  KEY `idx_kyc_status` (`kyc_status`),
  CONSTRAINT `fk_user_referred_by` FOREIGN KEY (`referred_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ─────────────────────────────────────────────
--  EMAIL VERIFICATION TOKENS
-- ─────────────────────────────────────────────
CREATE TABLE `email_verifications` (
  `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED  NOT NULL,
  `token`      VARCHAR(64)   NOT NULL,
  `otp`        VARCHAR(6)    NOT NULL,
  `expires_at` DATETIME      NOT NULL,
  `used`       TINYINT(1)    NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ev_user` (`user_id`),
  KEY `idx_ev_token` (`token`),
  CONSTRAINT `fk_ev_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ─────────────────────────────────────────────
--  PASSWORD RESETS
-- ─────────────────────────────────────────────
CREATE TABLE `password_resets` (
  `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `email`      VARCHAR(191)  NOT NULL,
  `token`      VARCHAR(128)  NOT NULL,
  `expires_at` DATETIME      NOT NULL,
  `used`       TINYINT(1)    NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pr_email` (`email`),
  KEY `idx_pr_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ─────────────────────────────────────────────
--  USER SESSIONS
-- ─────────────────────────────────────────────
CREATE TABLE `user_sessions` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `user_id`       INT UNSIGNED  NOT NULL,
  `session_token` VARCHAR(128)  NOT NULL,
  `ip_address`    VARCHAR(45)   DEFAULT NULL,
  `user_agent`    VARCHAR(255)  DEFAULT NULL,
  `device`        VARCHAR(120)  DEFAULT NULL,
  `last_active`   DATETIME      NOT NULL,
  `expires_at`    DATETIME      NOT NULL,
  `created_at`    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_session_token` (`session_token`),
  KEY `idx_us_user` (`user_id`),
  CONSTRAINT `fk_us_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ─────────────────────────────────────────────
--  KYC SUBMISSIONS
-- ─────────────────────────────────────────────
CREATE TABLE `kyc_submissions` (
  `id`               INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `user_id`          INT UNSIGNED  NOT NULL,
  `full_legal_name`  VARCHAR(200)  NOT NULL,
  `date_of_birth`    DATE          NOT NULL,
  `id_type`          ENUM('passport','national_id','drivers_license') NOT NULL,
  `doc_front`        VARCHAR(255)  DEFAULT NULL,
  `doc_back`         VARCHAR(255)  DEFAULT NULL,
  `doc_selfie`       VARCHAR(255)  DEFAULT NULL,
  `proof_of_address` VARCHAR(255)  DEFAULT NULL,
  `status`           ENUM('pending','approved','rejected','superseded') NOT NULL DEFAULT 'pending',
  `rejection_reason` TEXT          DEFAULT NULL,
  `reviewed_by`      INT UNSIGNED  DEFAULT NULL,
  `reviewed_at`      DATETIME      DEFAULT NULL,
  `submitted_at`     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_kyc_user` (`user_id`),
  KEY `idx_kyc_status` (`status`),
  CONSTRAINT `fk_kyc_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_kyc_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ─────────────────────────────────────────────
--  INVESTMENTS (PRODUCTS)
-- ─────────────────────────────────────────────
CREATE TABLE `investments` (
  `id`               INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `name`             VARCHAR(200)    NOT NULL,
  `slug`             VARCHAR(200)    NOT NULL,
  `type`             ENUM('real_estate','index_fund') NOT NULL,
  `status`           ENUM('active','funded','closed','coming_soon') NOT NULL DEFAULT 'active',
  `short_desc`       VARCHAR(500)    DEFAULT NULL,
  `description`      LONGTEXT        DEFAULT NULL,
  `roi`              DECIMAL(5,2)    NOT NULL,
  `duration_value`   SMALLINT        NOT NULL,
  `duration_unit`    ENUM('days','weeks','months','years') NOT NULL DEFAULT 'months',
  `payout_frequency` ENUM('daily','weekly','monthly','quarterly','semi_annual','at_maturity') NOT NULL DEFAULT 'monthly',
  `min_investment`   DECIMAL(15,2)   NOT NULL DEFAULT '100.00',
  `max_investment`   DECIMAL(15,2)   DEFAULT NULL,
  `funding_target`   DECIMAL(18,2)   DEFAULT NULL,
  `funding_raised`   DECIMAL(18,2)   NOT NULL DEFAULT '0.00',
  -- Real estate specific
  `property_type`    VARCHAR(100)    DEFAULT NULL,
  `street_address`   VARCHAR(255)    DEFAULT NULL,
  `city`             VARCHAR(100)    DEFAULT NULL,
  `state_region`     VARCHAR(100)    DEFAULT NULL,
  `country`          VARCHAR(100)    DEFAULT NULL,
  `postcode`         VARCHAR(20)     DEFAULT NULL,
  `maps_link`        VARCHAR(500)    DEFAULT NULL,
  `property_size`    VARCHAR(100)    DEFAULT NULL,
  `total_units`      VARCHAR(100)    DEFAULT NULL,
  `occupancy_rate`   DECIMAL(5,2)    DEFAULT NULL,
  `year_built`       SMALLINT        DEFAULT NULL,
  `completion_date`  DATE            DEFAULT NULL,
  -- Index fund specific
  `ticker`           VARCHAR(20)     DEFAULT NULL,
  `fund_category`    VARCHAR(100)    DEFAULT NULL,
  `risk_level`       ENUM('low','low_medium','medium','medium_high','high') DEFAULT NULL,
  `management_fee`   DECIMAL(5,2)    DEFAULT NULL,
  `benchmark`        VARCHAR(200)    DEFAULT NULL,
  `fund_start_date`  DATE            DEFAULT NULL,
  `fund_end_date`    DATE            DEFAULT NULL,
  -- Display options
  `is_featured`      TINYINT(1)      NOT NULL DEFAULT 0,
  `is_verified`      TINYINT(1)      NOT NULL DEFAULT 0,
  `notify_on_launch` TINYINT(1)      NOT NULL DEFAULT 0,
  `investor_count`   INT UNSIGNED    NOT NULL DEFAULT 0,
  `image`            VARCHAR(255)    DEFAULT NULL,
  `created_by`       INT UNSIGNED    DEFAULT NULL,
  `created_at`       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_investment_slug` (`slug`),
  KEY `idx_inv_type` (`type`),
  KEY `idx_inv_status` (`status`),
  CONSTRAINT `fk_inv_admin` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ─────────────────────────────────────────────
--  INVESTMENT HOLDINGS (USER → INVESTMENT)
-- ─────────────────────────────────────────────
CREATE TABLE `investment_holdings` (
  `id`               INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `user_id`          INT UNSIGNED    NOT NULL,
  `investment_id`    INT UNSIGNED    NOT NULL,
  `amount`           DECIMAL(15,2)   NOT NULL,
  `payment_method`   ENUM('crypto','paypal','wire','wallet') NOT NULL,
  `payment_ref`      VARCHAR(100)    DEFAULT NULL,
  `status`           ENUM('pending','active','matured','cancelled') NOT NULL DEFAULT 'pending',
  `start_date`       DATE            NOT NULL,
  `end_date`         DATE            NOT NULL,
  `roi`              DECIMAL(5,2)    NOT NULL,
  `total_earned`     DECIMAL(15,2)   NOT NULL DEFAULT '0.00',
  `auto_reinvest`    TINYINT(1)      NOT NULL DEFAULT '0',
  `last_payout_at`   DATETIME        DEFAULT NULL,
  `certificate_ref`  VARCHAR(20)     DEFAULT NULL,
  `created_at`       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cert_ref` (`certificate_ref`),
  KEY `idx_ih_user` (`user_id`),
  KEY `idx_ih_investment` (`investment_id`),
  KEY `idx_ih_status` (`status`),
  CONSTRAINT `fk_ih_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ih_investment` FOREIGN KEY (`investment_id`) REFERENCES `investments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ─────────────────────────────────────────────
--  INVESTMENT DOCUMENTS
-- ─────────────────────────────────────────────
CREATE TABLE `investment_documents` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `investment_id` INT UNSIGNED  NOT NULL,
  `name`          VARCHAR(200)  NOT NULL,
  `file_path`     VARCHAR(255)  NOT NULL,
  `file_size`     INT UNSIGNED  DEFAULT NULL,
  `uploaded_by`   INT UNSIGNED  DEFAULT NULL,
  `created_at`    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_idoc_investment` (`investment_id`),
  CONSTRAINT `fk_idoc_investment` FOREIGN KEY (`investment_id`) REFERENCES `investments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_idoc_admin` FOREIGN KEY (`uploaded_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ─────────────────────────────────────────────
--  INVESTMENT HOLDINGS (INDEX FUND TOP HOLDINGS)
-- ─────────────────────────────────────────────
CREATE TABLE `fund_holdings` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `investment_id` INT UNSIGNED  NOT NULL,
  `holding_name`  VARCHAR(200)  NOT NULL,
  `sort_order`    TINYINT       NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_fh_investment` (`investment_id`),
  CONSTRAINT `fk_fh_investment` FOREIGN KEY (`investment_id`) REFERENCES `investments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ─────────────────────────────────────────────
--  TRANSACTIONS
-- ─────────────────────────────────────────────
CREATE TABLE `transactions` (
  `id`             INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `user_id`        INT UNSIGNED    NOT NULL,
  `type`           ENUM('deposit','withdrawal','investment','return','referral_commission','adjustment','debit','transfer_sent','transfer_received') NOT NULL,
  `amount`         DECIMAL(15,2)   NOT NULL,
  `balance_before` DECIMAL(15,2)   NOT NULL,
  `balance_after`  DECIMAL(15,2)   NOT NULL,
  `status`         ENUM('pending','completed','failed','rejected') NOT NULL DEFAULT 'pending',
  `method`         VARCHAR(50)     DEFAULT NULL,
  `reference`      VARCHAR(60)     NOT NULL,
  `description`    VARCHAR(500)    DEFAULT NULL,
  `holding_id`     INT UNSIGNED    DEFAULT NULL,
  `admin_note`     TEXT            DEFAULT NULL,
  `processed_by`   INT UNSIGNED    DEFAULT NULL,
  `processed_at`   DATETIME        DEFAULT NULL,
  `created_at`     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tx_reference` (`reference`),
  KEY `idx_tx_user` (`user_id`),
  KEY `idx_tx_type` (`type`),
  KEY `idx_tx_status` (`status`),
  KEY `idx_tx_holding` (`holding_id`),
  CONSTRAINT `fk_tx_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tx_holding` FOREIGN KEY (`holding_id`) REFERENCES `investment_holdings` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_tx_admin` FOREIGN KEY (`processed_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ─────────────────────────────────────────────
--  DEPOSIT INVOICES (with timeout)
-- ─────────────────────────────────────────────
CREATE TABLE `deposit_invoices` (
  `id`             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `user_id`        INT UNSIGNED  NOT NULL,
  `reference`      VARCHAR(20)   NOT NULL,
  `amount`         DECIMAL(15,2) NOT NULL,
  `method`         ENUM('crypto','paypal','wire') NOT NULL,
  `coin`           VARCHAR(10)   DEFAULT NULL,
  `wallet_address` VARCHAR(255)  DEFAULT NULL,
  `holding_id`     INT UNSIGNED  DEFAULT NULL,
  `status`         ENUM('pending','submitted','paid','rejected','expired','cancelled') NOT NULL DEFAULT 'pending',
  `expires_at`     DATETIME      NOT NULL,
  `confirmed_at`   DATETIME      DEFAULT NULL,
  `admin_note`     TEXT          DEFAULT NULL,
  `reviewed_by`    INT UNSIGNED  DEFAULT NULL,
  `reviewed_at`    DATETIME      DEFAULT NULL,
  `created_at`     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_invoice_ref` (`reference`),
  KEY `idx_di_user` (`user_id`),
  KEY `idx_di_status` (`status`),
  CONSTRAINT `fk_di_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_di_holding` FOREIGN KEY (`holding_id`) REFERENCES `investment_holdings` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_di_admin` FOREIGN KEY (`reviewed_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ─────────────────────────────────────────────
--  WITHDRAWAL REQUESTS
-- ─────────────────────────────────────────────
CREATE TABLE `withdrawal_requests` (
  `id`             INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `user_id`        INT UNSIGNED    NOT NULL,
  `reference`      VARCHAR(20)     NOT NULL,
  `amount`         DECIMAL(15,2)   NOT NULL,
  `method`         ENUM('crypto','paypal','wire') NOT NULL,
  `details`        JSON            NOT NULL,
  `status`         ENUM('pending','approved','completed','rejected') NOT NULL DEFAULT 'pending',
  `rejection_note` TEXT            DEFAULT NULL,
  `reviewed_by`    INT UNSIGNED    DEFAULT NULL,
  `reviewed_at`    DATETIME        DEFAULT NULL,
  `completed_at`   DATETIME        DEFAULT NULL,
  `created_at`     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_wr_reference` (`reference`),
  KEY `idx_wr_user` (`user_id`),
  KEY `idx_wr_status` (`status`),
  CONSTRAINT `fk_wr_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_wr_admin` FOREIGN KEY (`reviewed_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ─────────────────────────────────────────────
--  NOTIFICATIONS
-- ─────────────────────────────────────────────
CREATE TABLE `notifications` (
  `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED  NOT NULL,
  `type`       VARCHAR(50)   NOT NULL,
  `title`      VARCHAR(255)  NOT NULL,
  `message`    TEXT          NOT NULL,
  `data`       JSON          DEFAULT NULL,
  `is_read`    TINYINT(1)    NOT NULL DEFAULT 0,
  `read_at`    DATETIME      DEFAULT NULL,
  `created_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notif_user` (`user_id`),
  KEY `idx_notif_read` (`is_read`),
  CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ─────────────────────────────────────────────
--  SUPPORT TICKETS
-- ─────────────────────────────────────────────
CREATE TABLE `support_tickets` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `user_id`       INT UNSIGNED  NOT NULL,
  `reference`     VARCHAR(12)   NOT NULL,
  `subject`       VARCHAR(255)  NOT NULL,
  `status`        ENUM('open','in_progress','resolved','closed') NOT NULL DEFAULT 'open',
  `priority`      ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  `assigned_to`   INT UNSIGNED  DEFAULT NULL,
  `closed_at`     DATETIME      DEFAULT NULL,
  `created_at`    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ticket_ref` (`reference`),
  KEY `idx_st_user` (`user_id`),
  KEY `idx_st_status` (`status`),
  CONSTRAINT `fk_st_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_st_admin` FOREIGN KEY (`assigned_to`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ─────────────────────────────────────────────
--  TICKET MESSAGES
-- ─────────────────────────────────────────────
CREATE TABLE `ticket_messages` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `ticket_id`   INT UNSIGNED  NOT NULL,
  `sender_type` ENUM('user','admin') NOT NULL,
  `sender_id`   INT UNSIGNED  NOT NULL,
  `message`     TEXT          NOT NULL,
  `created_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tm_ticket` (`ticket_id`),
  CONSTRAINT `fk_tm_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ─────────────────────────────────────────────
--  REFERRALS
-- ─────────────────────────────────────────────
CREATE TABLE `referrals` (
  `id`                INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `referrer_id`       INT UNSIGNED    NOT NULL,
  `referred_id`       INT UNSIGNED    NOT NULL,
  `commission_rate`   DECIMAL(5,2)    NOT NULL,
  `commission_amount` DECIMAL(15,2)   NOT NULL DEFAULT '0.00',
  `status`            ENUM('registered','invested','commission_paid') NOT NULL DEFAULT 'registered',
  `invested_amount`   DECIMAL(15,2)   NOT NULL DEFAULT '0.00',
  `paid_at`           DATETIME        DEFAULT NULL,
  `created_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_referral_pair` (`referrer_id`,`referred_id`),
  KEY `idx_ref_referrer` (`referrer_id`),
  KEY `idx_ref_referred` (`referred_id`),
  CONSTRAINT `fk_ref_referrer` FOREIGN KEY (`referrer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ref_referred` FOREIGN KEY (`referred_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ─────────────────────────────────────────────
--  AUDIT LOGS
-- ─────────────────────────────────────────────
CREATE TABLE `audit_logs` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `admin_id`    INT UNSIGNED  NOT NULL,
  `action`      VARCHAR(80)   NOT NULL,
  `target_type` VARCHAR(50)   DEFAULT NULL,
  `target_id`   INT UNSIGNED  DEFAULT NULL,
  `target_name` VARCHAR(200)  DEFAULT NULL,
  `detail`      TEXT          NOT NULL,
  `old_value`   JSON          DEFAULT NULL,
  `new_value`   JSON          DEFAULT NULL,
  `severity`    ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
  `ip_address`  VARCHAR(45)   DEFAULT NULL,
  `user_agent`  VARCHAR(255)  DEFAULT NULL,
  `created_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_al_admin` (`admin_id`),
  KEY `idx_al_action` (`action`),
  KEY `idx_al_severity` (`severity`),
  KEY `idx_al_created` (`created_at`),
  CONSTRAINT `fk_al_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ─────────────────────────────────────────────
--  TWO FA BACKUP CODES
-- ─────────────────────────────────────────────
CREATE TABLE `two_fa_backup_codes` (
  `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `user_id`    INT UNSIGNED  NOT NULL,
  `code`       VARCHAR(20)   NOT NULL,
  `used`       TINYINT(1)    NOT NULL DEFAULT 0,
  `used_at`    DATETIME      DEFAULT NULL,
  `created_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_2fa_user` (`user_id`),
  CONSTRAINT `fk_2fa_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ─────────────────────────────────────────────
--  ROI PAYOUT SCHEDULE (CRON)
-- ─────────────────────────────────────────────
CREATE TABLE `payout_schedules` (
  `id`           INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `holding_id`   INT UNSIGNED    NOT NULL,
  `user_id`      INT UNSIGNED    NOT NULL,
  `amount`       DECIMAL(15,2)   NOT NULL,
  `due_date`     DATE            NOT NULL,
  `paid_at`      DATETIME        DEFAULT NULL,
  `status`       ENUM('scheduled','paid','failed') NOT NULL DEFAULT 'scheduled',
  `tx_id`        INT UNSIGNED    DEFAULT NULL,
  `created_at`   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_holding_due` (`holding_id`, `due_date`),
  KEY `idx_ps_user` (`user_id`),
  KEY `idx_ps_due` (`due_date`),
  KEY `idx_ps_status` (`status`),
  CONSTRAINT `fk_ps_holding` FOREIGN KEY (`holding_id`) REFERENCES `investment_holdings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ps_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ps_tx` FOREIGN KEY (`tx_id`) REFERENCES `transactions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ─────────────────────────────────────────────
--  WALLET TRANSFERS (peer-to-peer)
-- ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `wallet_transfers` (
  `id`             INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `reference`      VARCHAR(60)     NOT NULL,
  `sender_id`      INT UNSIGNED    NOT NULL,
  `receiver_id`    INT UNSIGNED    NOT NULL,
  `amount`         DECIMAL(15,2)   NOT NULL,
  `note`           VARCHAR(255)    DEFAULT NULL,
  `sender_tx_id`   INT UNSIGNED    DEFAULT NULL,
  `receiver_tx_id` INT UNSIGNED    DEFAULT NULL,
  `created_at`     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_wt_reference` (`reference`),
  KEY `idx_wt_sender`   (`sender_id`),
  KEY `idx_wt_receiver` (`receiver_id`),
  CONSTRAINT `fk_wt_sender`   FOREIGN KEY (`sender_id`)      REFERENCES `users`        (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_wt_receiver` FOREIGN KEY (`receiver_id`)    REFERENCES `users`        (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_wt_stx`      FOREIGN KEY (`sender_tx_id`)   REFERENCES `transactions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_wt_rtx`      FOREIGN KEY (`receiver_tx_id`) REFERENCES `transactions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ─────────────────────────────────────────────
--  LOGIN ATTEMPTS (rate limiting)
-- ─────────────────────────────────────────────
CREATE TABLE `login_attempts` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `email`       VARCHAR(191)  NOT NULL,
  `ip_address`  VARCHAR(45)   NOT NULL,
  `success`     TINYINT(1)    NOT NULL DEFAULT 0,
  `created_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_la_email` (`email`),
  KEY `idx_la_ip` (`ip_address`),
  KEY `idx_la_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ─────────────────────────────────────────────
--  ANNOUNCEMENTS
-- ─────────────────────────────────────────────
CREATE TABLE `announcements` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `subject`     VARCHAR(255)  NOT NULL,
  `message`     LONGTEXT      NOT NULL,
  `sent_by`     INT UNSIGNED  NOT NULL,
  `sent_to`     ENUM('all','active','verified') NOT NULL DEFAULT 'all',
  `recipient_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `sent_at`     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_ann_admin` FOREIGN KEY (`sent_by`) REFERENCES `admins` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ─────────────────────────────────────────────
--  ADMIN-ISSUED PAYMENT INVOICES
-- ─────────────────────────────────────────────
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


SET FOREIGN_KEY_CHECKS = 1;

-- ─────────────────────────────────────────────
--  VIEWS (helpers for reports)
-- ─────────────────────────────────────────────
CREATE OR REPLACE VIEW `v_investor_summary` AS
SELECT
  u.id,
  CONCAT(u.first_name,' ',u.last_name) AS full_name,
  u.email,
  u.country,
  u.wallet_balance,
  u.status,
  u.kyc_status,
  u.created_at AS joined_at,
  COUNT(DISTINCT ih.id)                AS active_investments,
  COALESCE(SUM(ih.amount),0)           AS total_invested,
  COALESCE(SUM(ih.total_earned),0)     AS total_earned,
  COUNT(DISTINCT r.id)                 AS total_referrals
FROM users u
LEFT JOIN investment_holdings ih ON ih.user_id = u.id AND ih.status = 'active'
LEFT JOIN referrals r ON r.referrer_id = u.id
GROUP BY u.id;


CREATE OR REPLACE VIEW `v_platform_stats` AS
SELECT
  (SELECT COUNT(*) FROM users)                                         AS total_users,
  (SELECT COUNT(*) FROM users WHERE status='active')                   AS active_users,
  (SELECT COUNT(*) FROM users WHERE kyc_status='pending')              AS kyc_pending,
  (SELECT COALESCE(SUM(wallet_balance),0) FROM users)                  AS total_wallet_balance,
  (SELECT COALESCE(SUM(amount),0) FROM investment_holdings WHERE status='active') AS total_invested,
  (SELECT COALESCE(SUM(total_earned),0) FROM investment_holdings)      AS total_returns_paid,
  (SELECT COUNT(*) FROM withdrawal_requests WHERE status='pending')    AS pending_withdrawals,
  (SELECT COUNT(*) FROM support_tickets WHERE status='open')           AS open_tickets,
  (SELECT COUNT(*) FROM investments WHERE status='active')             AS active_investments;



-- ─────────────────────────────────────────────
--  SEED INVESTMENTS
-- ─────────────────────────────────────────────
INSERT INTO `investments` (`id`, `name`, `slug`, `type`, `status`, `short_desc`, `description`, `roi`, `duration_value`, `duration_unit`, `payout_frequency`, `min_investment`, `max_investment`, `funding_target`, `funding_raised`, `property_type`, `street_address`, `city`, `state_region`, `country`, `postcode`, `maps_link`, `property_size`, `total_units`, `occupancy_rate`, `year_built`, `completion_date`, `ticker`, `fund_category`, `risk_level`, `management_fee`, `benchmark`, `fund_start_date`, `fund_end_date`, `is_featured`, `is_verified`, `notify_on_launch`, `investor_count`, `image`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Marina Residences Tower — Dubai, UAE', 'marina-residences-tower-dubai', 'real_estate', 'active', 'Grade-A luxury residential tower in Dubai Marina offering stable monthly rental income with strong capital appreciation potential.', 'Marina Residences Tower is a 42-storey premium residential development situated on the prime waterfront of Dubai Marina, one of the most sought-after addresses in the Middle East. The building comprises 318 fully furnished apartments ranging from studios to 3-bedroom units, all commanding unobstructed views of the Arabian Gulf and the Dubai skyline.', 50.00, 12, 'months', 'weekly', 500.00, 500000.00, 12000000.00, 8745500.00, 'Residential Tower', 'Al Marsa Street, Plot 25', 'Dubai', 'Dubai', 'United Arab Emirates', NULL, 'https://maps.google.com/?q=Dubai+Marina,+Dubai,+UAE', '62,400 sq ft (GFA)', '318 units', 94.20, 2019, '2027-06-26', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, 0, 0, NULL, NULL, '2026-06-20 22:35:46', '2026-07-07 18:34:17'),
(3, 'Arlington S/P 500 Growth Fund', 'nexvest-sp500-growth-fund', 'index_fund', 'active', 'Passive exposure to the 500 largest U.S. companies. Historically the worlds most trusted long-term wealth-building vehicle.', 'The Arlington S/P500 Growth Fund provides direct, low-cost exposure to the Standard S/P 500 Index — the benchmark that tracks 500 of the largest publicly traded companies listed on U.S. stock exchanges, covering approximately 80% of total U.S. equity market capitalization.', 30.00, 6, 'months', 'weekly', 500.00, 1000000.00, 4000000.00, 457000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'NV-SPX500', 'U.S. Large-Cap Equity', 'medium', 0.00, 'S/P 500 Total Return Index', '2020-12-10', '2026-06-21', 1, 1, 1, 0, NULL, NULL, '2026-06-20 22:35:46', '2026-07-13 13:37:56'),
(4, 'Arlington Emerging Markets Fund', 'nexvest-emerging-markets-fund', 'index_fund', 'active', 'Higher-yield access to the fastest-growing economies in Asia, Latin America, and Africa. Ideal for growth-focused investors.', 'The Arlington Emerging Markets High-Yield Fund tracks a diversified basket of equities and sovereign bonds across 24 high-growth emerging market economies, with a tilt toward countries with the strongest GDP growth trajectories and improving governance scores. The fund is constructed and rebalanced by our quantitative portfolio management team using MSCI Emerging Markets Index as its primary benchmark.', 20.00, 6, 'months', 'weekly', 500.00, 50000.00, 2750000.00, 100000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'NV-EMF24', 'Emerging Market Multi-Asset', 'low', 0.00, 'MSCI Emerging Markets Total Return Index', '2022-03-01', '2026-06-21', 0, 1, 0, 0, NULL, NULL, '2026-06-20 22:35:46', '2026-07-13 13:41:44'),
(5, 'Full Floor Flatiron Office', 'full-floor-flatiron-office', 'real_estate', 'active', 'State of the Arts Office Space', 'This project is big and we invite you to join us in this journey and build wealth together. \r\n\r\nFeatures we are adding to the project \r\n\r\nFull-floor office in prime Flatiron location\r\n11,239 SF of fully furnished and wired workspace\r\n48 workstations with expansion potential for up to 90\r\nTwo executive boardrooms with glass enclosures\r\nFive private huddle rooms for meetings or focused work\r\nTwo movable phone booths for on-the-go privacy\r\nExpansive windows providing natural light and city views\r\nHigh ceilings with exposed beams for an open feel\r\nFully equipped kitchen with café-style seating\r\nSpacious breakout lounge for informal collaboration\r\nFour private restrooms and additional storage space\r\nDirect elevator access into a professional reception area\r\nConvenient access to Union Square and Madison Square Park\r\nClose to multiple subway lines for easy commuting', 25.00, 2, 'months', 'weekly', 500.00, 500000.00, 3500000.00, 414500.00, 'Commercial', 'Flatiron District', 'New York City', 'NYC', 'United States', NULL, 'https://www.google.com/maps/place/149+5th+Ave,+New+York,+NY+10010,+USA/@40.7404882,-73.9903624,3a,75y,298.49h,90t/data=!3m4!1e1!3m2!1s1RaWMeNPNfJ_vHVwGg_tzA!2e0!4m2!3m1!1s0x89c259a3ebd851ab:0x2fecfc08b71f3a0f?entry=s&amp;sa=X&amp;ved=1t:3780&amp;hl=en-US&amp;ictx=111', '11239 SQ. FT', NULL, NULL, 1999, '2026-09-26', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, 1, 0, NULL, 1, '2026-07-11 21:25:25', '2026-07-18 19:03:31'),
(6, 'Global Crypto Blue-Chip', 'global-crypto-blue-chip', 'index_fund', 'active', 'Diversified exposure to the 10 largest and most established cryptocurrencies by market capitalisation, weighted proportionally and rebalanced monthly. Built for investors who want broad participation in the digital asset market without the risk of holding a single coin.', 'The Arlington Global Crypto Blue-Chip Index Fund gives you diversified exposure to the ten largest and most established cryptocurrencies by market capitalisation, weighted proportionally and rebalanced every month. Instead of betting on a single coin, your investment is spread across the most liquid and institutionally recognised names in the crypto market so you capture the overall direction of digital assets without the concentration risk of holding just one.\r\n\r\nOur investment team monitors the fund continuously and rebalances on the first business day of each month to keep weightings aligned and prevent any single asset from dominating the portfolio after a price swing.', 30.00, 2, 'months', 'daily', 200.00, 2000000.00, 15000000.00, 1795000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'ACPBC', 'Cryptocurrency Index Fund', NULL, 3.00, 'CoinDesk 20 Index (CD20)', '2026-07-12', '2026-09-13', 1, 1, 1, 0, NULL, 1, '2026-07-12 08:18:34', '2026-07-18 19:20:58');

-- ─────────────────────────────────────────────
--  SEED FUND HOLDINGS (Global Crypto Blue-Chip, investment #6)
-- ─────────────────────────────────────────────
INSERT INTO `fund_holdings` (`id`, `investment_id`, `holding_name`, `sort_order`) VALUES
(1, 6, '|Asset           |Weight|', 0),
(2, 6, '|----------------|------|', 1),
(3, 6, '|Bitcoin (BTC)   |42%   |', 2),
(4, 6, '|Ethereum (ETH)  |28%   |', 3),
(5, 6, '|BNB (BNB)       |6%    |', 4),
(6, 6, '|Solana (SOL)    |5%    |', 5),
(7, 6, '|XRP (XRP)       |4%    |', 6),
(8, 6, '|Cardano (ADA)   |3%    |', 7),
(9, 6, '|Avalanche (AVAX)|3%    |', 8),
(10, 6, '|Polkadot (DOT)  |3%    |', 9),
(11, 6, '|Chainlink (LINK)|3%    |', 10),
(12, 6, '|Polygon (POL)   |3%    |', 11);

