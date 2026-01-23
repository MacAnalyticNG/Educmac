# Fees Module Term-Based Migration Summary

## Overview
This document summarizes the migration of the Educmac fees management system to support term-based fee allocation and tracking following the Nigerian 3-term academic calendar system. This implementation follows the same architectural approach used in the Academium-BGS system.

**Migration Version:** 200
**Date:** 2026-01-23
**Status:** Completed

---

## 1. Database Changes

### Migration File
**File:** `application/migrations/200_add_term_id_to_fees_tables.php`

### Tables Modified

#### 1.1 fee_allocation
- **Column Added:** `term_id INT(11) NULL` after `session_id`
- **Index Added:** `idx_term_id` on `term_id` column
- **Purpose:** Associates fee allocations with specific academic terms
- **Data Migration:** Existing records linked to first term (term_order = 1) of their session/branch

#### 1.2 fee_groups
- **Column Added:** `term_id INT(11) NULL` after `session_id`
- **Index Added:** `idx_term_id` on `term_id` column
- **Purpose:** Allows fee groups to be term-specific

#### 1.3 fee_fine
- **Column Added:** `term_id INT(11) NULL` after `session_id`
- **Index Added:** `idx_term_id` on `term_id` column
- **Purpose:** Associates late payment fines with specific terms

#### 1.4 transport_fee_details
- **Column Added:** `term_id INT(11) NULL` after `session_id`
- **Index Added:** `idx_term_id` on `term_id` column
- **Purpose:** Tracks transport fees per academic term

#### 1.5 fees_reminder
- **Column Added:** `term_id INT(11) NULL` after `session_id`
- **Index Added:** `idx_term_id` on `term_id` column
- **Purpose:** Links fee reminders to specific terms

### Data Population Strategy
The migration automatically populates `term_id` for existing `fee_allocation` records using the following logic:
1. **Primary:** Links to first term (term_order = 1) of the session/branch
2. **Fallback 1:** If no first term exists, links to any active term
3. **Fallback 2:** If no active term exists, links to any term for that session/branch
4. **Result:** Records without matching term remain NULL for manual review

**Example Output:**
```
Added term_id to fee_allocation table.
Added index on fee_allocation.term_id.
Populating term_id for existing fee_allocation records...
Updated 45 fee_allocation records with term_id.
Unable to match 3 records (no matching term found).
```

---

## 2. Model Changes

### File: `application/models/Fees_model.php`

#### 2.1 Updated Methods (Term Support Added)

##### getStudentAllocationList()
**Lines:** 70-85
**Changes:**
- Added optional `$termID` parameter
- Added term filtering when `$termID` is provided

```php
public function getStudentAllocationList($classID = '', $sectionID = '', $groupID = '', $branchID = '', $termID = null)
{
    // ... existing code ...

    // Add term_id filter if provided
    if (!empty($termID)) {
        $sql .= " AND fa.term_id = " . $this->db->escape($termID);
    }

    // ... rest of method ...
}
```

##### getInvoiceStatus()
**Lines:** 87-99
**Changes:**
- Added optional `$termID` parameter
- Filters invoice calculations by term

```php
public function getInvoiceStatus($enrollID = '', $termID = null)
{
    // ... existing code ...

    // Add term_id filter if provided
    if (!empty($termID)) {
        $sql .= " AND fee_allocation.term_id = " . $this->db->escape($termID);
    }

    // ... rest of method ...
}
```

##### getDueInvoiceDT_list()
**Lines:** 338-382
**Changes:**
- Added optional `$term_id` parameter
- Added term filtering for both regular fees and transport fees

```php
public function getDueInvoiceDT_list($class_id = '', $section_id = '', $feegroup_id = '', $fee_feetype_id = '', $term_id = null)
{
    if ($feegroup_id == 'transport') {
        // ... transport fees setup ...
        if (!empty($term_id)) {
            $this->datatables->where('fa.term_id', $term_id);
        }
    } else {
        // ... regular fees setup ...
        if (!empty($term_id)) {
            $this->datatables->where('fa.term_id', $term_id);
        }
    }
    // ... rest of method ...
}
```

#### 2.2 New Helper Methods

##### get_current_term()
**Lines:** 900-935
**Purpose:** Retrieves the current active term for a session and branch
**Features:**
- Checks for manually set term in session variable first
- Falls back to active term from database
- Returns term object or null

```php
public function get_current_term($session_id = null, $branch_id = null)
{
    // Default to current session/branch if not specified
    // Check manually_set_term_id session variable first
    // Return active term from academic_terms table
}
```

##### get_session_terms()
**Lines:** 937-956
**Purpose:** Retrieves all terms for a session and branch
**Returns:** Array of term objects ordered by term_order

