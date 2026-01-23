# Exam Results Marksheet Template Integration - Implementation Guide

## Overview

This implementation converts the `/exam_results` page from using hardcoded result printing logic to using the marksheet template system. The marksheet templates now support flexible display of both academic results and skills assessment data, giving administrators full control over what appears on report cards.

## What Was Changed

### 1. Database Schema Update

**File:** `add_show_skills_to_marksheet_template.sql`

Added a new column to the `marksheet_template` table:
- **Column:** `show_skills`
- **Type:** TINYINT(4)
- **Default:** 0
- **Description:** Controls whether skills assessment data should be displayed on the report card
  - 0 = Hide skills section
  - 1 = Show skills section

**To apply this change, run:**
```sql
ALTER TABLE `marksheet_template`
ADD COLUMN `show_skills` TINYINT(4) NOT NULL DEFAULT 0
COMMENT '0 = Hide Skills Section, 1 = Show Skills Section' AFTER `subjects_table`;
```

### 2. Model Updates

**File:** `application/models/Marksheet_template_model.php`

Added `show_skills` field to the save functionality:
- The model now captures the `show_skills` checkbox value from forms
- Stores it in the database when creating or updating templates

### 3. View Updates

**Files Modified:**
- `application/views/marksheet_template/index.php` (Add form)
- `application/views/marksheet_template/edit.php` (Edit form)

**Changes:**
- Added "Skills (Skills Assessment Section)" checkbox to both add and edit forms
- This gives administrators the ability to enable/disable skills display per template

### 4. New Unified Report Card View

**File:** `application/views/home/reportCardWithTemplate.php`

Created a new comprehensive report card view that:
- Uses marksheet templates for consistent branding
- Supports both academic and skills data display
- Respects template settings for what to show/hide
- Uses the same header/footer system as internal report cards

**Key Features:**
- **Template-Based Header/Footer:** Uses the marksheet template's header and footer content with tag replacement
- **Conditional Academic Results:** Shows subject marks only if `subjects_table` = 1 AND academic data exists
- **Conditional Skills Assessment:** Shows skills data only if `show_skills` = 1 AND skills data exists
- **Flexible Display:** Can show academic only, skills only, both, or neither depending on template settings and available data
- **Background and Styling:** Uses template background image and layout settings

### 5. Controller Update

**File:** `application/controllers/Home.php`

**Method:** `examResultsPrintFn()`

**Changes:**
- Simplified the logic to use a single unified report card view
- Removed complex conditional branching for different card types
- Now loads `reportCardWithTemplate.php` for all result displays
- The template settings control what gets displayed, not the controller

**Before:**
```php
// Complex logic checking for hasAcademicData, hasSkillsData
// Multiple view files: juniorReportCard, reportCard, etc.
```

**After:**
```php
// Simple unified approach
$this->data['studentID'] = $userID['id'];
$this->data['examID'] = $examID;
// ... other data
$card_data = $this->load->view('home/reportCardWithTemplate', $this->data, true);
```

## How to Use

### Step 1: Run Database Migration

Execute the SQL file to add the new column:
```bash
mysql -u your_username -p your_database < add_show_skills_to_marksheet_template.sql
```

### Step 2: Configure Marksheet Templates

1. Navigate to **Marksheet Template** in your admin panel
2. Create a new template or edit an existing one
3. Configure the display options:

   **For Academic-Only Report Cards:**
   - ✅ Check "Subjects Table"
   - ❌ Uncheck "Skills"

   **For Skills-Only Report Cards:**
   - ❌ Uncheck "Subjects Table"
   - ✅ Check "Skills"

   **For Combined Report Cards (Academic + Skills):**
   - ✅ Check "Subjects Table"
   - ✅ Check "Skills"

   **For Skills-Based with No Subjects Table:**
   - ❌ Uncheck "Subjects Table"
   - ✅ Check "Skills"

### Step 3: Set Default Template

1. Go to **School Settings** or **Branch Settings**
2. Set the `default_marksheet_temp` to your desired template ID
3. This template will be used for all exam results displays

### Step 4: Test the Exam Results Page

1. Visit `/exam_results` on your frontend
2. Enter exam details, academic year, and register number
3. Submit the form
4. The report card will display according to your template settings

## Template Settings Reference

### Existing Settings (Already Available)

