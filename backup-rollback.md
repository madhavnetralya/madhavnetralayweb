# Backup, Disaster Recovery & Rollback Runbook

**Project**: Madhav Netralaya Eye Hospital Web Portal & CMS  
**Classification**: Operational Recovery, Automated Snapshots & Rollback Procedures  
**Date**: September 2026  

---

## 🛡️ Overview & Data Safety Policy

This runbook defines standard operating procedures for creating pre-deployment backups, performing routine database snapshots, backing up CMS media assets, executing instant zero-downtime rollbacks, and handling emergency disaster recovery.

---

## 1. Automated & Pre-Deployment Backup Strategy

Before applying any updates, modifying server configurations, or deploying a new package release, execute a complete pre-deployment backup.

### Backup Components Overview

| Component | Files / Location | Criticality | Recommended Frequency |
| :--- | :--- | :--- | :--- |
| **SQLite Database** | `database.db` | **CRITICAL** | Hourly / Pre-deployment / Auto on write |
| **CMS Uploads & Media** | `storage/app/public/cms/` | **CRITICAL** | Daily / Pre-deployment |
| **Environment Config** | `.env` | **HIGH** | Whenever changed / Pre-deployment |
| **State Snapshots** | `storage/app/state_backups/` | **CRITICAL** | Auto-created by application on every edit |
| **Application Codebase** | App, Routes, Frontend Dist | **MEDIUM** | Per release |

---

## 2. Step-by-Step Backup Procedures

### A. Pre-Deployment Hot Backup (Linux / VPS)

Run the following unified backup script before deploying new code:

```bash
#!/bin/bash
# ==============================================================================
# MADHAV NETRALAYA PRE-DEPLOYMENT BACKUP SCRIPT
# ==============================================================================
set -e

APP_DIR="/var/www/madhavnetralaya"
BACKUP_ROOT="/var/backups/madhavnetralaya"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_DIR="${BACKUP_ROOT}/${TIMESTAMP}"

echo ">>> Creating backup directory at ${BACKUP_DIR}..."
mkdir -p "${BACKUP_DIR}"

# 1. Hot Backup of SQLite Database (using SQLite online backup API to prevent lock contention)
echo ">>> Performing hot backup of SQLite database..."
if [ -f "${APP_DIR}/database.db" ]; then
    sqlite3 "${APP_DIR}/database.db" ".backup '${BACKUP_DIR}/database_${TIMESTAMP}.db'"
    sha256sum "${APP_DIR}/database.db" > "${BACKUP_DIR}/database_checksum.sha256"
    echo "    Database backed up and checksum recorded."
fi

# 2. Backup CMS Media Uploads
echo ">>> Archiving CMS media files..."
if [ -d "${APP_DIR}/storage/app/public/cms" ]; then
    tar -czf "${BACKUP_DIR}/cms_media_${TIMESTAMP}.tar.gz" -C "${APP_DIR}/storage/app/public" cms
    echo "    CMS media archive created."
fi

# 3. Backup Environment Configuration
echo ">>> Backing up .env configuration..."
if [ -f "${APP_DIR}/.env" ]; then
    cp "${APP_DIR}/.env" "${BACKUP_DIR}/.env.backup_${TIMESTAMP}"
    chmod 600 "${BACKUP_DIR}/.env.backup_${TIMESTAMP}"
fi

# 4. Backup Full Application Snapshot (Optional for fast rollback)
echo ">>> Archiving full application release..."
tar --exclude="${APP_DIR}/storage/logs" \
    --exclude="${APP_DIR}/storage/framework" \
    --exclude="${APP_DIR}/node_modules" \
    -czf "${BACKUP_DIR}/full_app_snapshot_${TIMESTAMP}.tar.gz" -C "/var/www" madhavnetralaya

echo "=============================================================================="
echo ">>> Backup completed successfully: ${BACKUP_DIR}"
echo "=============================================================================="
```

---

### B. Windows Local Development / Staging Backup (PowerShell)

