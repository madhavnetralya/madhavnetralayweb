#!/bin/bash
set -e

# =================================================================
# MADHAV NETRALAYA - CPANEL AUTOMATED GIT DEPLOYMENT SCRIPT
# Triggered automatically via .cpanel.yml on cPanel Git Version Control
# =================================================================

CPANEL_USER=\desktop-6fggrsk\prana
HOME_DIR="/home/\"
CORE_DIR="\/core"
PUBLIC_DIR="\/public_html"
REPO_DIR="\E:\Madhav Netralaya Prod\madhavnetralaya-eye-hospital\deployment"

echo "========================================================"
echo "Starting Madhav Netralaya Git Deployment..."
echo "cPanel User:    \"
echo "Core Path:      \"
echo "Public Path:    \"
echo "Repository:     \"
echo "========================================================"

# 1. Ensure target core directories exist
mkdir -p "\"
mkdir -p "\/storage/framework/cache"
mkdir -p "\/storage/framework/sessions"
mkdir -p "\/storage/framework/views"
mkdir -p "\/storage/logs"
mkdir -p "\/storage/app/public/cms"
mkdir -p "\/storage/app/state_backups"
mkdir -p "\/bootstrap/cache"
mkdir -p "\"

# 2. Deploy Laravel Backend Code (from package/)
echo "[1/6] Copying backend files to \..."
cp -R "\/package/app" "\/"
cp -R "\/package/bootstrap" "\/"
cp -R "\/package/database" "\/"
cp -R "\/package/resources" "\/"
cp -R "\/package/routes" "\/"
cp "\/package/artisan" "\/"
cp "\/package/composer.json" "\/"
cp "\/package/composer.lock" "\/"

# 3. Environment Protection: NEVER overwrite an existing .env
echo "[2/6] Checking environment file..."
if [ ! -f "\/.env" ]; then
    echo "      Creating initial .env from template..."
    cp "\/.env.production.example" "\/.env"
else
    echo "      Existing .env detected. Preserving production credentials."
fi

# 4. Database Protection: NEVER overwrite an existing live database.db
echo "[3/6] Checking SQLite database..."
if [ ! -f "\/database.db" ]; then
    if [ -f "\/package/database.db" ]; then
        echo "      Copying initial database.db..."
        cp "\/package/database.db" "\/database.db"
    fi
else
    echo "      Existing live database.db detected. Preserving intact."
fi

# 5. Deploy Public Webroot Files (Frontend bundle, API entry, index.html, assets)
echo "[4/6] Copying public files to \..."
cp -R "\/package/public/"* "\/"

# 6. Ensure Storage Link exists for uploaded media
echo "[5/6] Verifying storage symlink..."
if [ ! -L "\/storage" ] && [ ! -d "\/storage" ]; then
    ln -s "\/storage/app/public" "\/storage" || true
    echo "      Storage symlink created."
fi

# 7. Set Permissions on writable directories
echo "[6/6] Setting permissions..."
chmod -R 775 "\/storage" "\/bootstrap/cache" 2>/dev/null || true

# 8. Run Laravel optimizations if PHP CLI is accessible
if command -v php >/dev/null 2>&1; then
    echo "Optimizing Laravel cache..."
    php "\/artisan" config:clear 2>/dev/null || true
    php "\/artisan" route:clear 2>/dev/null || true
    php "\/artisan" view:clear 2>/dev/null || true
    php "\/artisan" config:cache 2>/dev/null || true
    php "\/artisan" route:cache 2>/dev/null || true
    php "\/artisan" view:cache 2>/dev/null || true
fi

echo "========================================================"
echo "Deployment completed successfully!"
echo "========================================================"
