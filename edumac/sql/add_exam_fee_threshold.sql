-- Add fee_threshold_percentage column to exam table
-- This column stores the minimum fee payment percentage required to view/print results
-- Default value is 0 (no restriction)

ALTER TABLE `exam` ADD COLUMN `fee_threshold_percentage` DECIMAL(5,2) DEFAULT 0.00 AFTER `publish_result`;
COMMENT ON COLUMN `exam`.`fee_threshold_percentage` IS 'Minimum fee payment percentage required to view/print exam results';
