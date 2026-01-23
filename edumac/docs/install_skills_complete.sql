-- =====================================================
-- SKILLS MODULE - COMPLETE INSTALLATION SCRIPT
-- Academium v12.1
-- =====================================================
-- This script installs the complete Skills Assessment Module
-- Run this script once to set up everything
-- =====================================================

-- =====================================================
-- PART 1: CREATE TABLES
-- =====================================================

-- Table 1: Skills Categories
CREATE TABLE IF NOT EXISTS `skills_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `type` enum('affective','psychomotor','cognitive') NOT NULL,
  `class_level` enum('primary','junior','senior') NOT NULL DEFAULT 'primary',
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `branch_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `branch_id` (`branch_id`),
  KEY `status` (`status`),
  KEY `class_level` (`class_level`),
  KEY `idx_category_type_level` (`type`,`class_level`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table 2: Skills Items
CREATE TABLE IF NOT EXISTS `skills_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `status` (`status`),
  KEY `display_order` (`display_order`),
  CONSTRAINT `skills_items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `skills_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table 3: Skills Ratings
CREATE TABLE IF NOT EXISTS `skills_ratings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(10) NOT NULL,
  `numeric_value` int(11) NOT NULL,
  `description` varchar(100) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `branch_id` int(11) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `branch_id` (`branch_id`),
  KEY `status` (`status`),
  KEY `display_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table 4: Student Skills Ratings
CREATE TABLE IF NOT EXISTS `skills_students_ratings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `enroll_id` int(11) NOT NULL,
  `skill_item_id` int(11) NOT NULL,
  `rating_id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `term_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `teacher_remarks` text DEFAULT NULL,
  `head_teacher_remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_student_skill_exam` (`student_id`,`skill_item_id`,`exam_id`,`term_id`,`session_id`),
  KEY `student_id` (`student_id`),
  KEY `enroll_id` (`enroll_id`),
  KEY `skill_item_id` (`skill_item_id`),
  KEY `rating_id` (`rating_id`),
  KEY `exam_id` (`exam_id`),
  KEY `term_id` (`term_id`),
  KEY `class_id` (`class_id`),
  KEY `section_id` (`section_id`),
  KEY `session_id` (`session_id`),
  KEY `branch_id` (`branch_id`),
  KEY `teacher_id` (`teacher_id`),
  KEY `idx_student_ratings_lookup` (`student_id`,`exam_id`,`session_id`),
  KEY `idx_teacher_ratings` (`teacher_id`,`class_id`,`section_id`),
  KEY `idx_term_ratings` (`term_id`,`class_id`,`session_id`),
  CONSTRAINT `skills_students_ratings_ibfk_1` FOREIGN KEY (`skill_item_id`) REFERENCES `skills_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `skills_students_ratings_ibfk_2` FOREIGN KEY (`rating_id`) REFERENCES `skills_ratings` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `skills_students_ratings_ibfk_3` FOREIGN KEY (`student_id`) REFERENCES `student` (`id`) ON DELETE CASCADE,
  CONSTRAINT `skills_students_ratings_ibfk_4` FOREIGN KEY (`enroll_id`) REFERENCES `enroll` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- PART 2: INSERT SAMPLE DATA
-- (Adjust branch_id to match your default branch - usually 1)
-- =====================================================

-- Sample Skills Categories (using branch_id = 1)
INSERT INTO `skills_categories` (`name`, `type`, `class_level`, `description`, `status`, `branch_id`) VALUES
('Affective Skills', 'affective', 'primary', 'Social and emotional development skills', 'active', 1),
('Psychomotor Skills', 'psychomotor', 'primary', 'Physical and motor skills development', 'active', 1),
('Cognitive Skills', 'cognitive', 'primary', 'Mental and intellectual development skills', 'active', 1);

-- Sample Skills Items for Affective Category
INSERT INTO `skills_items` (`category_id`, `item_name`, `description`, `display_order`, `status`) VALUES
(1, 'Punctuality', 'Arrives to class on time', 1, 'active'),
(1, 'Honesty', 'Demonstrates truthfulness and integrity', 2, 'active'),
(1, 'Obedience', 'Follows instructions and rules', 3, 'active'),
(1, 'Politeness', 'Shows courtesy and respect to others', 4, 'active'),
(1, 'Cooperation', 'Works well with peers and teachers', 5, 'active'),
(1, 'Neatness', 'Maintains clean and organized appearance', 6, 'active'),
(1, 'Leadership', 'Takes initiative and guides others', 7, 'active'),
(1, 'Attentiveness', 'Pays attention during lessons', 8, 'active');

-- Sample Skills Items for Psychomotor Category
INSERT INTO `skills_items` (`category_id`, `item_name`, `description`, `display_order`, `status`) VALUES
(2, 'Handwriting', 'Quality and legibility of writing', 1, 'active'),
(2, 'Games/Sports', 'Participation in physical activities', 2, 'active'),
(2, 'Handling Tools', 'Proper use of scissors, pencils, etc.', 3, 'active'),
(2, 'Drawing/Painting', 'Artistic expression abilities', 4, 'active'),
(2, 'Verbal Fluency', 'Speaking and communication skills', 5, 'active'),
(2, 'Musical Skills', 'Participation in music activities', 6, 'active');

-- Sample Skills Items for Cognitive Category
INSERT INTO `skills_items` (`category_id`, `item_name`, `description`, `display_order`, `status`) VALUES
(3, 'Memory Retention', 'Ability to remember and recall information', 1, 'active'),
(3, 'Problem Solving', 'Analytical and critical thinking skills', 2, 'active'),
(3, 'Creativity', 'Innovative and imaginative thinking', 3, 'active'),
(3, 'Comprehension', 'Understanding of concepts taught', 4, 'active'),
(3, 'Concentration', 'Ability to focus on tasks', 5, 'active');

-- Sample Rating Scale (using branch_id = 1)
INSERT INTO `skills_ratings` (`label`, `numeric_value`, `description`, `display_order`, `branch_id`, `status`) VALUES
('A', 5, 'Excellent', 1, 1, 'active'),
('B', 4, 'Very Good', 2, 1, 'active'),
('C', 3, 'Good', 3, 1, 'active'),
('D', 2, 'Fair', 4, 1, 'active'),
('E', 1, 'Needs Improvement', 5, 1, 'active');

-- =====================================================
-- PART 3: ADD PERMISSIONS TO PERMISSION TABLE
-- Module ID 9 = Exam Master
-- =====================================================

-- Get the next available permission ID
-- Check your current max ID first: SELECT MAX(id) FROM permission;
-- Adjust the starting ID below if needed (example uses 500)

INSERT INTO `permission` (`id`, `module_id`, `name`, `prefix`, `show_view`, `show_add`, `show_edit`, `show_delete`, `created_at`) VALUES
(500, 9, 'Skills Categories', 'skills_categories', 1, 1, 1, 1, NOW()),
(501, 9, 'Skills Items', 'skills_items', 1, 1, 1, 1, NOW()),
(502, 9, 'Skills Ratings', 'skills_ratings', 1, 1, 1, 1, NOW()),
(503, 9, 'Skills Rating Entry', 'skills_rating_entry', 1, 1, 1, 1, NOW());

-- =====================================================
-- PART 4: ADD STAFF PRIVILEGES
-- This grants permissions to roles
-- =====================================================

-- Super Admin (role_id = 1) - Full Access to All Skills Permissions
INSERT INTO `staff_privileges` (`role_id`, `permission_id`, `is_add`, `is_edit`, `is_view`, `is_delete`) VALUES
(1, 500, 1, 1, 1, 1),  -- Skills Categories
(1, 501, 1, 1, 1, 1),  -- Skills Items
(1, 502, 1, 1, 1, 1),  -- Skills Ratings
(1, 503, 1, 1, 1, 1);  -- Skills Rating Entry

-- Admin (role_id = 2) - Full Access to All Skills Permissions
INSERT INTO `staff_privileges` (`role_id`, `permission_id`, `is_add`, `is_edit`, `is_view`, `is_delete`) VALUES
(2, 500, 1, 1, 1, 1),  -- Skills Categories
(2, 501, 1, 1, 1, 1),  -- Skills Items
(2, 502, 1, 1, 1, 1),  -- Skills Ratings
(2, 503, 1, 1, 1, 1);  -- Skills Rating Entry

-- Teacher (role_id = 3) - View Only for Setup, Full Access for Rating Entry
INSERT INTO `staff_privileges` (`role_id`, `permission_id`, `is_add`, `is_edit`, `is_view`, `is_delete`) VALUES
(3, 500, 0, 0, 1, 0),  -- Skills Categories - View Only
(3, 501, 0, 0, 1, 0),  -- Skills Items - View Only
(3, 502, 0, 0, 1, 0),  -- Skills Ratings - View Only
(3, 503, 1, 1, 1, 1);  -- Skills Rating Entry - Full Access

ALTER TABLE `marksheet_template`
ADD COLUMN `subjects_table` TINYINT(4) NOT NULL DEFAULT 0
COMMENT '0 = hide subjects table, 1 = show subjects table'
AFTER `result`;


-- =====================================================
-- Skills Module Permissions
-- Add these permissions to enable role-based access control
-- for the Skills Assessment module
-- =====================================================

-- Role IDs from your system:
-- 1 = Super Admin
-- 2 = Admin
-- 3 = Teacher
-- 4 = Accountant
-- 5 = Librarian
-- 6 = Parent
-- 7 = Student
-- 8 = Receptionist

-- =====================================================
-- STEP 1: Add permissions for Super Admin (Full Access)
-- =====================================================

INSERT INTO `permission` (`role_id`, `page_name`, `page_id`, `is_view`, `is_add`, `is_edit`, `is_delete`, `is_search`) VALUES
(1, 'skills_categories', 0, 1, 1, 1, 1, 1),
(1, 'skills_items', 0, 1, 1, 1, 1, 1),
(1, 'skills_ratings', 0, 1, 1, 1, 1, 1),
(1, 'skills_rating_entry', 0, 1, 1, 1, 1, 1);

-- =====================================================
-- STEP 2: Add permissions for Admin (Full Access)
-- =====================================================

INSERT INTO `permission` (`role_id`, `page_name`, `page_id`, `is_view`, `is_add`, `is_edit`, `is_delete`, `is_search`) VALUES
(2, 'skills_categories', 0, 1, 1, 1, 1, 1),
(2, 'skills_items', 0, 1, 1, 1, 1, 1),
(2, 'skills_ratings', 0, 1, 1, 1, 1, 1),
(2, 'skills_rating_entry', 0, 1, 1, 1, 1, 1);

-- =====================================================
-- STEP 3: Add permissions for Teacher
-- Categories, Items, Ratings: View & Search only
-- Rating Entry: Full access (teachers need to rate students)
-- =====================================================

INSERT INTO `permission` (`role_id`, `page_name`, `page_id`, `is_view`, `is_add`, `is_edit`, `is_delete`, `is_search`) VALUES
(3, 'skills_categories', 0, 1, 0, 0, 0, 1),
(3, 'skills_items', 0, 1, 0, 0, 0, 1),
(3, 'skills_ratings', 0, 1, 0, 0, 0, 1),
(3, 'skills_rating_entry', 0, 1, 1, 1, 1, 1);

-- =====================================================
-- STEP 3: Add Skills module to modules_manage table (if using module system)
-- =====================================================

-- Check if your system uses modules_manage table
-- If yes, add an entry for the skills module per branch
-- Replace branch_id=1 with your actual branch ID(s)

INSERT INTO `modules_manage` (`branch_id`, `module_name`, `status`) VALUES
(1, 'skills_assessment', 1);

-- For multiple branches, repeat the above with different branch_ids

-- =====================================================
-- STEP 4: Optional - Add to permission_modules table (if exists)
-- =====================================================

-- If your system has a permission_modules table for organizing modules
-- Get the next available ID from the permission_modules table first

INSERT INTO `permission_modules` (`id`, `name`, `prefix`, `icon`, `module_group`) VALUES
(NULL, 'Skills Assessment', 'skills_assessment', 'fas fa-star', 'exam_master');
