# Production Go-Live Checklist - Madhav Netralaya Eye Hospital

Use this comprehensive checklist before, during, and after deploying to production to ensure zero downtime, robust security, and full functionality.

---

## 📋 Pre-Flight & Configuration

### 1. Application & Environment
- [ ] `APP_ENV` is set to `production`.
- [ ] `APP_DEBUG` is set to `false`.
- [ ] `APP_KEY` is uniquely generated via `php artisan key:generate`.
- [ ] `APP_URL` matches the canonical production HTTPS domain (`https://www.madhavnetralaya.org`).
- [ ] `APP_TIMEZONE` is set to `Asia/Kolkata` (or appropriate regional zone).
- [ ] Error display is turned off in `php.ini` (`display_errors = Off`, `log_errors = On`).
- [ ] Composer production packages installed with `--no-dev --optimize-autoloader`.

---

## 💾 Database & State Persistence

### 2. Database Integrity
- [ ] Safe copy of production `database.db` transferred with matched SHA-256 checksum.
- [ ] Destructive database reset commands (`migrate:fresh`, `db:wipe`, `truncate`) are strictly prohibited.
- [ ] SQLite database file permissions set to `664` (or `660`) owned by `www-data`.
- [ ] Parent folder writable by web server for SQLite `-wal` and `-shm` creation.
- [ ] SQLite WAL mode enabled: `PRAGMA journal_mode=WAL;`.
- [ ] SQLite integrity check passes: `PRAGMA integrity_check;` returns `ok`.
- [ ] Automated state backup directory exists and is writable: `storage/app/state_backups/`.

---

## 🖥️ CMS & Content Management

### 3. CMS State & Administrative Controls
- [ ] GET `/api/state` returns complete JSON state containing all pages, departments, doctors, and facilities.
- [ ] Admin panel loads cleanly at `/admin` without console errors.
- [ ] Admin authentication and session persistence verified.
- [ ] State anti-regression validation guards active in `StateController.php` (blocks accidental data loss).
- [ ] Live preview and dynamic page builder functionality operational.
- [ ] Doctor availability scheduling updates save correctly to SQLite.
- [ ] Visual navigation CMS configurations render properly on frontend.

---

## 🖼️ Media & Public Assets

### 4. Media Storage & Delivery
- [ ] Storage symlink created via `php artisan storage:link` (or mapped directly in shared hosting).
- [ ] `storage/app/public/cms/` contains all subfolders:
  - [ ] `cms/doctors/` (doctor profile photos)
  - [ ] `cms/homepage/` (hero sliders and hospital infrastructure)
  - [ ] `cms/general/` (department banners and facility pictures)
  - [ ] `cms/career/` (submitted resumes and CVs)
  - [ ] `cms/settings/` (hospital logo, favicon, NABH badges)
- [ ] Direct URL testing of media assets (e.g. `https://domain.com/storage/cms/settings/...`) returns `200 OK`.
- [ ] PHP upload limit configured to at least `50M` (`upload_max_filesize = 50M`, `post_max_size = 50M`).
- [ ] Web server client max body size set to `50M` (`client_max_body_size 50M;`).

---

## 📝 Forms & Lead Capture

### 5. Interactive Form Submissions
- [ ] **Book Appointment Form**: Submits successfully and persists to database.
- [ ] **General Enquiry Form**: Validates inputs, captures inquiries, and logs to database.
- [ ] **Eye Consultation Popup Modal**: Opens correctly, submits lead data, and saves to `eye_consultation_enquiries`.
- [ ] **Patient Feedback Form**: Submits ratings/comments and persists to `patient_feedbacks`.
- [ ] **Career Job Application**: Allows PDF/DOC resume attachment upload and stores file securely.
- [ ] **Newsletter Subscription**: Validates email and adds to subscribers list.
- [ ] Success/Error feedback toasts display accurately on frontend.

---

## ✉️ Email Delivery & Notifications

### 6. Email (SMTP) Configuration
- [ ] Production SMTP credentials verified in `.env` (`MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION`).
- [ ] Sender address set to an authorized domain email (e.g., `info@madhavnetralaya.org` or `no-reply@madhavnetralaya.org`).
- [ ] SPF record verified on domain DNS.
- [ ] DKIM signing configured and active on DNS.
- [ ] DMARC record published on domain DNS.
- [ ] Test notification email successfully delivered to inbox (not spam/junk folder).

---

