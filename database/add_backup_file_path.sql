-- Add backup file path column to stl_summary table
ALTER TABLE stl_summary ADD COLUMN IF NOT EXISTS backup_file_path VARCHAR(255) AFTER filename;
