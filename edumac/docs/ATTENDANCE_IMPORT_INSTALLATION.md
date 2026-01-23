# Attendance Import/Export Feature - Installation Guide

## Overview
This feature allows bulk import and export of student attendance using Excel files. It generates templates with all school days (excluding weekends and holidays) for the active term and allows uploading filled attendance data.

## Files Created/Modified

### 1. Controller
- **File**: `application/controllers/Attendance.php`
- **Methods Added**:
  - `export_import()` - Main page for import/export
  - `download_attendance_template()` - Generates and downloads Excel template
  - `download_file()` - Downloads generated files
  - `import_attendance_excel()` - Processes uploaded Excel file
  - `get_school_days()` - Helper to get school days excluding weekends/holidays

### 2. View
- **File**: `application/views/attendance/export_import.php`
- Features:
  - Instructions section with status indicators (P, A, L, HD)
  - Class and Section filters
  - Download template button (AJAX)
  - Upload panel for Excel files
  - Error display for import failures
  - Active term information display

## Requirements

### PHP Library: PhpSpreadsheet
The feature requires PhpSpreadsheet library for Excel operations.

**Installation Steps:**

1. Navigate to `application/third_party/` directory:
   ```bash
   cd application/third_party/
   ```

2. If you don't have composer in that directory, create a composer.json file:
   ```json
   {
       "require": {
           "phpoffice/phpspreadsheet": "^1.29"
       }
   }
   ```

3. Install via Composer:
   ```bash
   composer install
   ```

   OR if you already have a vendor folder elsewhere, you can install it there and copy:
   ```bash
   composer require phpoffice/phpspreadsheet
   ```

4. Verify installation - the library should be at:
   ```
   application/third_party/vendor/phpoffice/phpspreadsheet/
   application/third_party/vendor/autoload.php
   ```

## Adding to Navigation Menu

To add a link to the attendance menu, you need to modify the menu configuration:

### Option 1: Add to Attendance Submenu

Find your attendance menu configuration (usually in `application/config/` or in the view layouts) and add:

```php
<li>
    <a href="<?=base_url('attendance/export_import')?>">
        <i class="fas fa-file-import"></i> 
        <span><?=translate('import')?>/<?=translate('export')?></span>
    </a>
</li>
```

### Option 2: Direct Access
Users can access the feature directly via:
```
https://your-domain.com/attendance/export_import
```

## Excel Template Structure

The generated template will have:
- **Column A**: Student ID (Enroll ID) - DO NOT MODIFY
- **Column B**: Register No - DO NOT MODIFY
- **Column C**: Roll - DO NOT MODIFY
- **Column D**: Student Name
- **Columns E onwards**: One column per school day in the active term

### Attendance Status Values:
- **P** = Present
- **A** = Absent
- **L** = Late  
- **HD** = Half Day

### Data Validation:
Each attendance cell has dropdown validation with allowed values: P, A, L, HD

## Features

### 1. Template Generation
- Automatically excludes weekends (configured in branch settings)
- Automatically excludes holidays
- Only includes dates within the active academic term
- Pre-filled student information
- Dropdown validation for attendance status

### 2. Import Processing
- Validates attendance status values
- Updates existing attendance records
- Inserts new attendance records
- Sends SMS notifications for absent students (if SMS is configured)
- Displays detailed error report for failed imports

### 3. Term Integration
- Respects active academic term dates
- Stores term_id with each attendance record
- Displays active term information on the page

## Permissions

The feature uses existing attendance permissions:
- **Required Permission**: `student_attendance` with `is_add` access
- Only users with this permission can access the import/export page

## Troubleshooting

### 1. PhpSpreadsheet Not Found
**Error**: `require_once(): Failed opening required 'APPPATH/third_party/vendor/autoload.php'`

**Solution**: Install PhpSpreadsheet as described in Requirements section

### 2. Template Download Fails
**Possible Causes**:
- No students in selected class/section
- No active term configured
- PhpSpreadsheet not installed

**Solution**: Check error message in AJAX response

### 3. Import Fails
**Check**:
- File format is .xlsx or .xls
- Student IDs and Register Numbers match database
- Attendance status values are valid (P, A, L, HD)
- Dates in template match school days
- File size is under server upload limit

### 4. Permission Issues
**Error**: Access Denied

**Solution**: Ensure logged-in user has `student_attendance` permission with add rights

## Testing the Feature

1. **Generate Template**:
   - Go to Attendance > Import/Export
   - Select Branch (if super admin), Class, and Section
   - Click "Download Template"
   - Verify Excel file downloads with correct students and dates

2. **Fill Template**:
   - Open downloaded Excel file
   - Fill attendance status for each student/date
   - Save file

3. **Upload**:
   - Go back to Import/Export page
   - Select same Class and Section
   - Upload filled Excel file
   - Verify success message and check for any errors

4. **Verify Import**:
   - Go to Student Attendance Report
   - Select same class/section and date range
   - Verify attendance records were imported correctly

## Database Structure

The feature interacts with these tables:
- **student_attendance**: Stores attendance records
  - Required columns: enroll_id, status, remark, date, term_id, branch_id
- **enroll**: Student enrollment data
- **student**: Student information
- **academic_terms**: Term dates and information

## Future Enhancements

Potential improvements:
1. Support for period-based attendance import
2. Bulk export of existing attendance data
3. Import attendance with remarks
4. Support for CSV format
5. Import validation preview before actual import
6. Template with existing attendance data pre-filled

## Support

For issues or questions:
1. Check error messages in browser console (F12)
2. Check server PHP error logs
3. Verify PhpSpreadsheet installation
4. Ensure permissions are correctly configured
5. Test with small class (5-10 students) first

## Credits

Implementation based on Academium-BGS attendance import/export system.