- **subjects_table:** Controls whether academic subjects table is displayed
- **attendance_percentage:** Show/hide attendance section
- **grading_scale:** Show/hide grading scale table
- **position:** Show/hide class position
- **cumulative_average:** Show/hide cumulative average
- **class_average:** Show/hide class average
- **subject_position:** Show/hide subject positions
- **remark:** Show/hide remarks column
- **result:** Show/hide pass/fail result

### New Setting

- **show_skills:** Controls whether skills assessment section is displayed

## Data Flow

1. **User submits exam results form** on `/exam_results`
2. **Controller validates** register number, exam, and session
3. **Controller checks** parent account status and fee payment threshold
4. **Controller loads template** using `default_marksheet_temp` from branch settings
5. **View determines display** based on:
   - Template `subjects_table` setting + availability of academic marks
   - Template `show_skills` setting + availability of skills data
6. **Report card renders** with appropriate sections shown/hidden

## Skills Assessment Display

When `show_skills` is enabled and skills data exists, the report card shows:

1. **Rating Scale:** Legend showing what each rating label means
2. **Skills Categories:** Displayed in 2-column grid layout
   - Affective Domain (blue border)
   - Psychomotor Domain (green border)
   - Cognitive Domain (orange border)
3. **Skills Items:** Each category shows:
   - Skill item name
   - Rating badge (visual label)
   - Rating remark/description
4. **Teacher Remarks:** Class teacher and head teacher comments

## Academic Results Display

When `subjects_table` is enabled and academic data exists, the report card shows:

1. **Subject List:** All enrolled subjects
2. **Mark Distribution:** Based on exam configuration (CA, Exam, etc.)
3. **Grades/Points:** If exam type supports grading
4. **Grand Total:** Overall marks and percentage
5. **Average Grade Point:** For grade-based exams
6. **Subject Position:** If enabled in template

## Benefits of This Approach

1. **Unified System:** One template system for both internal and external (frontend) reports
2. **Flexibility:** Administrators can create different templates for different purposes
3. **Consistency:** Same branding and layout across all report cards
4. **Easy Maintenance:** Changes to templates automatically apply to all reports
5. **Skills Integration:** Skills assessment data is now a first-class feature, not an afterthought
6. **No Code Changes Needed:** Administrators can control display through UI, no developer needed

## Backward Compatibility

- **Existing templates** will work as before because `show_skills` defaults to 0
- **Old report card views** are still available if needed
- **No breaking changes** to existing functionality

## Troubleshooting

### Skills Not Showing
- Verify `show_skills` = 1 in the template
- Check that skills assessment data exists for the student/exam
- Ensure the skills_model is properly loaded

### Subjects Not Showing
- Verify `subjects_table` = 1 in the template
- Check that academic marks have been entered for the exam
- Verify the exam configuration is correct

### Template Not Loading
- Check that `default_marksheet_temp` is set in branch/school settings
- Verify the template exists in the database
- Check template permissions

### Blank Report Card
- If neither academic nor skills data exists, the card will be mostly empty
- Ensure data has been entered for the exam before generating reports
- Check that the student is enrolled in the correct class/section

## Files Modified Summary

### New Files:
1. `add_show_skills_to_marksheet_template.sql` - Database migration
2. `application/views/home/reportCardWithTemplate.php` - Unified report card view
3. `EXAM_RESULTS_TEMPLATE_IMPLEMENTATION.md` - This documentation

### Modified Files:
1. `application/models/Marksheet_template_model.php` - Added show_skills field
2. `application/views/marksheet_template/index.php` - Added show_skills checkbox
3. `application/views/marksheet_template/edit.php` - Added show_skills checkbox
4. `application/controllers/Home.php` - Simplified examResultsPrintFn method

## Next Steps

1. **Run the database migration**
2. **Configure your templates** with appropriate settings
3. **Test with various data scenarios** (skills only, academic only, both)
4. **Train administrators** on the new template options
5. **Update user documentation** if needed

## Support

If you encounter any issues:
1. Check the database column was added correctly
2. Verify template settings in the admin panel
3. Check browser console for JavaScript errors
4. Review server logs for PHP errors
5. Ensure all required data (academic/skills) is entered

---

**Implementation Date:** January 20, 2026
**Version:** 1.0
**Status:** Complete
