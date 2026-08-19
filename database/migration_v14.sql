-- ============================================================
--  NexVest — Migration v14
--  Invoices can allow MULTIPLE payment methods.
--  Store 'any' or a comma-separated list (e.g. 'crypto,zelle')
--  instead of a single ENUM value.
--  Run once in phpMyAdmin against the live database.
-- ============================================================

ALTER TABLE `admin_invoices`
  MODIFY `payment_method` VARCHAR(100) NOT NULL DEFAULT 'any';
