-- Add show_skills column to marksheet_template table
-- This allows templates to control whether skills assessment data should be displayed

ALTER TABLE `marksheet_template`
ADD COLUMN `show_skills` TINYINT(4) NOT NULL DEFAULT 0
COMMENT '0 = Hide Skills Section, 1 = Show Skills Section' AFTER `subjects_table`;
