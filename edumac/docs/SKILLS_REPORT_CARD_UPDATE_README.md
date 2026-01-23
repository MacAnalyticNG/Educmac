# Skills Report Card - Optional Subjects Table Feature

## Overview
This update adds an optional subjects table to the skills report card, allowing different classes to have either:
- **Skills-only report cards** (default behavior)
- **Skills + Subjects combined report cards** (new feature)

The feature is controlled per marksheet template, giving you flexibility to design different report cards for different classes.

## Changes Made

### 1. Database Changes
**File:** `add_subjects_table_toggle.sql`

Added a new column to the `marksheet_template` table:
- **Column Name:** `subjects_table`
- **Type:** TINYINT(4)
- **Default:** 0 (subjects table hidden)
- **Values:**
  - `0` = Hide subjects table (skills only)
  - `1` = Show subjects table (skills + subjects)

**Installation:**
```sql
-- Run this SQL to add the new column
ALTER TABLE `marksheet_template`
ADD COLUMN `subjects_table` TINYINT(4) NOT NULL DEFAULT 0
COMMENT '0 = hide subjects table, 1 = show subjects table'
AFTER `result`;
```

### 2. Marksheet Template Designer Updates

#### Files Modified:
- `application/views/marksheet_template/index.php` (Add form)
- `application/views/marksheet_template/edit.php` (Edit form)
- `application/models/Marksheet_template_model.php` (Save logic)

#### Changes:
Added a new checkbox in the template designer form labeled **"Subjects Table (For Skills Report Card)"** that allows you to toggle whether the subjects table should appear on skills report cards using this template.

### 3. Skills Report Card Views Updated

#### Files Modified:
- `application/views/skills/junior_report_card_pdf.php` (PDF generation)
- `application/views/skills/junior_report_card_print.php` (Print view - NEW FILE)

#### Changes:
- Added conditional logic to check the `subjects_table` setting from the template
- When enabled, displays an "ACADEMIC PERFORMANCE" section showing:
  - Subject names
  - Scores
  - Grades
  - Remarks
- The subjects table appears **before** the skills assessment section
- Loads necessary models (subject_model, exam_model) dynamically

## How to Use

### Step 1: Run the SQL Migration
Execute the SQL file to add the new database column:
```bash
mysql -u your_username -p your_database < add_subjects_table_toggle.sql
```

### Step 2: Configure Templates

1. Navigate to **Marksheet Template** section in your admin panel
2. Either create a new template or edit an existing one
3. Scroll to the bottom checkbox options
4. Check **"Subjects Table (For Skills Report Card)"** if you want this template to show subjects alongside skills
5. Leave it unchecked for skills-only report cards
6. Save the template

### Step 3: Assign Templates to Classes

Configure different templates for different classes based on your needs:
- **Junior classes (e.g., Nursery, KG):** Use templates with subjects table **unchecked** (skills only)
- **Senior classes (e.g., Primary 1-6):** Use templates with subjects table **checked** (skills + subjects)

### Step 4: Generate Report Cards

When generating skills report cards:
1. Select the class and exam
2. Choose the appropriate template
3. The system will automatically show or hide the subjects table based on the template setting

## Example Use Cases

### Use Case 1: Nursery/Kindergarten Classes
- **Need:** Skills-based assessment only (no formal subjects)
- **Template Setting:** Subjects Table = **Unchecked**
- **Result:** Report card shows only skills assessment and teacher remarks

### Use Case 2: Primary 1-3 Classes
- **Need:** Both academic subjects and skills assessment
- **Template Setting:** Subjects Table = **Checked**
- **Result:** Report card shows:
  1. Academic Performance (subjects with scores/grades)
  2. Skills Assessment
  3. Teacher Remarks

### Use Case 3: Primary 4-6 Classes
- **Need:** Can be configured either way depending on school policy
- **Template Setting:** Your choice
- **Result:** Flexible based on your configuration

## Report Card Structure (When Subjects Table is Enabled)

```
┌─────────────────────────────────────┐
│     HEADER (Template Content)       │
├─────────────────────────────────────┤
│   ACADEMIC PERFORMANCE              │
│   ┌─────────────┬────┬────┬─────┐  │
│   │ Subject     │ ... │ ... │ ... │  │
│   ├─────────────┼────┼────┼─────┤  │
│   │ Mathematics │ 85 │  A │ Exc │  │
│   │ English     │ 78 │  B │ VG  │  │
│   │ ...         │... │... │ ... │  │
│   └─────────────┴────┴────┴─────┘  │
├─────────────────────────────────────┤
│   SKILLS ASSESSMENT REPORT          │
│   (Affective, Psychomotor, etc.)    │
├─────────────────────────────────────┤
│   TEACHER'S REMARKS                 │
│   - Class Teacher's Comment         │
│   - Head Teacher's Comment          │
├─────────────────────────────────────┤
│     FOOTER (Template Content)       │
└─────────────────────────────────────┘
```

## Technical Details

### Data Flow:
1. **Template Selection:** User selects template when generating report card
2. **Template Loading:** System loads template settings including `subjects_table` value
3. **Conditional Rendering:**
   - If `subjects_table == 1`: Fetch and display subject marks, grades, and remarks
   - If `subjects_table == 0`: Skip to skills assessment section
4. **Report Generation:** PDF/Print view renders with appropriate sections

### Models Loaded (When Subjects Table is Enabled):
- `subject_model`: To fetch subjects for the class/section
- `exam_model`: To calculate grades based on marks

### Database Tables Used:
- `marksheet_template`: Template configuration
- `mark`: Student marks for each subject
- `subject`: Subject details
- `exam`: Exam configuration
- `grade`: Grading scale

## Backward Compatibility

- **Existing Templates:** Will default to `subjects_table = 0` (hidden), maintaining current behavior
- **Existing Report Cards:** No changes to already generated PDFs
- **New Templates:** Can choose to enable or disable the feature

## Troubleshooting

### Issue: Subjects table not showing even when enabled
**Solution:**
- Verify the template has `subjects_table = 1` in the database
- Ensure students have marks entered for the selected exam
- Check that subjects are assigned to the class/section

### Issue: SQL migration fails
**Solution:**
- Ensure you're running the SQL with appropriate permissions
- Check if the column already exists
- Verify table name is correct (`marksheet_template`)

### Issue: Grades not displaying correctly
**Solution:**
- Verify grading scale is configured for the class
- Check that exam has a valid term_id
- Ensure marks are entered correctly in the marks table

## Support

For questions or issues related to this feature, please check:
1. The SQL migration ran successfully
2. Template configuration is correct
3. Required models are loaded (check error logs)
4. Subject marks are entered for the students

## Version Information

- **Feature:** Optional Subjects Table for Skills Report Card
- **Date:** December 18, 2025
- **Files Modified:** 5
- **Files Created:** 2
- **Database Changes:** 1 column added
