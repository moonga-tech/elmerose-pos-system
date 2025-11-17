# Migrations README

This folder contains SQL migrations for the Elmerose POS project.

Current migration:
- `2025-11-16-add-delivery-address.sql` — adds `delivery_address` (TEXT NULL) to the `orders` table.

IMPORTANT: Always back up your database before running migrations.

Windows (PowerShell) instructions

1) Back up your database (example using XAMPP MySQL binary). Adjust paths and DB user as needed:

```powershell
# create backups folder if needed
New-Item -ItemType Directory -Force -Path "c:\xampp\htdocs\elmerose-pos\database\backups"

# backup database (will prompt for password)
& "C:\xampp\mysql\bin\mysqldump.exe" -u root -p elmerose_pos > "c:\xampp\htdocs\elmerose-pos\database\backups\elmerose_pos_backup_$(Get-Date -Format yyyyMMdd).sql"
```

2) Apply the single migration (recommended):

```powershell
# Run the migration SQL against the elmerose_pos database
& "C:\xampp\mysql\bin\mysql.exe" -u root -p elmerose_pos < "c:\xampp\htdocs\elmerose-pos\migrations\2025-11-16-add-delivery-address.sql"
```

3) (Alternative) Recreate database from updated `database/db.sql` (this will DROP the database if present):

```powershell
# This will recreate the database as in database/db.sql (WARNING: destructive)
& "C:\xampp\mysql\bin\mysql.exe" -u root -p < "c:\xampp\htdocs\elmerose-pos\database\db.sql"
```

Notes
- If `mysql.exe` or `mysqldump.exe` are not in the shown path, adjust to your installation (e.g., `C:\xampp\mysql\bin\mysql.exe`).
- If you use a non-root DB user, replace `root` with your user and ensure it has ALTER privilege.
- The migration uses `ALTER TABLE ... ADD COLUMN IF NOT EXISTS` for safety; you can run it multiple times safely.

If you'd like, I can:
- Prepare a non-destructive SQL script that first checks column existence and logs actions.
- Attempt to run the migration for you (you would need to provide connection info or run the command locally and paste output).
