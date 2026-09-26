# Production Deployment Guide - Madhav Netralaya Eye Hospital

This guide provides exhaustive, step-by-step instructions for deploying the **Madhav Netralaya Eye Hospital** web application and Content Management System (CMS) to production environments.

---

## Table of Contents

1. [System & Hosting Prerequisites](#1-system--hosting-prerequisites)
2. [PHP Version & Required Extensions](#2-php-version--required-extensions)
3. [Environment Configuration (`.env`)](#3-environment-configuration-env)
4. [Database Setup & SQLite Integrity](#4-database-setup--sqlite-integrity)
5. [Storage Architecture & `storage:link`](#5-storage-architecture--storagelink)
6. [File Permissions & Directory Hardening](#6-file-permissions--directory-hardening)
7. [Web Server Configuration (Nginx & Apache)](#7-web-server-configuration-nginx--apache)
8. [SSL / HTTPS Enforcement](#8-ssl--https-enforcement)
9. [Domain & DNS Configuration](#9-domain--dns-configuration)
10. [Email / SMTP Delivery Setup](#10-email--smtp-delivery-setup)
11. [Laravel Production Optimization Commands](#11-laravel-production-optimization-commands)
12. [Post-Deployment Health & Verification Procedures](#12-post-deployment-health--verification-procedures)
13. [Shared Hosting (cPanel / InfinityFree) Deployment Workflow](#13-shared-hosting-cpanel--infinityfree-deployment-workflow)

---

## 1. System & Hosting Prerequisites

The application can be hosted on:
* **Dedicated / Virtual Private Server (VPS)**: Ubuntu 22.04 / 24.04 LTS, Debian 12, AlmaLinux / Rocky Linux (Recommended).
* **Cloud Platforms**: AWS (EC2 / Lightsail), DigitalOcean Droplet, Linode, Google Compute Engine.
* **Shared Hosting Platforms**: cPanel, Plesk, DirectAdmin, InfinityFree (PHP 8.2+ with SQLite support).

### Minimum Server Specifications

| Component | Minimum Specification | Recommended Specification |
| :--- | :--- | :--- |
| **CPU** | 1 vCPU (1.5 GHz+) | 2 vCPU or higher |
| **RAM** | 1 GB | 2 GB to 4 GB (handles DomPDF & image processing smoothly) |
| **Storage** | 10 GB SSD / NVMe | 25 GB+ SSD (allows room for CMS media and automated backups) |
| **OS** | Linux (Ubuntu 22.04/24.04 LTS) | Linux (Ubuntu 24.04 LTS) |
| **Web Server** | Nginx 1.20+ or Apache 2.4+ | Nginx 1.24+ with HTTP/2 and Gzip/Brotli |

---

## 2. PHP Version & Required Extensions

### Recommended PHP Version
* **PHP 8.2.2** or **PHP 8.3.x** (PHP 8.3 is strongly recommended for optimal performance and security).

### Required PHP Extensions

Ensure the following PHP modules are installed and enabled in `php.ini`:

```bash
# Ubuntu / Debian installation command
sudo apt update
sudo apt install -y php8.3-cli php8.3-fpm php8.3-common php8.3-sqlite3 php8.3-curl \
                    php8.3-mbstring php8.3-xml php8.3-zip php8.3-gd php8.3-bcmath \
                    php8.3-intl php8.3-tokenizer php8.3-opcache
```

| Extension | Purpose in Application |
| :--- | :--- |
| `pdo_sqlite` & `sqlite3` | Essential for database communication with `database.db`. |
| `mbstring` | Multi-byte string manipulation for CMS multilingual text and titles. |
| `openssl` | Cryptographic functions, password hashing, and API authentication. |
| `curl` | Server-side HTTP requests (e.g. Gemini AI Assistant, Razorpay, Instagram Feed). |
| `fileinfo` | Strict MIME type validation during staff image and PDF uploads. |
| `gd` or `imagick` | Image processing, thumbnail rendering, and DomPDF logo integration. |
| `xml` & `dom` | XML/HTML parsing used by Barryvdh DomPDF for certificate generation. |
| `zip` | Package compression and archive handling. |
| `bcmath` | Precision arithmetic for Razorpay transaction calculations. |
| `opcache` | Bytecode caching for lightning-fast PHP execution. |

### Recommended `php.ini` Settings for Production

```ini
memory_limit = 256M
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 60
max_input_time = 60
expose_php = Off
display_errors = Off
log_errors = On
error_log = /var/log/php/error.log
date.timezone = Asia/Kolkata
```

---

## 3. Environment Configuration (`.env`)

Never commit real secrets or production credentials to source control. Use the template provided in `deployment/.env.production.example`.

### Step 1: Copy and Initialize `.env`
```bash
cp .env.production.example .env
```

### Step 2: Key Production Variables

* `APP_NAME`: Set to `"Madhav Netralaya"`.
* `APP_ENV`: Set strictly to `production`.
* `APP_KEY`: Generate a unique 32-byte base64 key using `php artisan key:generate`.
* `APP_DEBUG`: **MUST BE `false`**. Never enable debug mode in production (prevents exposure of database credentials and stack traces).
* `APP_URL`: Set to your canonical HTTPS domain, e.g., `https://www.madhavnetralaya.org`.
* `APP_TIMEZONE`: Set to `Asia/Kolkata` (or `UTC`).
* `DB_CONNECTION`: Set to `sqlite`.
* `DB_DATABASE`: Set to the absolute path of the database file, e.g., `/var/www/madhavnetralaya/database.db`.
* `FILESYSTEM_DISK`: Set to `public`.
* `SESSION_DRIVER`: Set to `file` (or `database`).
* `LOG_CHANNEL`: Set to `daily` or `stack`.
* `LOG_LEVEL`: Set to `error` or `warning` in production to minimize disk I/O.

---

## 4. Database Setup & SQLite Integrity

The application relies on SQLite for zero-latency, transactional state persistence.

### Database File Location
* Place `database.db` in the application root or inside `database/` with strict file permissions:
  * Owned by the web server user (`www-data`).
  * Permission: `chmod 664 /var/www/madhavnetralaya/database.db` (or `660`).
  * The parent directory MUST be writable by the web server user so SQLite can create temporary `-shm` (Shared Memory) and `-wal` (Write-Ahead Logging) journal files.

### 🛡️ Data Preservation Rules
1. **Never run destructive commands**:
   ```bash
   # DO NOT EXECUTE THESE IN PRODUCTION:
   php artisan migrate:fresh
   php artisan db:wipe
   php artisan migrate:refresh
   ```
2. **Safe Migration Execution (if applying incremental schema additions)**:
   ```bash
   php artisan migrate --force
   ```
3. **Verify Database Integrity**:
   ```bash
   sqlite3 database.db "PRAGMA integrity_check;"
   # Expected Output: ok
   ```

### Enabling SQLite Write-Ahead Logging (WAL Mode)
For improved concurrency and read/write performance under traffic:
```bash
sqlite3 database.db "PRAGMA journal_mode=WAL;"
sqlite3 database.db "PRAGMA synchronous=NORMAL;"
```

---

## 5. Storage Architecture & `storage:link`

The application stores user uploads and CMS media assets in `storage/app/public/cms/`.

### Directory Layout
```text
storage/
├── app/
│   ├── public/
│   │   └── cms/
│   │       ├── career/        # Resume and CV uploads
│   │       ├── doctors/       # Doctor profile portraits
│   │       ├── general/       # Infrastructure and department photos
│   │       ├── homepage/      # Hero sliders and banners
│   │       └── settings/      # Hospital logo and favicon
│   └── state_backups/         # Automated pre-write JSON state snapshots
├── framework/
│   ├── cache/                 # Laravel framework data cache
│   ├── sessions/              # User session files
│   └── views/                 # Blade compiled templates
└── logs/                      # Application error and activity logs
```

### Linking Storage to Public Web Root
Execute the Artisan command to create the symbolic link:
```bash
php artisan storage:link
```
This maps `public/storage` -> `storage/app/public`.

*On Shared Hosting without symlink support, copy `storage/app/public/*` into `public/storage/` or configure direct asset serving.*

---

## 6. File Permissions & Directory Hardening

Set appropriate ownership and permissions to prevent unauthorized modifications while allowing Laravel and SQLite to write logs, caches, and uploaded assets.

```bash
# Assuming web server user is www-data and app directory is /var/www/madhavnetralaya

# 1. Assign ownership to web server user and deployment group
sudo chown -R www-data:www-data /var/www/madhavnetralaya

# 2. Standard directory and file permissions
sudo find /var/www/madhavnetralaya -type d -exec chmod 755 {} \;
sudo find /var/www/madhavnetralaya -type f -exec chmod 644 {} \;

# 3. Writable permissions for Storage, Cache and Database
sudo chmod -R 775 /var/www/madhavnetralaya/storage
sudo chmod -R 775 /var/www/madhavnetralaya/bootstrap/cache
sudo chmod 664 /var/www/madhavnetralaya/database.db
sudo chmod 775 /var/www/madhavnetralaya  # Allows SQLite journal file creation

# 4. Protect sensitive files
sudo chmod 600 /var/www/madhavnetralaya/.env
```

---

## 7. Web Server Configuration (Nginx & Apache)

### Nginx Server Block Configuration (`/etc/nginx/sites-available/madhavnetralaya.conf`)

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name madhavnetralaya.org www.madhavnetralaya.org;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name madhavnetralaya.org www.madhavnetralaya.org;

    # Document Root points to Laravel's public directory
    root /var/www/madhavnetralaya/public;
    index index.php index.html;

    # SSL Certificates
    ssl_certificate /etc/letsencrypt/live/madhavnetralaya.org/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/madhavnetralaya.org/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # Client Max Body Size for PDF and Media Uploads
    client_max_body_size 50M;

    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml application/json application/javascript application/xml+rss application/atom+xml image/svg+xml;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Pass PHP scripts to PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
        fastcgi_busy_buffers_size 256k;
    }

    # Static Asset Caching
    location ~* \.(jpg|jpeg|png|gif|ico|webp|svg|css|js|woff|woff2|ttf|otf)$ {
        expires 1y;
        add_header Cache-Control "public, no-transform";
        access_log off;
    }

    # Block access to hidden files (.env, .git)
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Deny direct access to SQLite database files if stored within public path
    location ~* \.(db|sqlite|sqlite3|wal|shm)$ {
        deny all;
    }
}
```

### Apache VirtualHost Configuration (`/etc/apache2/sites-available/madhavnetralaya.conf`)

```apache
<VirtualHost *:80>
    ServerName madhavnetralaya.org
    ServerAlias www.madhavnetralaya.org
    Redirect permanent / https://www.madhavnetralaya.org/
</VirtualHost>

<VirtualHost *:443>
    ServerName www.madhavnetralaya.org
    ServerAlias madhavnetralaya.org
    DocumentRoot /var/www/madhavnetralaya/public

    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/madhavnetralaya.org/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/madhavnetralaya.org/privkey.pem

    <Directory /var/www/madhavnetralaya/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # Custom upload limit
    LimitRequestBody 52428800

    # Deny access to sensitive files
    <FilesMatch "(^\.env|\.db|\.sqlite|composer\.(json|lock)|package\.json)">
        Require all denied
    </FilesMatch>

    ErrorLog ${APACHE_LOG_DIR}/madhavnetralaya_error.log
    CustomLog ${APACHE_LOG_DIR}/madhavnetralaya_access.log combined
</VirtualHost>
```

---

## 8. SSL / HTTPS Enforcement

Securing all medical and administrative communications is mandatory.

### Obtaining a Free Let's Encrypt SSL Certificate
```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d madhavnetralaya.org -d www.madhavnetralaya.org
```

### Automatic Certificate Renewal
Test the automated renewal timer:
```bash
sudo certbot renew --dry-run
```

---

## 9. Domain & DNS Configuration

Configure the following DNS records with your registrar or DNS provider (e.g. Cloudflare, GoDaddy, AWS Route 53):

| Record Type | Host / Name | Value / Target | TTL | Purpose |
| :--- | :--- | :--- | :--- | :--- |
| **A** | `@` | `YOUR_SERVER_IP` | 300 / Auto | Points apex domain to server |
| **A** or **CNAME** | `www` | `@` (or `YOUR_SERVER_IP`) | 300 / Auto | Subdomain routing |
| **TXT** | `@` | `v=spf1 include:_spf.yourmailhost.com ~all` | 3600 | SPF email anti-spoofing |
| **TXT** | `mail._domainkey` | `k=rsa; p=YOUR_DKIM_PUBLIC_KEY` | 3600 | DKIM cryptographic signature |
| **TXT** | `_dmarc` | `v=DMARC1; p=quarantine; rua=mailto:admin@madhavnetralaya.org` | 3600 | DMARC policy enforcement |

---

## 10. Email / SMTP Delivery Setup

The application sends automated notifications for:
* Patient appointment confirmations
* General contact and consultation inquiries
* Eye donation pledges & downloadable DomPDF certificates
* Job application confirmations

### Production SMTP Configuration in `.env`

```ini
MAIL_MAILER=smtp
MAIL_HOST=smtp.brevo.com        # Or smtp.sendgrid.net, email-smtp.us-east-1.amazonaws.com
MAIL_PORT=587
MAIL_USERNAME=YOUR_SMTP_USERNAME
MAIL_PASSWORD=YOUR_SMTP_PASSWORD
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="info@madhavnetralaya.org"
MAIL_FROM_NAME="Madhav Netralaya Eye Hospital"
```

### Testing Email Delivery
Test using Laravel Tinker:
```bash
php artisan tinker
> Illuminate\Support\Facades\Mail::raw('Test production email delivery from Madhav Netralaya.', function ($m) { $m->to('admin@madhavnetralaya.org')->subject('SMTP Test'); });
```

---

## 11. Laravel Production Optimization Commands

Before taking the application live, execute Laravel's production caching suite:

```bash
# 1. Install production dependencies without development packages
composer install --optimize-autoloader --no-dev

# 2. Clear old cached configurations
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 3. Cache configuration and routes for high throughput
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

> [!TIP]
> Whenever `.env` is updated in production, you **MUST** re-run `php artisan config:cache` for changes to take effect.

---

## 12. Post-Deployment Health & Verification Procedures

Verify every component systematically after deployment:

1. **Backend Health Check**:
   * Visit `https://www.madhavnetralaya.org/up` -> Expect `HTTP 200 OK`.
2. **State Store API**:
   * Visit `https://www.madhavnetralaya.org/api/state` -> Expect full CMS JSON payload.
3. **Frontend SPA Loading**:
   * Visit `https://www.madhavnetralaya.org/` -> Verify header, hero slider, department icons, doctor portraits, and visual navigation.
4. **Admin Panel Access**:
   * Navigate to `/admin` or click Admin login.
   * Verify authentication, CMS edit previews, and state updates.
5. **Media Asset Serving**:
   * Verify images load directly from `https://www.madhavnetralaya.org/storage/cms/doctors/...` without 404 errors.
6. **Lead Forms & Interactive Features**:
   * Submit an appointment request -> Verify record appears in database.
   * Test the Eye Consultation popup modal form -> Verify submission succeeds.
   * Submit feedback on the Feedback page -> Verify database persistence.
7. **Certificate PDF Generation**:
   * Test eye donation certificate generation endpoint -> Verify clean PDF download without memory exhaustion.

---

## 13. Shared Hosting (cPanel / InfinityFree) Deployment Workflow

If deploying to a shared hosting environment where the web root is fixed to `public_html` or `htdocs`:

1. Upload the entire `package/` content.
2. Structure the directory as follows:
   ```text
   /home/user/
   ├── core/                  # Place app, bootstrap, database, resources, routes, storage, vendor, database.db, .env here
   └── public_html/           # Place contents of package/public here (index.html, index.php, assets, images, storage)
   ```
3. Update `/home/user/public_html/index.php`:
   ```php
   <?php
   use Illuminate\Http\Request;
   define('LARAVEL_START', microtime(true));

   if (file_exists($maintenance = __DIR__.'/../core/storage/framework/maintenance.php')) {
       require $maintenance;
   }

   require __DIR__.'/../core/vendor/autoload.php';

   (require_once __DIR__.'/../core/bootstrap/app.php')
       ->handleRequest(Request::capture());
   ```
4. Copy `storage/app/public/*` into `public_html/storage/` so that uploads remain directly accessible via HTTP.
5. Verify `.htaccess` inside `public_html/` enforces rewrite rules and blocks direct HTTP access to `.env` and `.db` files.
