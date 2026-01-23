-- Create section_marksheet_template table for mapping sections to templates
-- This allows different sections (Nursery, Primary, JSS) to use different report card templates

CREATE TABLE IF NOT EXISTS `section_marksheet_template` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `branch_id` int(11) UNSIGNED NOT NULL,
  `section_id` int(11) NOT NULL,
  `template_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `branch_id` (`branch_id`),
  KEY `section_id` (`section_id`),
  KEY `template_id` (`template_id`),
  UNIQUE KEY `unique_branch_section` (`branch_id`, `section_id`),
  CONSTRAINT `section_marksheet_template_branch_fk` FOREIGN KEY (`branch_id`) REFERENCES `branch` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `section_marksheet_template_section_fk` FOREIGN KEY (`section_id`) REFERENCES `section` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  CONSTRAINT `section_marksheet_template_template_fk` FOREIGN KEY (`template_id`) REFERENCES `marksheet_template` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
