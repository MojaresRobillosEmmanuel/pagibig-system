# STL File Regeneration System

## Overview
The STL (Short-Term Loan) file generation system now stores employee data snapshots in the database. This allows files to be regenerated from the database even if the local Excel file is deleted.

## How It Works

### File Generation Flow
1. **Generate New File**: When generating a new STL file for a month/year:
   - Employee data is collected from the `selected_stl` table (active employees)
   - An Excel file is created and saved to `generated excel files/` folder
   - Employee data snapshot is stored in `stl_file_records` table
   - Summary information is stored in `stl_summary` table

2. **Download Existing File**: When downloading an STL summary file:
   - System first checks if the local Excel file exists
   - If it exists, the file is downloaded directly
   - If not, the system regenerates it from the stored employee data

### Database Tables

#### `stl_file_records`
Stores all STL file generation records with employee snapshots:
- `id`: Primary key
- `filename`: The generated filename (e.g., "january_2025_stl.xlsx")
- `month`: Month name (e.g., "January")
- `year`: Year number (e.g., 2025)
- `num_borrowers`: Count of employees in the file
- `total_ee_deducted`: Total EE (Employee) deduction amount
- `total_er_deducted`: Total ER (Employer) deduction amount
- `employee_data`: JSON array of employee records at time of generation
- `file_path`: Local path where the Excel file is stored
- `file_size`: Size of the Excel file in bytes
- `created_by`: User ID who created the file
- `created_date`: Timestamp when the record was created
- `updated_date`: Timestamp of last update

#### `stl_summary`
Stores summary information (already existed):
- References the month/year
- Used for displaying the summary table in the UI

### Benefits

1. **Data Integrity**: Employee data is preserved at the time of generation
2. **File Recovery**: Deleted files can be regenerated with the exact same data
3. **Audit Trail**: Can track which files were generated and by whom
4. **Consistency**: Regenerated files will always match the original data

### API Endpoints

#### `regenerate_stl_excel.php` (POST)
Generates/regenerates an STL Excel file

**Parameters:**
- `month`: Month name (required) - e.g., "January"
- `year`: Year as integer (required) - e.g., 2025

**Response:**
```json
{
  "status": "success",
  "message": "Excel file generated successfully",
  "filename": "january_2025_stl.xlsx",
  "filepath": "path/to/file",
  "record_count": 9,
  "total_ee": 1500.00
}
```

**Logic:**
1. Check if `stl_file_records` exists for the month/year
2. If exists, use stored employee data
3. If not exists, fetch from `selected_stl` table
4. Generate Excel file
5. Save employee data and file info to `stl_file_records`

### Example Usage

**Generate a new file:**
```javascript
fetch('./includes/regenerate_stl_excel.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
  body: new URLSearchParams({
    month: 'January',
    year: 2025
  })
})
.then(res => res.json())
.then(data => {
  // Download the file
  const link = document.createElement('a');
  link.href = '../generated excel files/' + data.filename;
  link.download = data.filename;
  link.click();
});
```

## File Management

### Local File Deletion
If a local Excel file is deleted from `generated excel files/` folder:
1. User attempts to download the file
2. System checks if file exists (HTTP HEAD request)
3. If file doesn't exist, automatically regenerates from database
4. Regenerated file has the exact same data as the original

### Benefits of This Approach
- **No manual intervention needed**: Users can recover deleted files
- **Data consistency**: Regenerated files match original perfectly
- **Space savings**: Can delete old local files if needed, still recoverable
- **Backup redundancy**: Data stored both in files and database

## Setup Instructions

1. Run the migration SQL file to create `stl_file_records` table:
   ```bash
   mysql -u root pagibig_db < database/create_stl_file_records.sql
   ```

2. Existing STL files can still be downloaded
3. For new generations, the employee data will be automatically stored

## Future Enhancements

- Add ability to bulk regenerate files for a date range
- Add file history/version tracking
- Add option to restore deleted files from database
- Add comparison tool to verify regenerated files match originals
