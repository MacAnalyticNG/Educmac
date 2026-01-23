# Academic Terms Migration Guide

## Overview

This guide documents the migration of the Educmac session module to support the Nigerian academic calendar system with **3 terms per session**. The system replaces the exam-specific `exam_term` table with a centralized `academic_terms` table that manages terms across all modules.

---

## Table of Contents

1. [System Architecture](#system-architecture)
2. [Database Schema](#database-schema)
3. [Migration Files](#migration-files)
4. [Controllers](#controllers)
5. [Helper Functions](#helper-functions)
6. [User Interface](#user-interface)
7. [Installation & Setup](#installation--setup)
8. [Troubleshooting](#troubleshooting)
9. [API Reference](#api-reference)

---

## System Architecture

### Nigerian Academic Calendar

The system implements the standard Nigerian academic calendar structure:

- **First Term**: September 1 - December 15 (15 weeks)
- **Second Term**: January 15 - April 15 (13 weeks)
- **Third Term**: May 1 - August 15 (15 weeks)

### Key Features

- **Centralized Terms**: Single `academic_terms` table replaces module-specific term tables
- **Multi-Branch Support**: Each term is associated with both a session and a branch
- **Auto-Creation**: Terms are automatically created when new sessions are added
- **Auto-Activation**: Current term is automatically activated based on date ranges
- **Inline Editing**: Quick adjustment of term dates with real-time validation
- **Bulk Operations**: Fix all sessions at once or individual sessions
- **Data Migration**: Tools to populate terms for existing sessions

---

## Database Schema

### academic_terms Table

```sql
CREATE TABLE `academic_terms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL DEFAULT 1,
  `term_name` varchar(50) NOT NULL,
  `term_order` tinyint(1) NOT NULL COMMENT '1=First Term, 2=Second Term, 3=Third Term',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `total_weeks` int(11) DEFAULT 0,
  `holidays` text DEFAULT NULL COMMENT 'JSON array of holiday dates',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_session_branch_term` (`session_id`,`branch_id`,`term_order`),
  KEY `idx_session_branch` (`session_id`,`branch_id`),
  KEY `idx_active` (`is_active`),
  KEY `idx_dates` (`start_date`,`end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

### Key Fields

- **session_id**: Links to `schoolyear` table
- **branch_id**: Links to `branch` table (defaults to 1 for main branch)
- **term_name**: Display name (e.g., "First Term", "Second Term")
- **term_order**: Numeric order (1, 2, or 3)
- **start_date / end_date**: Term date range
- **is_active**: Only one term per session/branch can be active at a time
- **total_weeks**: Auto-calculated based on date range
- **holidays**: JSON array for storing holiday dates (optional)

---

## Migration Files

### 196_create_academic_terms.php

Location: `application/migrations/196_create_academic_terms.php`

This migration file:

1. Creates the `academic_terms` table with proper indexes
2. Migrates existing data from `exam_term` table (if it exists)
3. Creates terms for all existing sessions with proper date ranges
4. Auto-activates the current term based on today's date
5. Handles both single-branch and multi-branch setups

**Running the migration:**

```bash
# Via web interface (recommended)
yoursite.com/migration_runner
```

---

## Controllers

### 1. Sessions.php (Enhanced)

Location: `application/controllers/Sessions.php`

#### Auto-Management Features

**Constructor Enhancement** (Lines 21-37):
- Calls `auto_manage_sessions()` on every page load
- Creates missing terms automatically
- Activates appropriate term based on current date

#### Key Methods

**quick_adjust_term()** (Lines 765-857):
```php
POST /sessions/quick_adjust_term
Parameters:
  - term_id: ID of the term to update
  - field: 'start_date' or 'end_date'
  - value: New date value (YYYY-MM-DD)

Response:
  - status: 'success' or 'error'
  - message: Human-readable message
  - total_weeks: Auto-calculated weeks (on success)
```

**bulk_adjust_terms()** (Lines 859-941):
```php
POST /sessions/bulk_adjust_terms
Parameters:
  - session_id: Session to update
  - branch_id: Branch to update
  - start_year: Starting year (YYYY)
  - end_year: Ending year (YYYY)

Response:
  - status: 'success' or 'error'
  - message: Human-readable message
  - updated_count: Number of terms updated
```

**get_session_terms_ajax()** (Lines 946-1016):
```php
POST /sessions/get_session_terms_ajax
Parameters:
  - session_id: Session ID
  - branch_id: Branch ID

Response:
  - status: 'success' or 'error'
  - message: Human-readable message
  - terms: Array of formatted term objects
```

---

### 2. Fix_terms.php

Location: `application/controllers/Fix_terms.php`

Diagnostic and repair tool for sessions with missing terms.

**Access**: `yoursite.com/fix_terms`

#### Features

- **Session Analysis**: Shows table of all sessions with term counts
- **Status Badges**: Visual indicators (Complete/Incomplete/No terms)
- **Individual Fix**: Fix specific session/branch combinations
- **Bulk Fix**: Fix all sessions at once

#### Routes

```php
GET  /fix_terms              - Main diagnostic page
GET  /fix_terms/fix_session/{session_id}/{branch_id} - Fix single session
GET  /fix_terms/fix_all      - Fix all sessions
```

#### Security

- Only accessible by superadmin users
- Checks authentication on every method

---

### 3. Migrate_terms.php

Location: `application/controllers/Migrate_terms.php`

Web-based migration tool for populating terms for existing sessions.

**Access**: `yoursite.com/migrate_terms/populate`

#### Features

- Creates all 3 terms for sessions that don't have them
- Validates session year format (YYYY/YYYY or YYYY-YYYY)
- Handles multi-branch setups
- Auto-activates current term based on date
- Displays detailed progress and statistics

#### Security

- Only accessible by superadmin users
- 5-minute execution time limit

---

## Helper Functions

Location: `application/helpers/general_helper.php` (Lines 788-991)

### get_term_id()

```php
/**
 * Get the current active term ID for a session/branch
 * @param int $session_id (optional) - defaults to current session
 * @param int $branch_id (optional) - defaults to current branch
 * @return int|null - Active term ID or null
 */
function get_term_id($session_id = null, $branch_id = null)
```

**Usage:**
```php
$term_id = get_term_id(); // Current session/branch
$term_id = get_term_id(5); // Session 5, current branch
$term_id = get_term_id(5, 2); // Session 5, branch 2
```

---

### get_current_term()

```php
/**
 * Get the current active term object for a session/branch
 * @param int $session_id (optional)
 * @param int $branch_id (optional)
 * @return object|null - Full term object or null
 */
function get_current_term($session_id = null, $branch_id = null)
```

**Usage:**
```php
$term = get_current_term();
if ($term) {
    echo $term->term_name; // "Second Term"
    echo $term->start_date; // "2026-01-15"
}
```
---

### get_session_terms()

```php
/**
 * Get all terms for a session/branch
 * @param int $session_id (optional)
 * @param int $branch_id (optional)
 * @return array - Array of term objects
 */
function get_session_terms($session_id = null, $branch_id = null)
```

**Usage:**
```php
$terms = get_session_terms(5, 1);
foreach ($terms as $term) {
    echo $term->term_name . ': ' . $term->start_date . ' - ' . $term->end_date;
}
```

---

### get_term_name()

```php
/**
 * Get term name by ID
 * @param int $term_id
 * @return string - Term name or empty string
 */
function get_term_name($term_id)
```

**Usage:**
```php
echo get_term_name(42); // "First Term"
```

---

### academic_terms_enabled()

```php
/**
 * Check if academic terms table exists
 * @return bool
 */
function academic_terms_enabled()
```

**Usage:**
```php
if (academic_terms_enabled()) {
    // Show term-specific features
}
```

---

## User Interface

### Sessions Management Page

Location: `application/views/sessions/index.php`

#### Inline Date Editing

**Features:**
- Click pencil icon to edit start/end dates
- Press Enter to save, ESC to cancel
- Auto-calculates total weeks on save
- Real-time AJAX updates without page reload

**UI Elements** (Lines 129-142):
```php
<td class="editable-date" data-field="start_date" data-term-id="<?=$term->id?>">
    <span class="date-display"><?=date('M d, Y', strtotime($term->start_date))?></span>
    <input type="date" class="form-control date-input" value="<?=$term->start_date?>" style="display:none;">
    <button class="btn btn-xs btn-info edit-date-btn">
        <i class="fas fa-pencil-alt"></i>
    </button>
</td>
```

#### JavaScript Handlers (Lines 551-658)

**Edit Mode Toggle:**
- Clicking pencil icon switches between view/edit mode
- Icon changes to save icon in edit mode

**Validation:**
- Ensures date is selected before saving
- Validates end_date is after start_date
- Shows friendly error messages

**AJAX Save:**
- Posts to `/sessions/quick_adjust_term`
- Updates display without page reload
- Shows success/error notifications

#### CSS Styling (Lines 662-685)

```css
.editable-date {
    position: relative;
}
.editable-date .edit-date-btn {
    margin-left: 8px;
    padding: 2px 6px;
    font-size: 11px;
}
.total-weeks-cell {
    font-weight: bold;
    color: #2196F3;
}
```

---

## Installation & Setup

### Step 1: Run Migration

**Option A: Web Interface (Recommended)**
```
1. Navigate to: yoursite.com/migration_runner
2. Click "Run Migrations"
3. Wait for completion message
```

**Option B: Manual SQL**
```sql
-- Import the migration file directly
source application/migrations/196_create_academic_terms.php
```

### Step 2: Populate Existing Sessions

**Option A: Web Interface**
```
1. Navigate to: yoursite.com/migrate_terms/populate
2. Review the session list
3. Click "Start Migration"
4. Wait for completion statistics
```

**Option B: Use Fix Terms Tool**
```
1. Navigate to: yoursite.com/fix_terms
2. Review sessions with missing terms
3. Click "Fix All Sessions" or individual "Fix" buttons
```

### Step 3: Verify Installation

1. Go to Sessions page: `yoursite.com/sessions`
2. Click "View Terms" on any session
3. Verify all 3 terms are present
4. Test inline editing by clicking pencil icons
5. Verify active term is marked correctly

---

## Troubleshooting

### Issue 1: Sessions Missing Terms

**Symptom**: Session shows only 1 or 2 terms instead of 3

**Solution**:
```
1. Navigate to: yoursite.com/fix_terms
2. Locate the session in the table
3. Click "Fix" button next to the session
4. Verify 3 terms are created
```

**Root Cause**: Session was created before migration or auto-creation failed

---

### Issue 2: Cannot Access Fix Terms Tool

**Symptom**: Getting 404 or redirected to home page

**Solution**: Ensure routes are configured correctly in `application/config/routes.php`:

```php
$route['fix_terms'] = 'fix_terms/index';
$route['fix_terms/(:any)'] = 'fix_terms/$1';
$route['migrate_terms'] = 'migrate_terms/index';
$route['migrate_terms/(:any)'] = 'migrate_terms/$1';
```

These routes must be placed **before** the catch-all route:
```php
$route['(:any)'] = 'home/index/$1';
```

**Location**: Lines 139-142 in `routes.php`

---

### Issue 3: Inline Editing Not Working

**Symptom**: JavaScript error "Uncaught SyntaxError" or dates not saving

**Checks**:
1. Verify jQuery is loaded on the page
2. Check browser console for errors (F12)
3. Verify CSRF token is valid
4. Check server response in Network tab

**Common Fix**: Clear browser cache and reload page

---

### Issue 4: Wrong Term Activated

**Symptom**: Incorrect term is marked as active

**Solution**:
```php
// Manual activation via database
UPDATE academic_terms
SET is_active = 0
WHERE session_id = [SESSION_ID] AND branch_id = [BRANCH_ID];

UPDATE academic_terms
SET is_active = 1
WHERE id = [CORRECT_TERM_ID];
```

Or use the Sessions UI:
1. View session terms
2. Click "Activate" on correct term
3. Confirm activation

---

### Issue 5: Date Validation Errors

**Symptom**: "End date must be after start date" when dates are correct

**Cause**: Date format or timezone issues

**Solution**:
1. Ensure dates are in YYYY-MM-DD format
2. Check server timezone settings in `php.ini`
3. Verify database timezone matches server timezone

---

## API Reference

### AJAX Endpoints

#### 1. Quick Adjust Term

**Endpoint**: `POST /sessions/quick_adjust_term`

**Request:**
```json
{
  "term_id": 42,
  "field": "start_date",
  "value": "2025-09-01",
  "csrf_token": "..."
}
```

**Success Response:**
```json
{
  "status": "success",
  "message": "Term updated successfully",
  "total_weeks": 15
}
```

**Error Response:**
```json
{
  "status": "error",
  "message": "End date must be after start date"
}
```

---

#### 2. Bulk Adjust Terms

**Endpoint**: `POST /sessions/bulk_adjust_terms`

**Request:**
```json
{
  "session_id": 5,
  "branch_id": 1,
  "start_year": "2025",
  "end_year": "2026",
  "csrf_token": "..."
}
```

**Success Response:**
```json
{
  "status": "success",
  "message": "Terms adjusted successfully",
  "updated_count": 3
}
```

---

#### 3. Get Session Terms

**Endpoint**: `POST /sessions/get_session_terms_ajax`

**Request:**
```json
{
  "session_id": 5,
  "branch_id": 1,
  "csrf_token": "..."
}
```

**Success Response:**
```json
{
  "status": "success",
  "message": "Terms loaded successfully",
  "terms": [
    {
      "id": 15,
      "term_name": "First Term",
      "term_order": 1,
      "start_date": "Sep 01, 2025",
      "end_date": "Dec 15, 2025",
      "total_weeks": 15,
      "is_active": 1
    },
    ...
  ]
}
```

---

### Database Queries

#### Get Active Term

```php
$term = $this->db
    ->where('session_id', $session_id)
    ->where('branch_id', $branch_id)
    ->where('is_active', 1)
    ->get('academic_terms')
    ->row();
```

#### Get All Terms for Session

```php
$terms = $this->db
    ->where('session_id', $session_id)
    ->where('branch_id', $branch_id)
    ->order_by('term_order', 'ASC')
    ->get('academic_terms')
    ->result();
```

#### Activate a Term

```php
// Deactivate all terms for session/branch
$this->db
    ->where('session_id', $session_id)
    ->where('branch_id', $branch_id)
    ->update('academic_terms', ['is_active' => 0]);

// Activate specific term
$this->db
    ->where('id', $term_id)
    ->update('academic_terms', [
        'is_active' => 1,
        'updated_at' => date('Y-m-d H:i:s')
    ]);
```

#### Count Terms for Session

```php
$count = $this->db
    ->where('session_id', $session_id)
    ->where('branch_id', $branch_id)
    ->count_all_results('academic_terms');
```

---

## File Reference

### Modified Files

1. **application/migrations/196_create_academic_terms.php**
   - Creates academic_terms table
   - Migrates existing data
   - Auto-populates terms

2. **application/controllers/Sessions.php**
   - Lines 21-37: Auto-management in constructor
   - Lines 765-857: quick_adjust_term() method
   - Lines 859-941: bulk_adjust_terms() method
   - Lines 946-1016: get_session_terms_ajax() method

3. **application/views/sessions/index.php**
   - Lines 129-142: Inline editable date fields
   - Lines 551-658: JavaScript for inline editing
   - Lines 662-685: CSS styling

4. **application/helpers/general_helper.php**
   - Lines 788-991: Helper functions for term access

5. **application/config/routes.php**
   - Lines 139-142: Routes for fix_terms and migrate_terms

6. **application/config/migration.php**
   - Updated version from 195 to 196

### New Files

1. **application/controllers/Fix_terms.php**
   - Diagnostic and repair tool
   - Session analysis
   - Individual and bulk fixes

2. **application/controllers/Migrate_terms.php**
   - Web-based migration tool
   - Population script for existing sessions

3. **populate_existing_terms.php** (CLI - Optional)
   - Command-line migration alternative
   - Located in project root

4. **fix_terms_2025_2026.sql** (Manual SQL - Optional)
   - Manual SQL queries for specific session
   - Located in project root

---

## Best Practices

### For Developers

1. **Always use helper functions** instead of direct database queries:
   ```php
   // Good
   $term_id = get_term_id();

   // Avoid
   $term_id = $this->db->where(...)->get('academic_terms')->row()->id;
   ```

2. **Check if terms exist** before creating new terms:
   ```php
   if (academic_terms_enabled()) {
       $existing = $this->db->where([
           'session_id' => $session_id,
           'branch_id' => $branch_id,
           'term_order' => $term_order
       ])->count_all_results('academic_terms');

       if ($existing == 0) {
           // Create term
       }
   }
   ```

3. **Always deactivate other terms** when activating a new term:
   ```php
   // Deactivate all first
   $this->db->where('session_id', $session_id)
            ->where('branch_id', $branch_id)
            ->update('academic_terms', ['is_active' => 0]);

   // Then activate one
   $this->db->where('id', $term_id)
            ->update('academic_terms', ['is_active' => 1]);
   ```

### For Administrators

1. **Run fix_terms after bulk imports**: Always check sessions after importing data
2. **Verify term dates**: Ensure dates align with your school's calendar
3. **Backup before migration**: Create database backup before running migration
4. **Test on staging first**: Run migration on test environment first
5. **Monitor active terms**: Only one term per session/branch should be active

---

## Changelog

### Version 1.0 (January 2026)

**Added:**
- academic_terms table with proper indexes
- Auto-creation of terms for new sessions
- Auto-activation based on current date
- Inline date editing with validation
- Helper functions for term access
- Fix_terms diagnostic tool
- Migrate_terms population tool
- Multi-branch support
- Bulk operations support

**Changed:**
- Replaced exam_term with academic_terms
- Updated Sessions controller with auto-management
- Enhanced Sessions UI with term management

**Deprecated:**
- exam_term table (data migrated to academic_terms)

---

## Support

### Common Questions

**Q: Can I customize the term dates?**
A: Yes, use the inline editing feature or bulk adjustment methods.

**Q: What happens to old exam_term data?**
A: It's automatically migrated to academic_terms during migration.

**Q: Can I have different dates for different branches?**
A: Yes, each branch can have different term dates for the same session.

**Q: How do I add a 4th term?**
A: The system is designed for 3 terms. Adding a 4th term requires database schema changes.

**Q: Can I delete terms?**
A: Not recommended. Terms are linked to other data. Instead, adjust dates or mark as inactive.

### Getting Help

1. Check this documentation first
2. Review the Troubleshooting section
3. Check browser console for JavaScript errors (F12)
4. Check server logs for PHP errors
5. Use the Fix Terms tool to diagnose issues

---

## Exam Module Migration

### Overview

The Exam module has been successfully migrated to use the centralized `academic_terms` table instead of the deprecated `exam_term` table. This provides better integration with the Nigerian academic system and eliminates data duplication.

### What Changed

#### Migration 197: Migrate Exam to Academic Terms

**File**: `application/migrations/197_migrate_exam_to_academic_terms.php`

This migration:
1. Migrates any remaining `exam_term` data to `academic_terms`
2. Updates all `exam.term_id` references to point to `academic_terms.id`
3. Drops the obsolete `exam_term` table

**Running the migration:**
```
Navigate to: yoursite.com/migration_runner
```

#### Code Changes

**1. Exam_model.php** (`application/models/Exam_model.php`)
- **Line 16**: Updated `getExamByID()` to JOIN with `academic_terms` instead of `exam_term`
- **Lines 74-84**: Deprecated `termSave()` method - now redirects to Sessions module

**2. Exam Controller** (`application/controllers/Exam.php`)
- **Lines 115-144**: Deprecated term management methods:
  - `term()` - Redirects to Sessions page
  - `term_edit()` - Returns error message
  - `term_delete()` - Redirects to Sessions page

**3. Exam Views**

- **exam/index.php** (Line 50): Changed from `get_type_name_by_id('exam_term', ...)` to `get_term_name(...)`
- **exam/index.php** (Lines 120-134): Updated term dropdown to use `get_session_terms()` helper
- **exam/edit.php** (Lines 28-42): Updated term dropdown to use `get_session_terms()` helper

**4. Ajax Controller** (`application/controllers/Ajax.php`)
- **Line 34**: Updated `getExamByBranch()` to use `get_term_name()` helper

**5. Application_model** (`application/models/Application_model.php`)
- **Line 210**: Updated `exam_name_by_id()` to use `get_term_name()` helper

**6. Frontend Views**
- **home/exam_results.php** (Line 44): Updated to use `get_term_name()`
- **home/admit_card.php** (Line 44): Updated to use `get_term_name()`
- **userrole/report_card.php** (Line 322): Updated to use `get_term_name()`

**7. Sidebar Menu** (`application/views/layout/sidebar.php`)
- **Lines 999-1009**: Commented out deprecated "Exam Term" menu item
- Users should now manage terms via Sessions > View Terms

### How to Use Terms in Exams

#### Creating an Exam with a Term

1. Navigate to **Exam > Exam List**
2. Click **Create Exam** tab
3. Select a term from the dropdown (populated from `academic_terms` for current session)
4. Fill other exam details and save

#### Managing Terms

Terms are now managed centrally through the **Sessions** module:

1. Navigate to **Sessions**
2. Click **View Terms** on any session
3. You'll see all 3 terms (First, Second, Third)
4. Edit term dates inline by clicking the pencil icon
5. Activate the current term as needed

### Migration Data Flow

```
OLD SYSTEM:
exam_term table → exam.term_id

NEW SYSTEM:
academic_terms table → exam.term_id
```

**Data Migration Process:**
1. Migration 694 created `academic_terms` and migrated existing `exam_term` data
2. Migration 197 updated all `exam.term_id` references to point to `academic_terms.id`
3. Migration 197 dropped the `exam_term` table

### Backward Compatibility

The migration maintains backward compatibility:

- Existing exam records are automatically updated to reference `academic_terms`
- If an exam's old term cannot be matched, `term_id` is set to 0 (no term)
- All helper functions and views updated to use new system
- Deprecated methods redirect users to the Sessions module

### Common Questions

**Q: What happens to my existing exams?**
A: All existing exams are automatically updated to reference the new `academic_terms` table. The migration matches terms by name and session/branch.

**Q: Can I still create exams without a term?**
A: Yes, the term field is optional. You can leave it as "Select" to create an exam without a specific term.

**Q: Where do I manage terms now?**
A: Terms are managed via **Sessions > View Terms**. Each session has 3 terms (First, Second, Third) that you can edit.

**Q: What if migration 197 fails?**
A: Check the migration output for errors. Common issues:
- `academic_terms` table doesn't exist (run migration 694 first)
- Database permissions
- Foreign key constraints

**Q: Can I rollback the migration?**
A: Yes, the migration has a `down()` method that recreates the `exam_term` table. However, data migration reversal is not supported. Create a database backup before migrating.

### Troubleshooting

#### Issue: Exam term dropdown is empty

**Solution:**
1. Ensure the session has terms: Navigate to Sessions > View Terms
2. If no terms exist, use Fix Terms tool: `yoursite.com/fix_terms`
3. Verify migration 694 ran successfully

#### Issue: "Term not found" error when viewing exam

**Solution:**
1. The exam references a term_id that doesn't exist in `academic_terms`
2. Run migration 197 again to update references
3. Or manually set exam.term_id to a valid term or 0

#### Issue: Old "Exam Term" menu still appears

**Solution:**
The menu has been commented out in sidebar.php. If it still appears:
1. Clear browser cache
2. Check `application/views/layout/sidebar.php` lines 999-1009
3. Ensure the menu item is commented out

### Database Schema Changes

**Before:**
```sql
exam_term:
- id (INT)
- name (LONGTEXT)
- branch_id (INT)
- session_id (INT)

exam:
- term_id → references exam_term.id
```

**After:**
```sql
-- exam_term table dropped

academic_terms:
- id (INT)
- session_id (INT)
- branch_id (INT)
- term_name (VARCHAR)
- term_order (INT) -- 1, 2, or 3
- start_date (DATE)
- end_date (DATE)
- is_active (TINYINT)
- total_weeks (INT)
- holidays (LONGTEXT)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)

exam:
- term_id → references academic_terms.id
```

### Files Modified for Exam Migration

| File | Lines Changed | Description |
|------|---------------|-------------|
| `migrations/197_migrate_exam_to_academic_terms.php` | New file | Migration script |
| `models/Exam_model.php` | 16, 74-84 | Updated to use academic_terms |
| `controllers/Exam.php` | 115-144 | Deprecated term methods |
| `views/exam/index.php` | 50, 120-134 | Updated term display/dropdown |
| `views/exam/edit.php` | 28-42 | Updated term dropdown |
| `controllers/Ajax.php` | 34 | Updated getExamByBranch |
| `models/Application_model.php` | 210 | Updated exam_name_by_id |
| `views/home/exam_results.php` | 44 | Updated term display |
| `views/home/admit_card.php` | 44 | Updated term display |
| `views/userrole/report_card.php` | 322 | Updated term display |
| `views/layout/sidebar.php` | 999-1009 | Commented out menu item |
| `config/migration.php` | 72 | Updated version to 197 |

### Testing Checklist

After migration, verify:

- [ ] Navigate to Exam > Exam List - terms display correctly
- [ ] Create new exam - term dropdown shows current session's terms
- [ ] Edit existing exam - term is preserved and correct
- [ ] View exam on student portal - term displays in exam name
- [ ] Generate report card - exam name includes term
- [ ] Print admit card - exam name includes term
- [ ] Check exam results page - terms display correctly
- [ ] Verify old Exam Term menu is hidden in sidebar
- [ ] Access yoursite.com/exam/term - redirects to Sessions page
- [ ] All 3 terms exist for each session (use Fix Terms if needed)

---

## License

This migration is part of the Educmac School Management System.

---

**Document Version**: 2.0
**Last Updated**: January 23, 2026
**Author**: System Migration Team
