-- ============================================================
--  NexVest — Migration v4
--  Run AFTER migration_v3.sql
-- ============================================================

-- Admin-configurable toggle: turn email verification on/off at registration.
-- Defaults to ON (existing behaviour). Set to '0' from Admin > Settings > Features
-- to auto-verify new accounts instantly — useful if outbound SMTP isn't configured
-- or is unreliable on the host.
INSERT IGNORE INTO `platform_settings` (`setting_key`, `setting_value`, `setting_group`) VALUES
('email_verification_enabled', '1', 'features');
