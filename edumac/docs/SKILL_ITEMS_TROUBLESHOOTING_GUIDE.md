# Troubleshooting Guide: Skill Items Not Showing in Rating Page

## Quick Diagnosis Checklist

If skill items exist for a branch but aren't showing on the skill rating page, follow this checklist:

### ✅ Quick Check (5 Minutes)

1. **Verify branch_id is correct**
2. **Check categories exist and are active**
3. **Check items exist and are active**
4. **Check items are linked to correct categories**
5. **Check rating scale exists for the branch**

---

## Common Causes & Solutions

### 🔴 Problem 1: Categories Have Wrong Status

**Symptom:** Items exist but don't appear on rating page

**Cause:** Categories are set to 'inactive'

**Check:**
```sql
SELECT id, name, status, branch_id
FROM skills_categories
WHERE branch_id = 2;  -- Replace 2 with your branch_id
```

**Solution:**
```sql
UPDATE skills_categories
SET status = 'active'
WHERE branch_id = 2 AND status = 'inactive';
```

---

### 🔴 Problem 2: Items Have Wrong Status

**Symptom:** Items exist but don't show in rating interface

**Cause:** Items are set to 'inactive'

**Check:**
```sql
SELECT si.id, si.item_name, si.status, sc.branch_id
FROM skills_items si
JOIN skills_categories sc ON si.category_id = sc.id
WHERE sc.branch_id = 2;
```

**Solution:**
```sql
UPDATE skills_items si
JOIN skills_categories sc ON si.category_id = sc.id
SET si.status = 'active'
WHERE sc.branch_id = 2 AND si.status = 'inactive';
```

---

### 🔴 Problem 3: No Categories Exist for Branch

**Symptom:** Nothing shows up at all

**Cause:** No categories created for this branch

**Check:**
```sql
SELECT COUNT(*) AS category_count
FROM skills_categories
WHERE branch_id = 2;
```

**Solution:** Create categories first via the web interface:
- Go to Skills → Categories
- Select the branch
- Create at least one category (Affective, Psychomotor, or Cognitive)

---

### 🔴 Problem 4: Items Linked to Wrong Categories

**Symptom:** Items show for Branch 1 but not Branch 2

**Cause:** Items are linked to categories from another branch

**Check:**
```sql
SELECT
    si.id,
    si.item_name,
    si.category_id,
    sc.name AS category_name,
    sc.branch_id AS actual_branch
FROM skills_items si
JOIN skills_categories sc ON si.category_id = sc.id
WHERE si.id = 123;  -- Replace with your item_id
```

**Solution:** Re-assign item to correct category:
```sql
UPDATE skills_items
SET category_id = <correct_category_id>
WHERE id = <item_id>;
```

---

### 🔴 Problem 5: Orphaned Items (Category Deleted)

**Symptom:** Items appear in database but nowhere in UI

**Cause:** The category was deleted but items still reference it

**Check:**
```sql
SELECT si.id, si.item_name, si.category_id
FROM skills_items si
LEFT JOIN skills_categories sc ON si.category_id = sc.id
WHERE sc.id IS NULL;
```

**Solution:** Either delete orphaned items or re-assign them:
```sql
-- Option 1: Delete orphaned items
DELETE FROM skills_items WHERE id IN (
    SELECT si.id FROM skills_items si
    LEFT JOIN skills_categories sc ON si.category_id = sc.id
    WHERE sc.id IS NULL
);

-- Option 2: Re-assign to valid category
UPDATE skills_items
SET category_id = <valid_category_id>
WHERE category_id NOT IN (SELECT id FROM skills_categories);
```

---

### 🔴 Problem 6: Wrong Class Level

**Symptom:** Items exist but don't show for specific class

**Cause:** Category class_level doesn't match the selected class

**Check:**
```sql
SELECT
    sc.id,
    sc.name,
    sc.class_level,
    COUNT(si.id) AS item_count
FROM skills_categories sc
LEFT JOIN skills_items si ON si.category_id = sc.id
WHERE sc.branch_id = 2
GROUP BY sc.id, sc.name, sc.class_level;
```

**Explanation:**
- Categories have `class_level` (primary, junior, senior)
- If you're viewing junior class but categories are for primary, items won't show

**Solution:** Create categories with the correct class_level or change existing:
```sql
UPDATE skills_categories
SET class_level = 'junior'
WHERE id = <category_id>;
```

---

### 🔴 Problem 7: No Rating Scale for Branch

**Symptom:** Items show but can't rate students

**Cause:** No rating scale (A, B, C, etc.) exists for the branch

**Check:**
```sql
SELECT id, label, numeric_value, status
FROM skills_ratings
WHERE branch_id = 2;
```

**Solution:** Create ratings via web interface:
- Go to Skills → Ratings
- Select branch
- Create rating scale (e.g., A=5, B=4, C=3, D=2, E=1)

---

## Step-by-Step Debugging Process

### Step 1: Run Diagnostic Summary

```sql
-- Replace <branch_id> with your actual branch ID (e.g., 2)
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
```

**Expected Output:**
```
component   | total | active_count
------------|-------|-------------
Categories  |   3   |      3
Items       |  15   |     15
Ratings     |   5   |      5
```

**If any active_count is 0, that's your problem!**

---

### Step 2: Simulate the Application Query

Run the exact query the application uses:

```sql
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
ORDER BY si.category_id ASC, si.display_order ASC;
```

**This is what should appear on the rating page.**

---

### Step 3: Check Application Code

If database queries return data but UI still doesn't show items, check:

#### 1. Check Controller (Skills.php:586-607)

