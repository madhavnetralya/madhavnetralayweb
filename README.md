# Madhav Netralaya Eye Hospital - Production Deployment Workspace

Welcome to the **Production Deployment** workspace for **Madhav Netralaya Eye Hospital**.

This dedicated `deployment/` directory contains all production-ready deployment packages, server configuration templates, architectural reviews, security audits, and operations checklists required to deploy and maintain the application in a live production environment.

---

## 🎯 Purpose of this Directory

1. **Isolation from Active Codebase**: Provides a clean, self-contained deployment package in `deployment/package/` without altering or endangering the development codebase or live data.
2. **Production Documentation**: Serves as the single source of truth for system administrators, DevOps engineers, and hosting providers.
3. **Zero Data-Loss Compliance**: Enforces strict protocols to protect hospital records, CMS content, appointment leads, patient feedback, and media assets during deployment.
4. **Standardized Operations**: Documents backup, rollback, security hardening, and performance tuning procedures.

---

## 🏥 About the Application

**Madhav Netralaya Eye Hospital** is a modern healthcare web application and Content Management System (CMS) featuring:

* **Frontend**: React 19 + TypeScript Single Page Application (SPA), bundled with Vite, styled with Tailwind CSS, featuring Lucide icons and Motion animations.
* **Backend**: Laravel 12 REST API providing endpoints for dynamic CMS state management, appointment bookings, patient feedback, career applications, eye consultation leads, newsletter subscriptions, Razorpay transactions, and DomPDF certificate generation.
* **Database**: High-performance SQLite database (`database.db`) featuring atomic state persistence, SQLite transaction locks, and automatic pre-write backups (`storage/app/state_backups`).
* **Media Management**: Structured CMS storage (`storage/app/public/cms/`) for doctors, departments, facilities, events, career attachments, and hospital infrastructure.

---

## 📁 Directory Structure

```text
deployment/
├── README.md                  # This file - Overview and architecture
├── DEPLOYMENT.md              # Complete step-by-step deployment guide
├── .env.production.example    # Safe production environment variable template
├── production-checklist.md    # Pre-flight, deployment & post-launch verification checklist
├── security-review.md         # Comprehensive security audit & hardening guidelines
├── performance-review.md      # Performance benchmarks, caching & optimization guide
├── backup-rollback.md         # Disaster recovery, hot backup & rollback procedures
└── package/                   # Complete, clean, deployable production package
    ├── app/                   # Laravel application core
    ├── bootstrap/             # Application bootstrapping & cache
    ├── database/              # Schema migrations & seeders
    ├── database.db            # Production SQLite database (verified copy)
    ├── public/                # Web root (Vite SPA build, index.php, .htaccess)
    │   ├── assets/            # Compiled CSS/JS bundles
    │   ├── images/            # Static brand assets
    │   ├── uploads/           # Public media upload directories
    │   ├── index.html         # Frontend SPA entry point
    │   ├── index.php          # Laravel front controller
    │   ├── .htaccess          # Apache rewrite rules & security headers
    │   └── .user.ini          # PHP upload limits & memory settings
    ├── resources/             # Views and templates
    ├── routes/                # API and Web route declarations
    ├── storage/               # Application storage, logs, and CMS media assets
    │   └── app/public/cms/    # Production uploaded media (doctors, banners, etc.)
    ├── vendor/                # Pre-installed Composer production dependencies
    ├── artisan                # Laravel CLI executable
    ├── composer.json          # Dependency definitions
    └── composer.lock          # Locked dependency manifest
```

---

## 🚀 How to Use the Deployment Package

Depending on your hosting infrastructure, choose one of the following methods:

### Option A: Standard VPS / Cloud Server (Ubuntu/Debian, Nginx/Apache) - Recommended
1. Upload the entire contents of `deployment/package/` to `/var/www/madhavnetralaya`.
2. Configure your web server Document Root to `/var/www/madhavnetralaya/public`.
3. Create `.env` from `deployment/.env.production.example` and set production credentials.
4. Set permissions: `chown -R www-data:www-data /var/www/madhavnetralaya` and `chmod -R 775 storage bootstrap/cache`.
5. Run `php artisan storage:link` and Laravel cache optimization commands.

### Option B: Shared Hosting (cPanel / Plesk / InfinityFree / DirectAdmin)
1. **If Root Access / Custom Document Root is supported**: Upload `package/` outside the web root and point `public_html` (or `htdocs`) to `package/public/`.
2. **If Single Web Root (`public_html` / `htdocs`) only**:
   * Move the contents of `package/public/` directly into `public_html/`.
   * Place the core folders (`app/`, `bootstrap/`, `database/`, `resources/`, `routes/`, `storage/`, `vendor/`, `database.db`, `.env`) in a subdirectory named `core/` inside `public_html/` (or adjacent to `public_html/`).
   * Update `index.php` paths to reference `__DIR__.'/core/...'` (refer to `DEPLOYMENT.md` for ready-to-use shared hosting config).
   * Ensure `.htaccess` blocks direct access to `core/`, `.env`, and `database.db`.

---

## 🛡️ Critical Data-Safety Precautions

> [!CAUTION]
> **CRITICAL DATA-SAFETY RULES:**
> 1. **NEVER run destructive database commands** in production:
>    * ❌ `php artisan migrate:fresh`
>    * ❌ `php artisan db:wipe`
>    * ❌ `php artisan migrate:refresh`
>    * ❌ Truncating or deleting `database.db`
> 2. **NEVER overwrite or delete uploaded media**:
>    * The `storage/app/public/cms/` directory contains live hospital photos, staff records, and certificates. Never clear or overwrite this directory with empty folders.
> 3. **Preserve `database.db`**:
>    * Always verify the checksum of `database.db` before and after file transfers.
> 4. **Pre-Write Backups**:
>    * The application backend includes automatic pre-write state backups located in `storage/app/state_backups/`. Ensure this folder remains writable.

---

## 📚 Documentation Reference

| Document | Description |
| :--- | :--- |
| **[DEPLOYMENT.md](file:///e:/Madhav%20Netralaya%20Prod/madhavnetralaya-eye-hospital/deployment/DEPLOYMENT.md)** | Step-by-step deployment procedure, PHP extensions, Nginx/Apache configuration, SSL, DNS, and SMTP setup. |
| **[.env.production.example](file:///e:/Madhav%20Netralaya%20Prod/madhavnetralaya-eye-hospital/deployment/.env.production.example)** | Sanitized environment variable template ready for production configuration. |
| **[production-checklist.md](file:///e:/Madhav%20Netralaya%20Prod/madhavnetralaya-eye-hospital/deployment/production-checklist.md)** | 15-category pre-flight, deployment, and post-launch verification checklist. |
| **[security-review.md](file:///e:/Madhav%20Netralaya%20Prod/madhavnetralaya-eye-hospital/deployment/security-review.md)** | Security evaluation, threat analysis, access control, upload safety, and header hardening. |
| **[performance-review.md](file:///e:/Madhav%20Netralaya%20Prod/madhavnetralaya-eye-hospital/deployment/performance-review.md)** | Caching, asset compression, OPcache, SQLite WAL mode, and CDN configuration. |
| **[backup-rollback.md](file:///e:/Madhav%20Netralaya%20Prod/madhavnetralaya-eye-hospital/deployment/backup-rollback.md)** | Database hot backup, file backup scripts, disaster recovery, and instant rollback guide. |