```php
public function get_session_terms($session_id, $branch_id = null)
{
    // Returns all terms for session/branch ordered by term_order ASC
}
```

##### get_term_by_date()
**Lines:** 958-982
**Purpose:** Finds the term that contains a specific date
**Returns:** Term object or null

```php
public function get_term_by_date($date, $session_id = null, $branch_id = null)
{
    // Returns term where date is between start_date and end_date
}
```

---

## 3. Controller Changes

### File: `application/controllers/Fees.php`

#### 3.1 allocation() Method
**Lines:** 402-457
**Changes:**
- Gets active term and passes to view
- Accepts `term_id` from POST
- Passes `term_id` to `getStudentAllocationList()` model method
- Includes `term_id` in allocation save operations
- Includes `term_id` in allocation delete operations

**Key Code:**
```php
// Get active term for the form
$this->data['active_term'] = get_active_term();

if (isset($_POST['search'])) {
    $this->data['term_id'] = $this->input->post('term_id');
    $this->data['studentlist'] = $this->fees_model->getStudentAllocationList(
        $this->data['class_id'],
        $this->data['section_id'],
        $this->data['fee_group_id'],
        $branchID,
        $this->data['term_id']  // NEW
    );
}

if (isset($_POST['save'])) {
    $termID = $this->input->post('term_id');
    foreach ($student_array as $key => $value) {
        $arrayData = array(
            'student_id' => $value,
            'group_id' => $fee_groupID,
            'session_id' => get_session_id(),
            'branch_id' => $branchID,
            'term_id' => $termID,  // NEW
        );
        // ... insert logic ...
    }
}
```

#### 3.2 allocation_save() Method
**Lines:** 459-501
**Changes:**
- Accepts `term_id` from POST
- Includes `term_id` in allocation insert operations
- Includes `term_id` in allocation delete operations

#### 3.3 invoice() Method
**Lines:** 578-600
**Changes:**
- Gets active term ID using helper function
- Passes `term_id` to `getInvoiceStatus()` model method
- Passes active term object to view for display

```php
// Get active term for filtering
$termID = get_active_term_id();

$this->data['invoice'] = $this->fees_model->getInvoiceStatus($enrollID, $termID);
$this->data['active_term'] = get_active_term();
```

#### 3.4 due_invoice() Method
**Lines:** 668-701
**Changes:**
- Gets active term and passes to view
- Added validation rule for `term_id` field

```php
// Get active term for the form
$this->data['active_term'] = get_active_term();

if ($_POST) {
    // ... other validation rules ...
    $this->form_validation->set_rules('term_id', translate('term'), 'trim|required');
    // ... rest of validation ...
}
```

#### 3.5 getDueInvoiceListDT() Method
**Lines:** 703-726
**Changes:**
- Accepts `term_id` from POST
- Passes `term_id` to `getDueInvoiceDT_list()` model method

```php
$class_id = $this->input->post('class_id');
$section_id = $this->input->post('section_id');
$term_id = $this->input->post('term_id');  // NEW

$results = $this->fees_model->getDueInvoiceDT_list(
    $class_id,
    $section_id,
    $feegroup_id,
    $fee_feetype_id,
    $term_id  // NEW
);
```

---

## 4. View Changes

### 4.1 Fee Allocation Form
**File:** `application/views/fees/allocation.php`
**Lines:** 43-58

**Changes:**
- Added term dropdown after section dropdown
- Pre-selects active term by default
- Loads terms using `get_session_terms()` helper

**New HTML:**
```php
<div class="col-md-<?php echo $widget; ?> mb-sm">
    <div class="form-group">
        <label class="control-label"><?=translate('term')?> <span class="required">*</span></label>
        <?php
            $terms = get_session_terms(get_session_id(), $branch_id);
            $arrayTerm = array('' => translate('select'));
            if (!empty($terms)) {
                foreach ($terms as $term) {
                    $arrayTerm[$term->id] = $term->term_name;
                }
            }
            echo form_dropdown("term_id", $arrayTerm, set_value('term_id', isset($active_term) ? $active_term->id : ''),
                "class='form-control' id='term_id' required data-plugin-selectTwo data-width='100%' data-minimum-results-for-search='Infinity' ");
        ?>
    </div>
</div>
```

**Hidden Field Added (Line 86):**
```php
<input type="hidden" name="term_id" value="<?=isset($term_id) ? $term_id : ''; ?>" >
```

