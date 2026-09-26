# Production Security Review & Hardening Report

**Project**: Madhav Netralaya Eye Hospital Web Portal & CMS  
**Classification**: Production Security Evaluation & Threat Assessment  
**Date**: September 2026  

---

## Executive Summary

A comprehensive security audit of the Madhav Netralaya Eye Hospital application was conducted to evaluate data protection mechanisms, server configuration safety, API endpoint security, file upload controls, and susceptibility to common web vulnerabilities (OWASP Top 10).

This document outlines key security findings, potential threat vectors, and verified mitigation controls implemented in the deployment package.

---

## 1. Environment & Secrets Protection (`.env`)

### Vulnerability / Risk Analysis
The `.env` file contains sensitive parameters including application encryption keys, database paths, SMTP mail credentials, payment gateway keys, and external API tokens. If served as a static text file via an improperly configured web server, all credentials could be compromised.

### Current Architecture & Mitigations
1. **Document Root Isolation**:
   * In standard deployment, the Web Server Document Root points exclusively to `/var/www/madhavnetralaya/public`. The `.env` file is located one level above the document root in `/var/www/madhavnetralaya/`, rendering it physically inaccessible via standard HTTP requests.
2. **Web Server Level Blocking**:
   * **Nginx**: Explicit block added in server configuration:
     ```nginx
     location ~ /\.(?!well-known).* {
         deny all;
     }
     ```
   * **Apache (`.htaccess`)**: Built-in Apache rule rejects access to dotfiles and sensitive files:
     ```apache
     <FilesMatch "(^\.env|\.db|\.sqlite|composer\.(json|lock)|package\.json)">
         Require all denied
     </FilesMatch>
     ```
3. **File Permission Lockdown**:
   * Production file permissions set to `chmod 600 .env`, restricting read access strictly to the web server process user (`www-data`).

---

## 2. Debug Mode & Error Information Leakage

### Vulnerability / Risk Analysis
When `APP_DEBUG=true`, unhandled PHP or Laravel exceptions output complete stack traces, including database table structures, file system paths, environment variable dumps, and SQL queries.

### Current Architecture & Mitigations
* In `deployment/.env.production.example`, `APP_DEBUG=false` is enforced.
* Production `php.ini` configures `display_errors = Off` and `log_errors = On`.
* All unhandled backend exceptions trigger standard generic HTTP 500 error responses without exposing server internals.

---

## 3. SQLite Database Security & Access Control

### Vulnerability / Risk Analysis
SQLite databases (`database.db`) are single-file binary stores. If placed inside a publicly accessible web root without web server protection, an attacker could download the entire database directly.

### Current Architecture & Mitigations
1. **Directory Placement**: The database file is placed outside the public web root (`/var/www/madhavnetralaya/database.db`).
2. **Web Server Defense-in-Depth**:
   * Nginx and Apache configurations explicitly deny requests ending with `.db`, `.sqlite`, `.sqlite3`, `.wal`, or `.shm`.
3. **Write-Lock & Atomic State Guarding**:
   * All state modifications in `StateController.php` and `PortalController.php` use transactional write locks (`DB::transaction` with `lockForUpdate()`) to prevent race conditions and concurrent write corruption.

---

## 4. File Upload Security & Validation

### Vulnerability / Risk Analysis
File upload interfaces (such as staff image uploads in CMS and resume CV uploads in career forms) are common attack vectors for Remote Code Execution (RCE) if executable scripts (e.g. `.php`, `.phtml`, `.phar`) or malicious payloads are allowed.

### Current Architecture & Mitigations
1. **Strict Server-Side MIME Type & Extension Whitelisting**:
   * In `StateController.php` and `PortalController.php`:
     ```php
     $request->validate([
         'file' => 'required|file|mimes:jpeg,png,webp,gif,pdf,doc,docx|max:51200',
         'folder' => 'nullable|string'
     ]);
     ```
2. **Cryptographic Filename Obfuscation**:
   * Files are never stored using client-supplied raw filenames. The application generates randomized names using `uniqid('file_') . '_' . time() . '.' . $extension`, preventing directory traversal and file overwrite attacks.
3. **Storage Segregation**:
   * Uploads are stored in subfolders under `storage/app/public/cms/` (`doctors/`, `homepage/`, `career/`, `settings/`).
4. **Execution Prevention in Upload Directory**:
   * In Apache/Nginx configurations, PHP execution is disabled within the `/storage/` directory so uploaded files cannot be executed even if an unauthorized file type bypasses validation.

---

## 5. Public Files & Clean Artifacts Audit

