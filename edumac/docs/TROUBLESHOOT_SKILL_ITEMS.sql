-- ============================================================================
-- TROUBLESHOOTING GUIDE: Skill Items Not Showing in Skill Rating Page
-- ============================================================================
-- Run these queries in order to diagnose why skill items aren't appearing
-- Replace the values in <angle brackets> with your actual values
-- ============================================================================

-- ============================================================================
-- STEP 1: Verify Branch ID
-- ============================================================================
-- First, confirm the branch_id you're working with
SELECT id, name FROM branch ORDER BY id;
-- Note the branch_id you're testing (e.g., branch_id = 2)


-- ============================================================================
-- STEP 2: Check Skills Categories for the Branch
-- ============================================================================
-- Replace <branch_id> with your actual branch ID (e.g., 2)
SELECT
    id,
    name,
    type,
    class_level,
    status,
    branch_id,
    created_at
FROM skills_categories
WHERE branch_id = 2
ORDER BY type, name;

-- Expected: You should see categories here
-- If EMPTY: No categories exist for this branch - CREATE CATEGORIES FIRST!
-- If status = 'inactive': Change to 'active' or create active categories


-- ============================================================================
-- STEP 3: Check Skills Items Linked to Those Categories
-- ============================================================================
-- Replace <branch_id> with your actual branch ID
SELECT
    si.id AS item_id,
    si.item_name,
    si.category_id,
    sc.name AS category_name,
    sc.type AS category_type,
    sc.class_level,
    si.status AS item_status,
    sc.status AS category_status,
    sc.branch_id,
    si.display_order
FROM skills_items si
LEFT JOIN skills_categories sc ON si.category_id = sc.id
WHERE sc.branch_id = 2
ORDER BY sc.type, si.display_order, si.item_name;

-- Expected: You should see skill items here
-- If EMPTY: No skill items exist for this branch's categories - CREATE ITEMS!
-- Check the statuses:
--   - item_status should be 'active'
--   - category_status should be 'active'


-- ============================================================================
-- STEP 4: Check for Orphaned Items (Items with Missing Categories)
-- ============================================================================
SELECT
    si.id AS item_id,
    si.item_name,
    si.category_id,
    si.status
FROM skills_items si
LEFT JOIN skills_categories sc ON si.category_id = sc.id
WHERE sc.id IS NULL;

-- Expected: Should be EMPTY
-- If NOT EMPTY: These items are linked to deleted categories - DELETE or REASSIGN them


-- ============================================================================
-- STEP 5: Check Skills Ratings (Rating Scale) for the Branch
-- ============================================================================
-- Replace <branch_id> with your actual branch ID
SELECT
    id,
    label,
    numeric_value,
    description,
    status,
    branch_id,
    display_order
FROM skills_ratings
WHERE branch_id = <branch_id>
ORDER BY numeric_value DESC;

-- Expected: You should see ratings (A, B, C or Excellent, Good, etc.)
-- If EMPTY: No rating scale exists for this branch - CREATE RATINGS FIRST!
-- If status = 'inactive': Change to 'active'


-- ============================================================================
-- STEP 6: Check What the System Query Actually Returns
-- ============================================================================
-- This mimics the exact query used by getItems() method
-- Replace <branch_id> with your actual branch ID

SELECT
    si.*,
    sc.name as category_name,
    sc.type as category_type,
    sc.class_level
FROM skills_items si
LEFT JOIN skills_categories sc ON si.category_id = sc.id
WHERE sc.branch_id = <branch_id>
  AND si.status = 'active'
  AND sc.status = 'active'
ORDER BY si.category_id ASC, si.display_order ASC, si.item_name ASC;

-- Expected: This is what the rating page should display
-- If EMPTY: Check the WHERE conditions - one of them is failing


-- ============================================================================
-- STEP 7: Check for Specific Class Level Issues
-- ============================================================================
-- If you're filtering by class_level (primary/junior/senior)
-- Replace <branch_id> and <class_level> with your values

SELECT
    si.id AS item_id,
    si.item_name,
    sc.name AS category_name,
    sc.class_level,
    sc.branch_id
