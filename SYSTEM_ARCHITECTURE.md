# STL File Regeneration System Architecture

## System Flow Diagram

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    STL FILE MANAGEMENT SYSTEM                           │
└─────────────────────────────────────────────────────────────────────────┘

                         USER ACTIONS
                              │
                    ┌─────────┴──────────┐
                    │                    │
              GENERATE FILE          DOWNLOAD FILE
                    │                    │
                    ▼                    ▼
         ┌──────────────────┐   ┌──────────────────────┐
         │ Generate Page    │   │ Check File Exists    │
         │ (select month)   │   │ (HTTP HEAD request)  │
         └──────────────────┘   └──────────┬───────────┘
                    │                      │
                    ▼                      ├─── YES → Download & Exit
         ┌──────────────────┐              │
         │ Fetch Employees  │              └─── NO → Regenerate
         │ from selected_stl│                      │
         └──────────────────┘                      ▼
                    │                    ┌──────────────────────┐
                    │                    │ Check Database       │
                    │                    │ (stl_file_records)   │
                    │                    └──────────┬───────────┘
                    │                               │
                    │                ┌──────────────┴────────────┐
                    │                │                           │
                    │                YES (Saved Data)        NO (New File)
                    │                │                           │
                    │                ▼                           ▼
                    │        ┌──────────────┐        ┌────────────────────┐
         ┌──────────┴────────▶│ Use Saved    │        │ Fetch from         │
         │                   │ Employee Data│        │ selected_stl Table │
         │                   │ from JSON    │        └────────────────────┘
         │                   └──────┬───────┘                   │
         │                          │                           │
         │                          └───────────┬───────────────┘
         │                                      │
         ▼                                      ▼
    ┌──────────────────────────────────────────────────┐
    │        Generate Excel File (PHPSpreadsheet)      │
    │ • Format headers                                 │
    │ • Add employee rows                              │
    │ • Calculate totals                               │
    │ • Style and format                               │
    └──────────────────────┬───────────────────────────┘
                           │
                           ▼
    ┌──────────────────────────────────────────────────┐
    │      Save to Disk & Database                     │
    │ • Save Excel: generated excel files/             │
    │ • Store metadata in stl_file_records             │
    │ • Store employee JSON snapshot                   │
    └──────────────────────┬───────────────────────────┘
                           │
                           ▼
    ┌──────────────────────────────────────────────────┐
    │      Return Success Response                     │
    │ • Filename                                       │
    │ • Record count                                   │
    │ • Total amounts                                  │
    └──────────────────────┬───────────────────────────┘
                           │
                           ▼
                    Download to User
```

## Database Schema Relationships

```
┌──────────────────────────────────────┐
│       selected_stl                   │
├──────────────────────────────────────┤
│ id (PK)                              │
│ pagibig_no                           │
│ id_number                            │
│ ee (Employee contribution)           │
│ er (Employer contribution)           │
│ tin                                  │
│ birthdate                            │
│ is_active                            │
│ user_id                              │
│ date_added                           │
└──────────────────────────────────────┘
           │
           │ (fetches active records)
           │
           ▼
┌──────────────────────────────────────┐
│     stl_file_records (NEW)           │
├──────────────────────────────────────┤
│ id (PK)                              │
│ filename                             │
│ month                                │
│ year                                 │
│ num_borrowers                        │
│ total_ee_deducted                    │
│ total_er_deducted                    │
│ employee_data (JSON)  ◄─────────────┐│
│ file_path                            ││
│ file_size                            ││
│ created_by (user_id)                 ││
│ created_date                         ││
│ updated_date                         ││
└──────────────────────────────────────┘│
                                        │
           (saves snapshot of)───────┘
                                        
           ▼
┌──────────────────────────────────────┐
│        Excel Files                   │
├──────────────────────────────────────┤
│ Location:                            │
│ generated excel files/               │
│ january_2025_stl.xlsx                │
│ february_2025_stl.xlsx               │
│ etc.                                 │
└──────────────────────────────────────┘
```

## Data Flow Example

### Example 1: Creating January 2025 STL File

```
Step 1: User selects January 2025 and clicks "Generate"
    └─> Fetch 5 active employees from selected_stl table