Look at the `getSkillsByLevel()` method:
```php
public function getSkillsByLevel()
{
    $class_level = $this->input->post('class_level');
    $branch_id = $this->input->post('branch_id');

    $categories = $this->skills_model->getCategories($branch_id, 'active', $class_level);
    $skills = [];

    foreach ($categories as $category) {
        $items = $this->skills_model->getItems($category['id'], 'active');
        foreach ($items as $item) {
            $skills[] = $item;
        }
    }

    echo json_encode($skills);
}
```

**Issue:** Line 600 passes `$category['id']` to `getItems()`, but the model expects `branch_id`!

---

### Step 4: Check Browser Console

1. Open the rating entry page
2. Press F12 to open Developer Tools
3. Go to Network tab
4. Look for AJAX calls to `getSkillsByLevel`
5. Check the response - does it return empty array `[]`?

**If response is empty but database has data, there's a PHP/AJAX issue.**

---

## Database Relationship Diagram

```
┌─────────────────┐
│     branch      │
│  id | name      │
└────────┬────────┘
         │
         │ branch_id (FK)
         │
         ▼
┌──────────────────────┐         ┌──────────────────┐
│ skills_categories    │         │  skills_ratings  │
│ id                   │         │  id              │
│ name                 │         │  label (A,B,C)   │
│ type                 │         │  numeric_value   │
│ class_level          │         │  branch_id (FK)  │
│ status               │         │  status          │
│ branch_id (FK)       │         └──────────────────┘
└─────────┬────────────┘
          │
          │ category_id (FK)
          │
          ▼
┌──────────────────────┐
│   skills_items       │
│   id                 │
│   item_name          │
│   category_id (FK)   │◄─── NO direct branch_id!
│   status             │      Uses category's branch_id
└──────────────────────┘
```

**Key Point:** Items don't have `branch_id` directly - they inherit it through the category!

---

## Testing Procedure

### Test 1: Manual Database Test

```sql
-- 1. Pick a branch
SET @branch = 2;

-- 2. Check everything exists
SELECT 'PASS' AS test, 'Categories Exist' AS check_name
FROM skills_categories WHERE branch_id = @branch AND status = 'active' LIMIT 1
UNION ALL
SELECT 'PASS', 'Items Exist'
FROM skills_items si
JOIN skills_categories sc ON si.category_id = sc.id
WHERE sc.branch_id = @branch AND si.status = 'active' LIMIT 1
UNION ALL
SELECT 'PASS', 'Ratings Exist'
FROM skills_ratings WHERE branch_id = @branch AND status = 'active' LIMIT 1;

-- Should return 3 rows with 'PASS'
-- If any row is missing, that component needs to be created
```

---

### Test 2: Compare Branches

```sql
-- See what Branch 1 has vs Branch 2
SELECT
    sc.branch_id,
    COUNT(DISTINCT sc.id) AS categories,
    COUNT(DISTINCT si.id) AS items,
    (SELECT COUNT(*) FROM skills_ratings WHERE branch_id = sc.branch_id) AS ratings
FROM skills_categories sc
LEFT JOIN skills_items si ON si.category_id = sc.id
GROUP BY sc.branch_id;
```

**Expected:** Each branch should have similar counts

---

## Prevention Tips

1. **Always create categories before items**
2. **Ensure branch_id is set when creating categories**
3. **Keep status = 'active' for items you want to use**
4. **Don't delete categories that have items** - set status to inactive instead
5. **Create rating scales for each branch**

---

## Quick Fix Script

Run this to activate everything for a specific branch:

```sql
-- Replace 2 with your branch_id
SET @target_branch = 2;

-- Activate all categories
UPDATE skills_categories
SET status = 'active'
WHERE branch_id = @target_branch;

-- Activate all items in those categories
UPDATE skills_items si
JOIN skills_categories sc ON si.category_id = sc.id
SET si.status = 'active'
WHERE sc.branch_id = @target_branch;

-- Activate all ratings
UPDATE skills_ratings
SET status = 'active'
WHERE branch_id = @target_branch;

-- Verify
SELECT
    'Categories' AS component, COUNT(*) AS active_count
FROM skills_categories
WHERE branch_id = @target_branch AND status = 'active'
UNION ALL
SELECT 'Items', COUNT(si.id)
FROM skills_items si
JOIN skills_categories sc ON si.category_id = sc.id
WHERE sc.branch_id = @target_branch AND si.status = 'active'
UNION ALL
SELECT 'Ratings', COUNT(*)
FROM skills_ratings
WHERE branch_id = @target_branch AND status = 'active';
```

---

## Still Not Working?

If you've tried everything above and items still don't show:

1. **Check PHP error logs** - Look for errors in CodeIgniter logs
2. **Check browser console** - Look for JavaScript errors
3. **Verify permissions** - Ensure user has `skills_rating_entry` permission
4. **Clear cache** - Clear browser cache and CodeIgniter cache
5. **Check the actual AJAX calls** - Use browser DevTools to see what data is being sent/received

---

## Files to Check

If the issue persists, examine these files:

- **Controller:** `application/controllers/Skills.php:586-607` (getSkillsByLevel method)
- **Model:** `application/models/Skills_model.php:111-129` (getItems method)
- **View:** `application/views/skills/rating_entry.php` (if it exists)
- **Database:** Tables `skills_categories`, `skills_items`, `skills_ratings`

---

## Contact Support

If none of these solutions work, provide this information when asking for help:

1. Results from Step 1 (Diagnostic Summary query)
2. Results from Step 2 (Application query simulation)
3. Browser console errors (screenshot)
4. Network tab AJAX response (screenshot)
5. Branch ID you're testing with
