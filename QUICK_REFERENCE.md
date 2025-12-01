# STL File Regeneration - Quick Reference

## What Changed?

You now have a system that **stores employee data in the database** when generating STL files. This means:

✅ **If you delete a local Excel file, you can regenerate it from the database**
✅ **The regenerated file will have the exact same data as the original**
✅ **No manual intervention needed - it happens automatically**

## How to Use

### Generating a New File (Same as Before)
1. Go to STL module
2. Select a month from the dropdown
3. Click "Generate Excel"
4. System creates and saves the file locally

**What's New:** The employee data is now also saved in the database

### Recovering a Deleted File (NEW!)
1. Delete the Excel file from `generated excel files/` folder (intentionally or by accident)
2. Go back to STL module
3. Click "Download" for that month
4. **System automatically regenerates the file from database data**
5. File is downloaded to you

## Database Table Structure

### `stl_file_records` (NEW Table)

This table stores:
- **filename**: Name of the Excel file (e.g., "january_2025_stl.xlsx")
- **month & year**: Identifies which period this is for
- **num_borrowers**: How many employees in this file
- **total_ee_deducted**: Total employee deductions
- **total_er_deducted**: Total employer deductions
- **employee_data**: **JSON array of all employee records** (this is the key!)
- **file_path**: Where the Excel file is stored
- **file_size**: Size in bytes
- **created_by**: User ID who created it
- **created_date & updated_date**: Timestamps

## Example Scenario

```
Monday, November 28, 2025:
├─ Admin generates January 2025 STL file
├─ File saved: generated excel files/january_2025_stl.xlsx
└─ Employee data saved in database

Tuesday, November 29, 2025:
├─ Someone accidentally deletes the Excel file
├─ File: generated excel files/january_2025_stl.xlsx ✗ DELETED
└─ Database still has the data ✓

Wednesday, November 30, 2025:
├─ Admin clicks "Download" for January 2025
├─ System checks for file - NOT FOUND
├─ System checks database - FOUND
├─ System regenerates the file with exact same data
├─ New file created: generated excel files/january_2025_stl.xlsx
└─ Admin downloads it - IDENTICAL to original!
```

## Benefits

| Aspect | Before | After |
|--------|--------|-------|
| **File Deleted** | Lost forever ✗ | Recoverable ✓ |
| **Data Backup** | Files only | Files + Database |
| **Recovery Time** | Contact admin | Instant (click download) |
| **Data Accuracy** | n/a | Perfect (saved snapshot) |
| **Audit Trail** | None | Yes (timestamps, user info) |
| **Disk Space** | Always needed | Can delete files safely |

## Files Modified

1. **`stl/includes/regenerate_stl_excel.php`** (UPDATED)
   - Now checks database first for saved data
   - Falls back to active employees if no saved data
   - Saves employee data as JSON to database

2. **`stl/stl.php`** (UPDATED)
   - Download function now regenerates missing files
   - Automatic - user doesn't need to do anything

3. **`database/create_stl_file_records.sql`** (NEW)
   - Migration file to create the new table
   - Run once to set up

## Database Queries

### Check if data exists for a month
```sql
SELECT * FROM stl_file_records 
WHERE month = 'January' AND year = 2025;
```

### View saved employee data for a file
```sql
SELECT employee_data FROM stl_file_records 
WHERE month = 'January' AND year = 2025;
```

### See all file records
```sql
SELECT id, filename, month, year, num_borrowers, 
       total_ee_deducted, total_er_deducted, created_date
FROM stl_file_records
ORDER BY year DESC, FIELD(month, 'January', 'February', 'March', ...)
DESC;
```

## Technical Details

### Employee Data JSON Format
```json
[
  {
    "id": 56,
    "pagibig_no": "458879899666",
    "id_number": "0998",
    "ee": 0.00,
    "er": 200.00,
    "tin": "732-888-832-000",
    "birthdate": "0000-00-00"
  },
  {
    "id": 57,
    "pagibig_no": "445888665632",
    "id_number": "0332",
    "ee": 0.00,
    "er": 200.00,
    "tin": "482-637-462-000",
    "birthdate": "0000-00-00"
  }
]
```

### Regeneration Logic
```
IF file doesn't exist locally THEN
    IF saved data in database THEN
        Use saved employee data
    ELSE
        Fetch from current active employees
    END
    Generate Excel file
    Save employee data to database
    Return new file to user
END
```

## Common Questions

**Q: Will regenerated files match the originals?**
A: Yes! If the local file was deleted but database record exists, the regenerated file will have identical data.

**Q: What if I change employee data after generating a file?**
A: The saved file record uses a snapshot from generation time, so it won't change. This preserves historical accuracy.

**Q: Can I delete local Excel files to save space?**
A: Yes! The data is backed up in the database. You can regenerate anytime by clicking Download.

**Q: What if both the file and database record are deleted?**
A: This shouldn't happen in normal use. The database acts as a backup. Only delete files intentionally.

**Q: How much space does the JSON data take?**
A: Very little. A typical file with 5-10 employees is just a few KB of JSON.

## Troubleshooting

**Problem: Getting "No active STL records found" when regenerating**
- Cause: No record in database AND no active employees in selected_stl
- Solution: Ensure you have active employees registered in the STL system

**Problem: JSON parse error in regenerated file**
- Cause: Corrupted employee_data in database
- Solution: Check the database record, may need to regenerate fresh

**Problem: File regenerates but with different employee count**
- Cause: Employees were deactivated after original generation
- Solution: If database has saved data, it uses the original. If not, uses current active.

## Next Steps

1. ✓ Database table created
2. ✓ Code updated
3. Test by:
   - Generate a new file
   - Delete the local file
   - Click Download
   - Verify it regenerates correctly
4. Monitor system performance
5. Consider regular database backups
