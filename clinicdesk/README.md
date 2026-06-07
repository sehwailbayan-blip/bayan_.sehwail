# ClinicDesk

A private, login-protected clinic management dashboard built with PHP (no framework), MySQL, and AdminLTE 3.

## Setup

1. Import `clinicdesk_db.sql` into MySQL.
2. Edit `config/database.php` with your DB credentials.
3. Download AdminLTE 3 from https://adminlte.io and extract into `public/assets/adminlte/`.
4. Point your web server document root to the `clinicdesk/` folder, or place it under `htdocs/`.
5. Navigate to `http://localhost/clinicdesk/` — you will be redirected to the login page.

## Default Admin Account

- **Email:** admin@clinic.local
- **Password:** password

> Change this password immediately after first login.

## Features

- Role-based access: Admin, Doctor, Patient
- Session-based authentication with CSRF protection
- Appointment booking with conflict detection
- Prescription management with secure PDF upload/download
- Paginated lists with dynamic filtering
- Admin reports with CSV export
- AdminLTE 3 dashboard UI

## Security

- All passwords hashed with `password_hash(PASSWORD_BCRYPT)`
- All queries use prepared statements — zero raw SQL interpolation
- CSRF token on every POST form
- XSS protection via `htmlspecialchars()` on all output
- Prescription files blocked from direct URL access via `.htaccess`
- File uploads validated with `getimagesize()` / `finfo`
