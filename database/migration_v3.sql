-- ============================================================
--  NexVest — Migration v3
--  Run AFTER migration_v2.sql
-- ============================================================

-- Link investment holdings to their deposit invoice
-- NULL means it is a plain wallet top-up deposit
ALTER TABLE `deposit_invoices`
  ADD COLUMN `holding_id` INT UNSIGNED DEFAULT NULL AFTER `user_id`,
  ADD CONSTRAINT `fk_di_holding` FOREIGN KEY (`holding_id`) REFERENCES `investment_holdings` (`id`) ON DELETE SET NULL;
