-- ============================================================
--  NexVest — Migration v12
--  Marketing campaigns can now feature MULTIPLE opportunities.
--  Store a comma-separated list of ids instead of a single id.
--  Run once in phpMyAdmin against the live database.
-- ============================================================

ALTER TABLE `marketing_campaigns`
  CHANGE `featured_id` `featured_ids` VARCHAR(255) DEFAULT NULL;
