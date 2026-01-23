# Quick Start Guide: Exam Results with Marksheet Templates

## What Changed?

The `/exam_results` page now uses your marksheet template system instead of hardcoded HTML. You can now control what appears on report cards (academic results, skills assessment, or both) through your marksheet template settings.

## Installation Steps

### 1. Run Database Migration (REQUIRED)

Open your MySQL client or phpMyAdmin and run this command:

```sql
ALTER TABLE `marksheet_template`
ADD COLUMN `show_skills` TINYINT(4) NOT NULL DEFAULT 0
COMMENT '0 = Hide Skills Section, 1 = Show Skills Section' AFTER `subjects_table`;
```

**OR** import the SQL file:
```bash
mysql -u your_username -p your_database < add_show_skills_to_marksheet_template.sql
```

### 2. Configure Your Marksheet Template

Go to **Admin Panel → Marksheet Template** and either create a new template or edit an existing one.

You'll see two new checkboxes at the bottom:

1. **✅ Subjects Table (For Skills Report Card)**
   - Check this if you want academic subjects/marks to appear
   - Uncheck to hide academic results

2. **✅ Skills (Skills Assessment Section)**  
   - Check this if you want skills assessment to appear
   - Uncheck to hide skills data

**Template Configurations Examples:**

| Use Case | Subjects Table | Skills | Result |
|----------|---------------|--------|--------|
| Traditional academic report card | ✅ | ❌ | Shows only subjects and marks |
| Skills-based report card | ❌ | ✅ | Shows only skills assessment |
| Combined report card | ✅ | ✅ | Shows both subjects and skills |
| Minimal report card | ❌ | ❌ | Shows only student info and header/footer |

### 3. Set Default Template (Optional)

If you haven't already, go to **School Settings** and set your `default_marksheet_temp` to the template you want to use for exam results.

### 4. Test It Out

1. Go to your website's `/exam_results` page
2. Select an exam, academic year, and enter a register number
3. Click Submit
4. The report card will appear with the sections you enabled in your template

## Features

### For Administrators

✅ **Full Control**: Enable/disable skills and academic results per template  
✅ **Consistent Branding**: Same header/footer across all report cards  
✅ **No Coding Required**: Configure everything through the UI  
✅ **Multiple Templates**: Create different templates for different purposes  

### For Users/Parents

✅ **Professional Layout**: Report cards use your school's branding  
✅ **Skills Support**: View detailed skills assessment if enabled  
✅ **Academic Results**: Traditional marks and grades if enabled  
✅ **Print-Friendly**: Clean layout optimized for printing  

## What Shows on the Report Card?

### Header Section (Always Shows)
- Uses your template's header content
- Supports all existing tags: {name}, {register_no}, {class}, etc.

### Academic Results Section (Conditional)
Shows when:
- Template has `subjects_table` = ✅ (checked)
- AND student has academic marks entered

Displays:
- Subject list
- Mark distribution (CA, Exam, etc.)
- Grades and points
- Grand total and average
- Subject positions (if enabled)

### Skills Assessment Section (Conditional)
Shows when:
- Template has `show_skills` = ✅ (checked)
- AND student has skills assessment data

Displays:
- Rating scale legend
- Skills by category (Affective, Psychomotor, Cognitive)
- Individual ratings for each skill item
- Teacher and head teacher remarks

### Additional Sections (Based on Template Settings)
- Attendance (if enabled in template)
- Grading scale (if enabled)
- Class position (if enabled)
- Pass/Fail result (if enabled)

### Footer Section (Always Shows)
- Uses your template's footer content
- Signature lines
- Print date

## Troubleshooting

### "Skills not showing even though I checked the box"
- Make sure skills assessment data has been entered for that student/exam
- Check that the skills module is properly configured

### "Subjects not showing even though I checked the box"
- Make sure academic marks have been entered for that exam
- Verify the exam has mark distribution configured

### "Report card is blank"
- If both academic and skills data are missing, only header/footer will show
- Enter either academic marks or skills assessment data

### "Changes to template not reflecting"
- Clear your browser cache
- Make sure you saved the template changes
- Verify you're using the correct template

## Files Created/Modified

**New Files:**
- `add_show_skills_to_marksheet_template.sql`
- `application/views/home/reportCardWithTemplate.php`
- `EXAM_RESULTS_TEMPLATE_IMPLEMENTATION.md`
- `QUICK_START_GUIDE.md`

**Modified Files:**
- `application/controllers/Home.php`
- `application/models/Marksheet_template_model.php`
- `application/views/marksheet_template/index.php`
- `application/views/marksheet_template/edit.php`

## Need Help?

1. Make sure the database migration ran successfully
2. Check that your template has the new checkboxes
3. Verify data (academic or skills) exists for the test student
4. Review the full documentation in `EXAM_RESULTS_TEMPLATE_IMPLEMENTATION.md`

---

**Version:** 1.0  
**Date:** January 20, 2026  
**Status:** Ready to Use