## 📄 PDF Certificate Generation

### 7. PDF Generation (DomPDF)
- [ ] DomPDF / Barryvdh extension installed and functional in `vendor/`.
- [ ] Eye donation certificate generation endpoint renders valid PDF with hospital logo and pledge details.
- [ ] Server memory limit (`memory_limit = 256M`) sufficient for concurrent PDF rendering.
- [ ] Standard web fonts / typography render correctly inside generated PDFs.

---

## 🔌 APIs & Integrations

### 8. External & Internal APIs
- [ ] REST API routes (`/api/*`) return valid JSON with appropriate HTTP status codes (200, 201, 400, 422, 500).
- [ ] Gemini AI Assistant API key configured and clinical query endpoint verified.
- [ ] Instagram Graph API access token tested (if social feed active).
- [ ] Razorpay API keys (`VITE_RAZORPAY_KEY_ID`, `RAZORPAY_KEY_SECRET`) configured for tender/EMD processing.
- [ ] API error handling returns sanitized JSON messages without leaking SQL syntax or stack traces.

---

## 📱 Mobile & Responsive Experience

### 9. Mobile & Touch Screen Usability
- [ ] Viewport meta tag active (`<meta name="viewport" content="width=device-width, initial-scale=1.0">`).
- [ ] Mobile navigation drawer opens, scrolls, and collapses seamlessly on touch devices.
- [ ] Emergency helpline and click-to-call buttons function properly on mobile devices.
- [ ] Forms, modal popups, and datepickers render cleanly on screens from 360px width upwards.
- [ ] No horizontal scrolling or content overflow on iOS and Android viewports.

---

## 🌐 Browser Compatibility

### 10. Cross-Browser & Device Testing
- [ ] **Google Chrome** (Desktop & Mobile) - All layouts, animations, and modals tested.
- [ ] **Mozilla Firefox** (Desktop & Mobile) - CSS Grid and Flexbox alignment verified.
- [ ] **Apple Safari** (macOS & iOS) - WebKit rendering, font smoothing, and sticky headers verified.
- [ ] **Microsoft Edge** - Full functionality confirmed.

---

## 🔒 Security & SSL

### 11. Security Hardening
- [ ] SSL certificate active and valid with 0 mixed-content warnings.
- [ ] HTTP automatically redirects to HTTPS (301 Permanent Redirect).
- [ ] Direct access to `.env` file blocked with `403 Forbidden` / `404 Not Found`.
- [ ] Direct access to `.db`, `.sqlite`, and `.git` blocked by web server.
- [ ] HTTP Security Headers configured:
  - [ ] `Strict-Transport-Security: max-age=31536000; includeSubDomains`
  - [ ] `X-Frame-Options: SAMEORIGIN`
  - [ ] `X-Content-Type-Options: nosniff`
  - [ ] `Referrer-Policy: strict-origin-when-cross-origin`
- [ ] File upload extensions restricted to safe whitelist (`jpg`, `jpeg`, `png`, `webp`, `gif`, `pdf`, `doc`, `docx`).
- [ ] PHP execution disabled inside upload directories (`storage/app/public/cms`).

---

## 🌐 Domain & DNS

### 12. DNS Propagation & Routing
- [ ] Primary `@` A record points to production server IP.
- [ ] `www` CNAME / A record points to canonical server address.
- [ ] Non-www to www (or vice versa) canonical redirection active.
- [ ] TTL lowered prior to switchover (300s) and restored to normal (3600s/86400s) post-launch.

---

## ⚡ Performance Optimization

### 13. Production Caching & Speed
- [ ] `php artisan config:cache` executed.
- [ ] `php artisan route:cache` executed.
- [ ] `php artisan view:cache` executed.
- [ ] PHP OPcache enabled and verified in `php.ini`.
- [ ] Gzip / Brotli compression enabled in web server for CSS, JS, JSON, and SVG.
- [ ] Long-term browser caching headers set for static assets (`Cache-Control: max-age=31536000`).

---

## 🧪 Final User Acceptance Testing (UAT)

### 14. Stakeholder Sign-Off
- [ ] Hospital administration contact information, addresses, and emergency numbers verified.
- [ ] Doctor rosters, qualifications, and department timings verified.
- [ ] NABH and ISO accreditation badges confirmed.
- [ ] Privacy Policy, Terms & Conditions, and Disclaimer pages reviewed.
- [ ] Post-launch backup snapshot taken immediately after go-live.
