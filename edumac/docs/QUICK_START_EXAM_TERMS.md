# Quick Start: Exam Terms Migration

## 🚀 Getting Started in 3 Steps

### Step 1: Run the Migration (2 minutes)

1. Navigate to: **yoursite.com/migration_runner**
2. Click **"Run Migrations"** button
3. Wait for completion message
4. You should see: "Migration 197: migrate_exam_to_academic_terms - SUCCESS"

### Step 2: Verify Terms (1 minute)

1. Navigate to: **Sessions** (from sidebar menu)
2. Click **"View Terms"** on any session
3. You should see 3 terms:
   - First Term (Sep - Dec)
   - Second Term (Jan - Apr)
   - Third Term (May - Aug)

### Step 3: Fix Missing Terms (if needed)

If any session has less than 3 terms:

1. Navigate to: **yoursite.com/fix_terms**
2. Click **"Fix All Sessions"** button
3. Done! All sessions now have 3 terms

---

## ✅ What Just Happened?

- ✅ Old `exam_term` table removed
- ✅ All exams now use centralized `academic_terms`
- ✅ Terms managed via Sessions module (not Exam module)
- ✅ All existing data preserved and migrated
- ✅ No data loss, fully backward compatible

---

## 📝 How to Create an Exam with Terms

### Before (Old Way - DEPRECATED)
```
1. Go to Exam > Exam Term
2. Create term
3. Go to Exam > Create Exam
4. Select term
```

### Now (New Way - RECOMMENDED)
```
1. Terms already exist (auto-created per session)
2. Go to Exam > Create Exam
3. Select term from dropdown
4. Done!
```

---

## 🎯 Common Tasks

### Task 1: Create a New Exam

1. Navigate to: **Exam > Exam List**
2. Click **"Create Exam"** tab
3. Fill in:
   - Name: "Mid-Term Exam"
   - **Term**: Select from dropdown (First/Second/Third)
   - Type: Marks/Grade/Both
   - Mark Distribution: Select options
4. Click **Save**

**Result**: Exam created with selected term

---

### Task 2: Edit Term Dates

1. Navigate to: **Sessions**
2. Click **"View Terms"** on desired session
3. Click the **pencil icon** next to start/end date
4. Change date
5. Press **Enter** or click **Save icon**

**Result**: Term dates updated, total weeks auto-calculated

---

### Task 3: Activate a Term

1. Navigate to: **Sessions**
2. Click **"View Terms"** on current session
3. Click **"Activate"** button next to desired term

**Result**: Selected term becomes active (only one active per session/branch)

---

### Task 4: View Exam with Term

1. Navigate to: **Exam > Exam List**
2. Look at **"Term"** column
3. You'll see: "First Term", "Second Term", or "Third Term"

**Result**: Term displays correctly for each exam

---

## ⚠️ What Changed?

| Feature | Before | After |
|---------|--------|-------|
| **Term Management** | Exam > Exam Term | Sessions > View Terms |
| **Term Table** | `exam_term` | `academic_terms` |
| **Term Fields** | name, branch_id, session_id | + term_order, dates, weeks, active |
| **Term Creation** | Manual | Auto-created (3 per session) |
| **Term Editing** | Form-based | Inline editing |

---

## 🔍 Troubleshooting

### Problem: Term dropdown is empty when creating exam

**Solution**:
1. Go to **yoursite.com/fix_terms**
2. Click **"Fix All Sessions"**
3. Return to Exam creation - dropdown now populated

---

### Problem: Old "Exam Term" menu still visible

**Solution**:
1. Clear browser cache (Ctrl+Shift+Delete)
2. Refresh page (F5)
3. Menu should be hidden now

---

### Problem: Exam shows "N/A" for term

**Solution**:
1. Edit the exam
2. Select a term from dropdown
3. Save
4. Term now displays correctly

---

## 📚 Need More Help?

- **Full Documentation**: See [ACADEMIC_TERMS_MIGRATION_GUIDE.md](edumac/docs/ACADEMIC_TERMS_MIGRATION_GUIDE.md)
- **Detailed Summary**: See [EXAM_MIGRATION_SUMMARY.md](EXAM_MIGRATION_SUMMARY.md)
- **Fix Terms Tool**: yoursite.com/fix_terms
- **Migration Runner**: yoursite.com/migration_runner

---

## 💡 Pro Tips

1. **Always use Sessions module** to manage terms (not Exam module)
2. **Fix Terms tool** is your friend - run it after creating new sessions
3. **Inline editing** is faster than form-based editing
4. **Active term** determines current term for the session/branch
5. **term_id is optional** - exams can exist without a specific term

---

**Last Updated**: January 23, 2026
**Migration Version**: 197
**Status**: ✅ Ready to Use
