-- ============================================================
--  NexVest — Migration v15
--  Capital (principal) returns get their own transaction type so
--  they no longer inflate "returns earned" (profit) or "total
--  withdrawn". Maturity principal was booked as 'return'; early
--  termination principal was booked as 'withdrawal' — both wrong.
--  Run once in phpMyAdmin against the live database.
-- ============================================================

ALTER TABLE `transactions`
  MODIFY `type` ENUM('deposit','withdrawal','investment','return','referral_commission','adjustment','debit','transfer_sent','transfer_received','principal') NOT NULL;

-- Reclassify existing capital-return rows (safe: matched by their descriptions)
UPDATE `transactions` SET `type`='principal'
  WHERE `type`='return'     AND `description` LIKE 'Principal returned%';
UPDATE `transactions` SET `type`='principal'
  WHERE `type`='withdrawal' AND `description` LIKE 'Early termination%';
