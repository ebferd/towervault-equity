-- Migration v9 — Partner (special agent) accounts
-- Adds a Partner flag and a custom referral commission rate per user.
ALTER TABLE `users`
  ADD COLUMN `is_agent` TINYINT(1) NOT NULL DEFAULT 0 AFTER `min_investment_note`,
  ADD COLUMN `agent_commission` DECIMAL(5,2) DEFAULT NULL AFTER `is_agent`;
