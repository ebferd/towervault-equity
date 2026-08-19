-- ============================================================
--  NexVest — Migration v13
--  Add Zelle and Cash App as payment methods.
--  Extends the ENUM columns that store the chosen method.
--  Run once in phpMyAdmin against the live database.
-- ============================================================

ALTER TABLE `deposit_invoices`
  MODIFY `method` ENUM('crypto','paypal','wire','zelle','cashapp') NOT NULL;

ALTER TABLE `investment_holdings`
  MODIFY `payment_method` ENUM('crypto','paypal','wire','zelle','cashapp','wallet') NOT NULL;

ALTER TABLE `admin_invoices`
  MODIFY `payment_method` ENUM('any','crypto','paypal','wire','zelle','cashapp') NOT NULL DEFAULT 'any';

ALTER TABLE `withdrawal_requests`
  MODIFY `method` ENUM('crypto','paypal','wire','zelle','cashapp') NOT NULL;
