# Fees Module - Term Dropdown Loading Fix

## Date: 2026-01-23

## Issue
Term dropdowns were showing on fees pages but not loading data properly, especially for super admin users who need to select a branch first.

---

## Root Cause

The issue was that when super admins selected a branch, the system only refreshed the **class** and **section** dropdowns via AJAX, but **not the terms dropdown**.

Since different branches can have different academic terms, the terms dropdown needs to be refreshed based on the selected branch. Without this refresh, the terms dropdown either:
- Showed no terms
- Showed terms from a different branch
- Caused validation errors when filtering

---

## Solution

### 1. Backend AJAX Method (Already Existed)
The `getAcademicTermsByBranch()` method in [Ajax.php](application/controllers/Ajax.php:120-142) already existed and works correctly:

```php
public function getAcademicTermsByBranch()
{
    $html = "";
    $branch_id = $this->application_model->get_branch_id();

    if (!empty($branch_id)) {
        // Get terms for current session and branch
        $terms = get_session_terms(get_session_id(), $branch_id);

        if (!empty($terms)) {
            $html .= "<option value=''>" . translate('select') . "</option>";
            foreach ($terms as $term) {
                $html .= '<option value="' . $term->id . '">' . $term->term_name . '</option>';
            }
        } else {
            $html .= '<option value="">' . translate('no_information_available') . '</option>';
        }
    } else {
        $html .= '<option value="">' . translate('select_branch_first') . '</option>';
    }

    echo $html;
}
```

### 2. Page-Specific JavaScript Updates

Each fees page has its own branch change handler that loads related dropdowns. We added term loading to these existing handlers following the same pattern used for class and fee group dropdowns.

#### [invoice_list.php](application/views/fees/invoice_list.php)

**Branch Dropdown (Line 17):**
```php
echo form_dropdown("branch_id", $arrayBranch, set_value('branch_id'),
    "class='form-control' id='branch_id' onchange='getBranchData(this.value)'
    data-plugin-selectTwo data-width='100%'");
```

**JavaScript Function (Lines 262-286):**
```javascript
// Load terms and classes when branch changes
function getBranchData(branch_id) {
    // Load classes
    getClassByBranch(branch_id);

    // Load terms for the selected branch
    $.ajax({
        url: base_url + 'ajax/getAcademicTermsByBranch',
        type: 'POST',
        data: { branch_id: branch_id },
        beforeSend: function () {
            if ($('#select2-term_id-container').length) {
                $('#select2-term_id-container').parent().addClass('select2loading');
            }
        },
        success: function (data) {
            $('#term_id').html(data);
        },
        complete: function () {
            if ($('#select2-term_id-container').length) {
                $('#select2-term_id-container').parent().removeClass('select2loading');
            }
        }
    });
}
```

#### [due_invoice.php](application/views/fees/due_invoice.php)

**Existing Branch Change Handler (Lines 209-214):**
```javascript
$('#branch_id').on('change', function() {
    var branchID = $(this).val();
    getClassByBranch(branchID);
    getTypeByBranch(branchID);
    getTermsByBranch(branchID); // ADDED
});
```

**New Terms Function (Lines 216-236):**
```javascript
// Load terms for the selected branch
function getTermsByBranch(branch_id) {
    $.ajax({
        url: base_url + 'ajax/getAcademicTermsByBranch',
        type: 'POST',
        data: { branch_id: branch_id },
        beforeSend: function () {
            if ($('#select2-term_id-container').length) {
                $('#select2-term_id-container').parent().addClass('select2loading');
            }
        },
        success: function (data) {
            $('#term_id').html(data);
        },
        complete: function () {
            if ($('#select2-term_id-container').length) {
                $('#select2-term_id-container').parent().removeClass('select2loading');
            }
        }
    });
}
```

#### [allocation.php](application/views/fees/allocation.php)

**Existing Branch Change Handler (Lines 156-169):**
```javascript
$('#branch_id').on('change', function(){
    var branchID = $(this).val();
    getClassByBranch(branchID);
    $.ajax({
        url: base_url + 'fees/getGroupByBranch',
        type: 'POST',
        data: {
            'branch_id' : branchID,
        },
        success: function (data) {
            $('#groupID').html(data);
        }
    });
    // Load terms for the selected branch - ADDED (Lines 169-189)
    $.ajax({
        url: base_url + 'ajax/getAcademicTermsByBranch',
        type: 'POST',
        data: {
            'branch_id' : branchID,
        },
        beforeSend: function () {
            if ($('#select2-term_id-container').length) {
                $('#select2-term_id-container').parent().addClass('select2loading');
            }
        },
        success: function (data) {
            $('#term_id').html(data);
        },
        complete: function () {
            if ($('#select2-term_id-container').length) {
                $('#select2-term_id-container').parent().removeClass('select2loading');
            }
        }
    });
});
```

