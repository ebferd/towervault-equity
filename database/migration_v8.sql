-- ─────────────────────────────────────────────
--  Migration v8 — Per-user account restrictions
--  • Disable withdrawals for a specific user
--  • Per-user minimum investment override + note
--  Safe to run on an existing database. Idempotent-ish:
--  re-running will error only if columns already exist.
-- ─────────────────────────────────────────────
USE `nexvest`;

ALTER TABLE `users`
  ADD COLUMN `withdrawals_disabled`    TINYINT(1)    NOT NULL DEFAULT 0 AFTER `two_fa_secret`,
  ADD COLUMN `min_investment_override` DECIMAL(15,2) DEFAULT NULL       AFTER `withdrawals_disabled`,
  ADD COLUMN `min_investment_note`     VARCHAR(500)  DEFAULT NULL       AFTER `min_investment_override`;