Step 2: Generate Excel file with:
    Employee Data:
    ┌─────────────────────────────────────────┐
    │ PAG-IBIG NO | EE    | ER     | TIN       │
    ├─────────────────────────────────────────┤
    │ 458879899666│ 0.00  │200.00  │732-888...│
    │ 445888665632│ 0.00  │200.00  │482-637...│
    │ 663672461231│ 0.00  │200.00  │376-476...│
    │ 723455215365│ 0.00  │200.00  │351-562...│
    │ 756739823498│ 0.00  │200.00  │766-372...│
    ├─────────────────────────────────────────┤
    │ TOTAL      │ 0.00  │1000.00 │           │
    └─────────────────────────────────────────┘

Step 3: Save files and records:
    
    File System:
    generated excel files/january_2025_stl.xlsx (12 KB)
    
    Database - stl_file_records:
    ┌───────────────────────────────────────────────────────┐
    │ filename: january_2025_stl.xlsx                       │
    │ month: January                                        │
    │ year: 2025                                            │
    │ num_borrowers: 5                                      │
    │ total_ee_deducted: 0.00                               │
    │ total_er_deducted: 1000.00                            │
    │ employee_data: [{"pagibig_no":"458879899666",...},]  │
    │ file_path: /...generated excel files/january_2025...│
    │ file_size: 12288                                      │
    │ created_by: 1 (admin)                                 │
    │ created_date: 2025-11-28 10:30:00                     │
    └───────────────────────────────────────────────────────┘
```

### Example 2: Recovering Deleted File

```
Step 1: Local file deleted
    generated excel files/ [january_2025_stl.xlsx] ✗ DELETED

Step 2: User clicks "Download" button

Step 3: System checks if file exists
    └─> File not found (404)

Step 4: Automatically regenerate from database
    └─> Query stl_file_records WHERE month='January' AND year=2025
    └─> Found record with saved employee_data JSON

Step 5: Use saved employee data to recreate Excel
    └─> No need to refetch from selected_stl
    └─> Uses exact same data as original file

Step 6: Save new Excel file to disk
    └─> generated excel files/january_2025_stl.xlsx ✓ RESTORED

Step 7: User downloads the recovered file
    └─> File is identical to the original
```

## Key Advantages Visualized

```
BEFORE (Without Database Storage):
┌──────────────────────────────┐
│ Local Excel Files Only       │
├──────────────────────────────┤
│ january_2025_stl.xlsx        │ ◄── Deleted by mistake
│ february_2025_stl.xlsx       │
│ march_2025_stl.xlsx          │
│                              │
│ ✗ No way to recover          │
│ ✗ No audit trail             │
│ ✗ Data lost forever          │
└──────────────────────────────┘


AFTER (With Database Storage):
┌──────────────────────────────┐
│ Local Excel Files (Optional) │
├──────────────────────────────┤
│ january_2025_stl.xlsx        │ ◄── Can be deleted, recoverable
│ february_2025_stl.xlsx       │
│ march_2025_stl.xlsx          │
└──────────────────────────────┘
          ▲
          │ (backed up in)
          │
┌──────────────────────────────┐
│ Database stl_file_records    │
├──────────────────────────────┤
│ ✓ Employee data snapshots    │
│ ✓ File metadata              │
│ ✓ Audit trail (who, when)    │
│ ✓ Can regenerate anytime     │
│ ✓ Data never lost            │
└──────────────────────────────┘
```

## API Response Examples

### Successful Generation/Regeneration

```json
{
  "status": "success",
  "message": "Excel file generated successfully",
  "filename": "january_2025_stl.xlsx",
  "filepath": "/xampp/htdocs/pagibig/generated excel files/january_2025_stl.xlsx",
  "record_count": 5,
  "total_ee": 0.00,
  "total_er": 1000.00
}
```

### Error Response (No Data Available)

```json
{
  "status": "error",
  "message": "No active STL records found in database"
}
```

## Implementation Checklist

- [x] Create `stl_file_records` table
- [x] Update `regenerate_stl_excel.php` to:
  - [x] Check for saved employee data first
  - [x] Fall back to current active employees if no saved data
  - [x] Save employee snapshot as JSON
  - [x] Store metadata in database
- [x] Update `stl.php` download function to:
  - [x] Check if local file exists
  - [x] Trigger regeneration if file missing
  - [x] Download automatically after regeneration
- [x] Create documentation
- [x] Database migration completed
- [x] Indexes created for performance