```powershell
$Timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$BackupDir = "e:\Madhav Netralaya Prod\backups\$Timestamp"
New-Item -ItemType Directory -Force -Path $BackupDir | Out-Null

# Copy SQLite Database
Copy-Item "database.db" "$BackupDir\database.db"

# Record SHA-256 Checksum
$Hash = (Get-FileHash "database.db").Hash
Set-Content "$BackupDir\checksum.txt" -Value $Hash

# Copy CMS Uploads
Copy-Item -Recurse -Force "laravel\storage\app\public\cms" "$BackupDir\cms"

# Copy .env
Copy-Item ".env" "$BackupDir\.env"

Write-Host "Pre-deployment backup created at $BackupDir (SHA256: $Hash)" -ForegroundColor Green
```

---

## 3. Automated State Snapshots

The application has a built-in pre-write safety mechanism located in `StateController.php` and `PortalController.php`:
* Every time an administrator updates content in the CMS, the backend automatically writes a complete JSON state backup to:
  ```text
  storage/app/state_backups/state_backup_auto_{TIMESTAMP}_{MICROTIME}.json
  ```
* These snapshots are never deleted automatically, providing a continuous audit log and point-in-time state recovery capability.

---

## 4. Rollback Procedure

If any issue, bug, or unforeseen failure occurs after deployment, execute the following rollback steps immediately:

### Step 1: Put Application into Maintenance Mode (Optional)
```bash
cd /var/www/madhavnetralaya
php artisan down --secret="madhav-admin-bypass-key"
```

### Step 2: Restore SQLite Database
```bash
# Locate the pre-deployment backup
BACKUP_DIR="/var/backups/madhavnetralaya/YYYYMMDD_HHMMSS"

# Replace database file
cp "${BACKUP_DIR}/database_*.db" /var/www/madhavnetralaya/database.db
chown www-data:www-data /var/www/madhavnetralaya/database.db
chmod 664 /var/www/madhavnetralaya/database.db

# Verify database checksum
sqlite3 /var/www/madhavnetralaya/database.db "PRAGMA integrity_check;"
```

### Step 3: Restore CMS Media Files (if modified)
```bash
tar -xzf "${BACKUP_DIR}/cms_media_*.tar.gz" -C /var/www/madhavnetralaya/storage/app/public/
chown -R www-data:www-data /var/www/madhavnetralaya/storage/app/public/cms
```

### Step 4: Revert Application Codebase
If using symbolic link releases (e.g. `/var/www/releases/20260924_01` -> `/var/www/madhavnetralaya`):
```bash
# Point symlink back to previous release
ln -sfn /var/www/releases/PREVIOUS_RELEASE /var/www/madhavnetralaya
```
Or extract the previous application snapshot:
```bash
tar -xzf "${BACKUP_DIR}/full_app_snapshot_*.tar.gz" -C /var/www/
```

### Step 5: Clear and Re-Cache Laravel Configurations
```bash
cd /var/www/madhavnetralaya
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo systemctl reload php8.3-fpm
php artisan up
```

### Step 6: Post-Rollback Validation
* Verify `https://www.madhavnetralaya.org/up` returns `HTTP 200 OK`.
* Verify `GET /api/state` returns valid JSON.
* Verify frontend loads with previous working version.

---

## 5. DNS Rollback Considerations

If changing server infrastructure, hosting providers, or IP addresses during a major migration:

1. **Pre-Migration TTL Reduction**:
   * Lower the DNS A record TTL to **300 seconds (5 minutes)** at least 24 hours before migration.
   * This ensures any emergency DNS rollback propagates worldwide within 5 minutes.
2. **Cloudflare / CDN Zero-Downtime Fallback**:
   * If using Cloudflare proxying, updating the Origin IP in Cloudflare DNS takes effect in under **30 seconds** globally without waiting for ISP DNS caches.
3. **Old Server Standby**:
   * Keep the previous server and database in read-only standby mode for at least 72 hours after migration.
4. **Post-Migration TTL Restoration**:
   * Once production stability is confirmed for 48 hours, increase TTL back to **3600 or 86400 seconds**.