---

## How It Works Now

### For Super Admin Users:
1. **Select Branch** → Triggers AJAX to refresh:
   - Class dropdown (existing behavior)
   - Section dropdown reset (existing behavior)
   - **Terms dropdown (NEW)**
   - Fee groups dropdown (on allocation page)
   - Fee types dropdown (on due invoice page)
2. **Select Term** → Choose from terms available in selected branch
3. **Select Class/Section** → Optional filters
4. **Click Filter Button** → Loads data filtered by branch, term, class, section

### For Non-Admin Users:
- Branch is pre-selected (single branch access)
- Terms dropdown already shows correct terms for their branch
- No change to their workflow

---

## Files Modified

### Views (JavaScript sections updated)
- **application/views/fees/invoice_list.php**
  - Line 17: Changed onchange to `getBranchData(this.value)`
  - Lines 262-286: Added `getBranchData()` function

- **application/views/fees/due_invoice.php**
  - Line 213: Added `getTermsByBranch(branchID)` call
  - Lines 216-236: Added `getTermsByBranch()` function

- **application/views/fees/allocation.php**
  - Lines 169-189: Added terms loading AJAX call

### No Changes Needed
- **application/controllers/Ajax.php** - Method already existed
- **assets/js/app.fn.js** - No changes to centralized functions
- Controller methods already handle term_id parameter (from previous fixes)
- Model methods already filter by term_id (from previous fixes)
- Views already have term dropdowns (from previous fixes)

---

## Testing Checklist

### As Super Admin:
- [ ] Navigate to **Fees → Invoice List**
- [ ] Select a branch from dropdown
- [ ] Verify terms dropdown refreshes and shows terms for that branch
- [ ] Select a term
- [ ] Select class/section (optional)
- [ ] Click Filter
- [ ] Verify invoice data loads for selected criteria
- [ ] Change branch and verify terms update again

### As Branch Admin/Teacher:
- [ ] Navigate to **Fees → Invoice List**
- [ ] Verify terms dropdown shows terms for your branch
- [ ] Select a term and filters
- [ ] Click Filter
- [ ] Verify data loads correctly

### Test All Fees Pages:
- [ ] **Fees → Fee Allocation** - Branch change → Terms refresh → Data loads
- [ ] **Fees → Invoice List** - Branch change → Terms refresh → Data loads
- [ ] **Fees → Due Invoice** - Branch change → Terms refresh → Data loads
- [ ] **Fees → Fee Collection** - Term switcher works on invoice page

---

## Why This Approach

This solution follows the **existing pattern** used throughout the codebase:
- Each page has its own branch change handler
- Each handler loads the specific dropdowns needed for that page
- This is consistent with how class, section, fee group, and fee type dropdowns are already handled
- Follows the principle of "don't reinvent the wheel" - we used the same approach as existing code

We initially considered adding a global function to `app.fn.js`, but that wouldn't work because:
- Each page needs different combinations of dropdowns refreshed
- Some pages need fee groups, others need fee types
- Page-specific logic should stay in page-specific JavaScript
- Academium-BGS uses a centralized branch selector (different architecture)

---

## Why This Issue Occurred

The original Educmac system didn't have academic terms, so when terms were added:
1. Backend term support was added (database, models, controllers)
2. Frontend term dropdowns were added to views
3. BUT the JavaScript that handles branch changes wasn't updated to refresh terms

Meanwhile, Academium-BGS handles this differently - they have a **centralized branch selector** (likely in header/sidebar) that sets the active branch globally. This means:
- All pages automatically know the current branch
- Terms are loaded when the page loads, not on branch change
- No per-page branch dropdowns for super admins

Educmac still uses per-page branch selection, so we needed to add the term refresh logic to each page's existing branch change handler.

---

## Future Enhancement

Consider implementing a centralized branch selector like Academium-BGS to:
- Eliminate per-page branch dropdowns
- Set active branch in session/cookie
- Simplify all forms that need branch-dependent data
- Improve UX by remembering branch selection across pages

---

## Related Documentation

- [FEES_TERM_MIGRATION_SUMMARY.md](FEES_TERM_MIGRATION_SUMMARY.md) - Complete fees migration guide
- [FEES_TERM_FIX_SUMMARY.md](FEES_TERM_FIX_SUMMARY.md) - Invoice list term integration fix
- [EXAM_TERM_DROPDOWN_FIX.md](EXAM_TERM_DROPDOWN_FIX.md) - Similar fix for exam module
- [ATTENDANCE_TERM_MIGRATION_SUMMARY.md](ATTENDANCE_TERM_MIGRATION_SUMMARY.md) - Attendance term migration

---

**Status:** ✅ Fixed
**Version:** 1.1
**Last Updated:** 2026-01-23