FROM skills_items si
JOIN skills_categories sc ON si.category_id = sc.id
WHERE sc.branch_id = <branch_id>
  AND sc.class_level = '<class_level>'  -- e.g., 'primary', 'junior', 'senior'
  AND si.status = 'active'
  AND sc.status = 'active';

-- Expected: Items filtered by class level
-- If EMPTY: No items exist for this class level in this branch


-- ============================================================================
-- STEP 8: Count Items by Branch (Summary View)
-- ============================================================================
SELECT
    sc.branch_id,
    b.name AS branch_name,
    sc.class_level,
    sc.type,
    COUNT(si.id) AS total_items,
    SUM(CASE WHEN si.status = 'active' THEN 1 ELSE 0 END) AS active_items,
    SUM(CASE WHEN si.status = 'inactive' THEN 1 ELSE 0 END) AS inactive_items
FROM skills_categories sc
LEFT JOIN skills_items si ON si.category_id = sc.id
LEFT JOIN branch b ON sc.branch_id = b.id
WHERE sc.status = 'active'
GROUP BY sc.branch_id, sc.class_level, sc.type
ORDER BY sc.branch_id, sc.class_level, sc.type;

-- Expected: Overview of all items across all branches
-- Use this to compare different branches


-- ============================================================================
-- STEP 9: Check if Categories Have the Wrong branch_id
-- ============================================================================
-- Sometimes categories are accidentally assigned to the wrong branch
SELECT
    sc.id,
    sc.name,
    sc.branch_id,
    b.name AS branch_name,
    COUNT(si.id) AS item_count
FROM skills_categories sc
LEFT JOIN branch b ON sc.branch_id = b.id
LEFT JOIN skills_items si ON si.category_id = sc.id
GROUP BY sc.id, sc.name, sc.branch_id, b.name
ORDER BY sc.branch_id, sc.name;

-- Expected: Categories should be in the correct branch
-- If branch_id is wrong: UPDATE skills_categories SET branch_id = <correct_id> WHERE id = <category_id>;


-- ============================================================================
-- COMMON FIXES
-- ============================================================================

-- FIX 1: Activate inactive categories
-- UPDATE skills_categories SET status = 'active' WHERE branch_id = <branch_id> AND status = 'inactive';

-- FIX 2: Activate inactive items
-- UPDATE skills_items SET status = 'active' WHERE id IN (SELECT id FROM skills_items WHERE status = 'inactive');

-- FIX 3: Move category to correct branch
-- UPDATE skills_categories SET branch_id = <correct_branch_id> WHERE id = <category_id>;

-- FIX 4: Check if items are linked to categories from a different branch
SELECT
    si.id AS item_id,
    si.item_name,
    si.category_id,
    sc.name AS category_name,
    sc.branch_id AS category_branch
FROM skills_items si
JOIN skills_categories sc ON si.category_id = sc.id
WHERE sc.branch_id != <expected_branch_id>;
-- If results found, the items are in the wrong branch's categories


-- ============================================================================
-- STEP 10: Test the Complete Rating Entry Flow
-- ============================================================================
-- This simulates what happens when a teacher enters ratings
-- Replace values as needed

-- 10a. Get categories for branch and class level
SELECT * FROM skills_categories
WHERE branch_id = <branch_id>
  AND class_level = '<class_level>'
  AND status = 'active';

-- 10b. Get items for those categories
SELECT si.*
FROM skills_items si
JOIN skills_categories sc ON si.category_id = sc.id
WHERE sc.branch_id = <branch_id>
  AND sc.class_level = '<class_level>'
  AND si.status = 'active'
  AND sc.status = 'active';

-- 10c. Get rating scale for branch
SELECT * FROM skills_ratings
WHERE branch_id = <branch_id>
  AND status = 'active'
ORDER BY numeric_value DESC;


-- ============================================================================
-- DIAGNOSTIC SUMMARY QUERY
-- ============================================================================
-- Run this to get a complete overview for a specific branch