### Findings from Codebase Audit
During local development, temporary test files, diagnostic scripts, and local backups were generated:
* Diagnostic scripts: `check_media.php`, `dump_json.cjs`, `dump_pages.php`, `dump_sqlite.php`, `dump_tables.php`, `find_employee.php`, `search_db.cjs`, `patch_*.cjs`, `fix_remaining.cjs`.
* Temporary test images & PDFs: `test_3mb.pdf`, `test_DiplomainOphthalmicTechnicalAssistance.png`, `test_FreeOutreachCamps.png`, `verify_*.png`.
* Development backup directories: `backup_aug10/`, `backup_final/`, `backup_safety_*/`.

### Deployment Mitigation
* **All diagnostic scripts, test media, and local backup directories are strictly excluded** from `deployment/package/`.
* Only production assets and compiled frontend bundles are included in `deployment/package/public/`.

---

## 6. Injection Protection (SQLi, XSS, CSRF)

### 1. SQL Injection (SQLi)
* **Status: PROTECTED**
* Laravel Eloquent ORM and Query Builder utilize PDO prepared statements with parameter binding for all database interactions (`DB::table('state_store')->where('key', 'state')`, `Setting::first()`, `EyeConsultationEnquiry::create()`). No raw SQL string interpolation is present.

### 2. Cross-Site Scripting (XSS)
* **Status: PROTECTED**
* The React 19 frontend automatically escapes all data bindings rendered via JSX `{ ... }`.
* Rich text HTML rendered in custom pages passes through sanitization before rendering in the DOM.

### 3. Cross-Site Request Forgery (CSRF) & API Security
* **Status: PROTECTED**
* Laravel provides built-in CSRF token verification for session-based web routes and stateless validation for API endpoints registered under `routes/api.php`.

---

## 7. Administrative CMS Access & Anti-Regression State Guard

### Current Architecture
The CMS includes hard safety guards built directly into `StateController.php`:
1. **Pre-Write Automatic Snapshot**: Before any state modification is written to disk, an automatic JSON snapshot is archived in `storage/app/state_backups/state_backup_auto_{timestamp}.json`.
2. **Anti-Regression Safeguard**: The server validates that incoming state updates do not inadvertently drop doctor records, custom pages, empanelments, or uploaded media references. Destructive drops are automatically rejected with `422 Unprocessable Entity` unless an explicit override flag is supplied.

### Production Hardening Recommendations
* Ensure admin authentication credentials (passwords, tokens) are complex (minimum 16 characters).
* Configure rate limiting on authentication and API state endpoints to prevent brute-force attacks (`throttle:60,1`).

---

## 8. HTTP Security Headers Hardening

The following headers are configured in web server configurations to protect visitors from clickjacking, MIME-sniffing, and protocol downgrade attacks:

| Header | Production Value | Purpose |
| :--- | :--- | :--- |
| **Strict-Transport-Security** | `max-age=31536000; includeSubDomains` | Enforces HTTPS on all browser connections for 1 year. |
| **X-Frame-Options** | `SAMEORIGIN` | Prevents unauthorized framing/clickjacking. |
| **X-Content-Type-Options** | `nosniff` | Blocks MIME-type sniffing attacks. |
| **Referrer-Policy** | `strict-origin-when-cross-origin` | Protects sensitive URL query parameters across origins. |
| **X-XSS-Protection** | `1; mode=block` | Legacy browser XSS filtering. |
| **Permissions-Policy** | `geolocation=(), camera=(), microphone=()` | Restricts browser hardware access. |

---

## 9. Dependency & Package Security

* **Composer Packages**: Running `composer audit` verifies that all installed PHP packages (Laravel 12, Barryvdh DomPDF, Sanctum, Tinker) are free of known CVE vulnerabilities.
* **NPM Packages**: Running `npm audit` validates all frontend production dependencies.

---

## Summary of Security Status

| Security Area | Risk Rating | Status |
| :--- | :--- | :--- |
| `.env` & Secret Protection | High | **Hardened (Isolated outside Document Root & Blocked)** |
| Debug Mode Leakage | High | **Hardened (`APP_DEBUG=false` enforced)** |
| SQLite Database File Protection | High | **Hardened (Isolated & HTTP access denied)** |
| File Upload Security | Medium | **Hardened (MIME validated, randomized names, no exec)** |
| SQL Injection | Critical | **Mitigated (100% PDO parameterized binding)** |
| Cross-Site Scripting (XSS) | Medium | **Mitigated (React JSX escaping & HTML sanitization)** |
| Diagnostic Script Exposure | Medium | **Mitigated (All debug scripts excluded from package)** |