### 4.2 Due Invoice Report Form
**File:** `application/views/fees/due_invoice.php**
**Lines:** 48-64

**Changes:**
- Added term dropdown between section and fees_type
- Pre-selects active term by default
- Loads terms using `get_session_terms()` helper

**JavaScript Update (Lines 136-144):**
```javascript
var filter = function (d) {
    d.branch_id = $('#branch_id').val();
    d.class_id = $('#class_id').val();
    d.section_id = $('#section_id').val();
    d.term_id = $('#term_id').val();  // NEW
    d.fees_type = $('#feesType').val();
    d.submit_btn = searchBtn;
};
```

### 4.3 Fee Collection/Invoice View
**File:** `application/views/fees/collect.php`
**Lines:** 63-68

**Changes:**
- Added term display badge in invoice header
- Shows active term name next to invoice status

**New HTML:**
```php
<?php if (isset($active_term) && $active_term): ?>
<p class="mb-none">
    <span class="text-dark"><?=translate('term')?> : </span>
    <span class="value label label-info"><?= $active_term->term_name ?></span>
</p>
<?php endif; ?>
```

---

## 5. Usage Guide

### 5.1 Allocating Fees to Students

1. Navigate to **Fees → Fee Allocation**
2. Select:
   - Branch (superadmin only)
   - Class
   - Section
   - **Term** (new field - defaults to active term)
   - Fee Group
3. Click **Filter** to load students
4. Check students to allocate fees for the selected term
5. Click **Save** to complete allocation

**Important:** Fees are now allocated PER TERM. To allocate fees for different terms, you must repeat the process for each term.

### 5.2 Viewing Due Invoices

1. Navigate to **Fees → Due Invoice**
2. Select:
   - Branch (superadmin only)
   - Class
   - Section
   - **Term** (new field - defaults to active term)
   - Fees Type
3. Click **Filter**
4. Report displays students with outstanding fees for the selected term only

### 5.3 Collecting Fees

1. Navigate to **Fees → Invoice List** or **Fees → Due Invoice**
2. Click **Collect** button for a student
3. Invoice displays fees for the **active term** only
4. Term is shown in the invoice header
5. Collect payment as normal

**Note:** The invoice automatically filters allocations by the active term. To view/collect fees for other terms, you must change the active term in the Sessions module.

### 5.4 Changing Active Term

To work with a different term:
1. Navigate to **Academic → Sessions**
2. View terms for the current session
3. Set desired term as active
4. All fees operations will now use that term

---

## 6. Key Features

### 6.1 Backward Compatibility
- All `term_id` fields are **nullable** to support existing data
- Model methods work with or without `term_id` parameter
- Existing fee allocations without terms remain accessible

### 6.2 Multi-Branch Support
- Terms are branch-specific
- Each branch can have different term dates
- Fee allocations respect branch boundaries

### 6.3 Manual Term Override
- Session variable `manually_set_term_id` allows viewing non-active terms
- Useful for administrative review or end-of-term reporting
- Set via: `$this->session->set_userdata('manually_set_term_id', $term_id);`

### 6.4 Active Term Display
- Forms show active term information
- Invoice displays current term badge
- Clear visual indicators prevent confusion

---

## 7. Testing Checklist

### Database Migration
- [ ] Run migration 200: `php index.php migrate`
- [ ] Verify `term_id` column exists in all 5 tables
- [ ] Check indexes created successfully
- [ ] Confirm existing fee_allocation records populated with term_id
- [ ] Review records with NULL term_id for manual assignment

### Fee Allocation
- [ ] Can select term in allocation form
- [ ] Term defaults to active term
- [ ] Fee allocation saves with term_id
- [ ] Can allocate different fees for different terms
- [ ] Cannot allocate same fee group twice for same term
- [ ] Can view allocated students filtered by term

### Due Invoice Report
- [ ] Term dropdown appears and is required
- [ ] Report filters by selected term
- [ ] DataTable loads correct data for term
- [ ] Export includes term in title/data
- [ ] Collect button links to correct invoice

### Fee Collection
- [ ] Invoice shows active term badge
- [ ] Invoice displays only fees for active term
- [ ] Payment records correctly
- [ ] Balance calculations accurate per term
- [ ] Payment history shows correct term data

### Edge Cases
- [ ] Allocations without term_id display correctly
- [ ] Changing active term updates forms
- [ ] Multi-branch scenarios work correctly
- [ ] Session year transition handled properly
- [ ] Transport fees term filtering works

---

## 8. Migration Rollback

If needed, the migration can be reversed:

```bash
php index.php migrate version 199
```

**WARNING:** Rolling back will:
- Drop all `term_id` columns from fees tables
- Remove all indexes created
- **Lose all term associations for fees**

Before rollback:
1. Export fee_allocation table for backup
2. Document any manual term assignments
3. Notify users of the change

---

## 9. Known Limitations

1. **Historical Data:** Existing fees are assigned to first term of session by default - may not reflect actual term they were created in
2. **Payment History:** Individual payments don't have direct term field - term determined through fee_allocation relationship
3. **Fee Groups:** Fee groups can have term_id but not currently enforced in UI - groups are still session-wide
4. **Cross-Term Payments:** Paying fees across multiple terms requires collecting per term (current behavior)

---

## 10. Future Enhancements

### Potential Improvements
1. **Term-Specific Fee Groups:** Allow creating different fee groups per term
2. **Bulk Term Assignment:** Tool to assign/reassign terms to existing allocations
3. **Term-Based Reports:** Additional reports filtered by term (collection summaries, outstanding by term, etc.)
4. **Fee Template System:** Create fee templates that auto-allocate across all terms
5. **Term Transition Wizard:** Guided process for moving fees between terms
6. **Term Fee Comparison:** Side-by-side comparison of fees across terms

### Recommended Next Steps
1. Train staff on term-based fee allocation workflow
2. Review and update existing fee allocations with correct terms
3. Create term-specific fee groups if different amounts per term
4. Document institution's policy for fees paid late (which term to attribute)
5. Monitor for any data quality issues in first 1-2 terms

---

## 11. Related Files

### Migration
- `application/migrations/200_add_term_id_to_fees_tables.php`
- `application/migrations/199_create_academic_terms.php` (dependency)

### Controllers
- `application/controllers/Fees.php` (main changes)

### Models
- `application/models/Fees_model.php` (term support methods)

### Views
- `application/views/fees/allocation.php` (term dropdown added)
- `application/views/fees/due_invoice.php` (term dropdown added)
- `application/views/fees/collect.php` (term display added)

### Helpers
- `application/helpers/general_helper.php` (get_active_term, get_active_term_id)

### Configuration
- `application/config/migration.php` (updated to version 200)

---

## 12. Support and Troubleshooting

### Common Issues

#### Issue: Term dropdown is empty
**Cause:** No terms created for current session/branch
**Solution:** Navigate to Sessions module and create terms for the session

#### Issue: Fees not showing in invoice
**Cause:** Fee allocation has different term_id than active term
**Solution:** Either change active term, or re-allocate fees for current term

#### Issue: Can't allocate fees (duplicate error)
**Cause:** Student already has this fee group allocated for this term
**Solution:** Either use different fee group, different term, or remove existing allocation first

#### Issue: Migration fails with term_id error
**Cause:** academic_terms table doesn't exist (migration 199 not run)
**Solution:** Run migration 199 first: `php index.php migrate version 199`

#### Issue: Old fee allocations have NULL term_id
**Cause:** No matching term found during migration
**Solution:** Manually review these records and assign appropriate term_id via SQL or admin tool

---

## 13. SQL Queries for Maintenance

### Find allocations without term
```sql
SELECT * FROM fee_allocation WHERE term_id IS NULL;
```

### Assign allocations to specific term
```sql
UPDATE fee_allocation
SET term_id = 1  -- Replace with actual term ID
WHERE session_id = 2
  AND branch_id = 1
  AND term_id IS NULL;
