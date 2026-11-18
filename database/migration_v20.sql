-- ============================================================
--  NexVest — Migration v20
--  Optional tag on each investment (real estate or index fund):
--  a short label + colour shown on the investor-side card.
--  Run once in phpMyAdmin against the live database,
--  BEFORE uploading the new code.
-- ============================================================

ALTER TABLE `investments`
  ADD COLUMN `tag`       VARCHAR(30) DEFAULT NULL AFTER `notify_on_launch`,
  ADD COLUMN `tag_color` ENUM('blue','green','amber','red','purple','slate') NOT NULL DEFAULT 'blue' AFTER `tag`;