SELECT
    'Categories' AS component,
    COUNT(*) AS total,
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active_count
FROM skills_categories
WHERE branch_id = <branch_id>

UNION ALL

SELECT
    'Items' AS component,
    COUNT(DISTINCT si.id) AS total,
    SUM(CASE WHEN si.status = 'active' THEN 1 ELSE 0 END) AS active_count
FROM skills_items si
JOIN skills_categories sc ON si.category_id = sc.id
WHERE sc.branch_id = <branch_id>

UNION ALL

SELECT
    'Ratings' AS component,
    COUNT(*) AS total,
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active_count
FROM skills_ratings
WHERE branch_id = <branch_id>;

-- Expected output:
-- Categories: Should have at least 1 active
-- Items: Should have at least 1 active
-- Ratings: Should have at least 1 active
-- If ANY of these are 0, that's your problem!



 Showing rows 0 - 2 (3 total, Query took 0.0002 seconds.) [type: AFFECTIVE... - COGNITIVE...] [name: PERSONALITY AND CHARACTER... - SKILLS AND ABILITIES...]
SELECT id, name, type, class_level, status, branch_id, created_at FROM skills_categories WHERE branch_id = 2 ORDER BY type, name;
 Profiling [ Edit inline ] [ Edit ] [ Explain SQL ] [ Create PHP code ] [ Refresh ]
 Show all	|			Number of rows: 
25
Filter rows: 
Search this table
Sort by key: 
None
Full texts
id
name Ascending 2
type Ascending 1
class_level
status
branch_id
created_at

Edit Edit
Copy Copy
Delete Delete
21
PERSONALITY AND CHARACTER
affective
junior
active
2
2025-12-27 19:35:12

Edit Edit
Copy Copy
Delete Delete
22
GROSS MOTOR SKILL
psychomotor
primary
active
2
2025-12-27 19:36:06

Edit Edit
Copy Copy
Delete Delete
23
SKILLS AND ABILITIES
cognitive
primary
active
2
2025-12-27 19:37:00





 Showing rows 0 - 11 (12 total, Query took 0.0007 seconds.) [display_order: 7... - 6...] [item_name: FRIENDLY AND COURTEOUS... - SPEAKING...]
SELECT si.id AS item_id, si.item_name, si.category_id, sc.name AS category_name, sc.type AS category_type, sc.class_level, si.status AS item_status, sc.status AS category_status, sc.branch_id, si.display_order FROM skills_items si LEFT JOIN skills_categories sc ON si.category_id = sc.id WHERE sc.branch_id = 2 ORDER BY sc.type, si.display_order, si.item_name;
 Profiling [ Edit inline ] [ Edit ] [ Explain SQL ] [ Create PHP code ] [ Refresh ]
 Show all	|			Number of rows: 
25
Filter rows: 
Search this table
item_id
item_name
category_id
category_name
category_type
class_level
item_status
category_status
branch_id
display_order
71
Friendly And Courteous
21
PERSONALITY AND CHARACTER
affective
junior
active
active
2
7
72
Punctual
21
PERSONALITY AND CHARACTER
affective
junior
active
active
2
8
73
Clean And Orderly
21
PERSONALITY AND CHARACTER
affective
junior
active
active
2
9
74
Attentive
21
PERSONALITY AND CHARACTER
affective
junior
active
active
2
10
75
Respectful
21
PERSONALITY AND CHARACTER
affective
junior
active
active
2
11
76
test
21
PERSONALITY AND CHARACTER
affective
junior
active
active
2
12
65
Writing
23
SKILLS AND ABILITIES
cognitive
primary
active
active
2
1
66
Rhymes
23
SKILLS AND ABILITIES
cognitive
primary
active
active
2
2
67
Creative Development
23
SKILLS AND ABILITIES
cognitive
primary
active
active
2
3
68
Number Work
23
SKILLS AND ABILITIES
cognitive
primary
active
active
2
4
69
Letter Work
23
SKILLS AND ABILITIES
cognitive
primary
active
active
2
5
70
Speaking
23
SKILLS AND ABILITIES
cognitive
primary
active
active
2
6