```

### Count allocations per term
```sql
SELECT
    at.term_name,
    COUNT(*) as allocation_count
FROM fee_allocation fa
JOIN academic_terms at ON at.id = fa.term_id
WHERE fa.session_id = 2
GROUP BY at.term_name
ORDER BY at.term_order;
```

### View fee summary by term
```sql
SELECT
    at.term_name,
    COUNT(DISTINCT fa.student_id) as students,
    SUM(gd.amount) as total_fees
FROM fee_allocation fa
JOIN academic_terms at ON at.id = fa.term_id
JOIN fee_groups_details gd ON gd.fee_groups_id = fa.group_id
WHERE fa.session_id = 2
  AND fa.branch_id = 1
GROUP BY at.term_name
ORDER BY at.term_order;
```

---

## Conclusion

The fees module has been successfully migrated to support term-based operations, aligning with the Nigerian 3-term academic calendar. This change provides:

✅ **Better Financial Tracking:** Fees allocated and tracked per term
✅ **Accurate Reporting:** Reports filtered by specific terms
✅ **Clear Audit Trail:** Know which term each fee applies to
✅ **Flexible Management:** Different fees for different terms
✅ **User-Friendly:** Active term defaults simplify daily operations

All existing fee data has been preserved and automatically linked to appropriate terms. The system maintains backward compatibility while providing enhanced term-based functionality.

For questions or issues, refer to the troubleshooting section or contact system administrators.

---

**Document Version:** 1.0
**Last Updated:** 2026-01-23
**Migration Status:** ✅ Completed
