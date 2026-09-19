# VINYLWAVE

VINYLWAVE is a PHP/MySQL storefront for vinyl records, CDs, releases and artist merchandise. It includes a customer-facing catalog, session and persistent carts, checkout with stock reservations and shipping calculation, reviews, wishlists, and a role-protected admin dashboard.

This repository is a demo project. Payment processing is intentionally not included: completed checkout orders are marked as paid for demonstration purposes.

<img width="1912" height="1080" alt="{72E45D36-489F-46FB-988C-62B801BE2551}" src="https://github.com/user-attachments/assets/8ebb3fb0-5e36-425e-84f6-d57b1200fb3b" />

<img width="1906" height="1039" alt="{9AF86E06-6780-403A-A90F-C9FF017A2DBF}" src="https://github.com/user-attachments/assets/5bf4be88-ddbb-4b25-8f6d-b0d72709a4af" />

<img width="1911" height="1041" alt="{DDC84373-26B7-411E-AA30-7871EDC384F2}" src="https://github.com/user-attachments/assets/acf22e5b-f15d-4035-8856-dad7ed6c0726" />

## Requirements

- PHP 8.1 or newer
- MySQL 8 or MariaDB 10.4+
- Apache with `mod_rewrite` enabled, or another web server configured to route requests to `public/index.php`
- PHP extensions: `pdo_mysql`, `mbstring`, `fileinfo`, `gd`
- Optional: `ffmpeg` for generating video poster images
- Optional for email features: a configured PHP `mail()` transport or SMTP adapter

## Quick start on OpenServer

1. Put the repository in `C:\OSPanel\domains\vinylwave-project` (or another OpenServer domain directory).
2. Start Apache and MySQL.
3. Create a MySQL database, then import [`database/schema.sql`](database/schema.sql) using phpMyAdmin or Adminer.
4. Copy [`.env.example`](.env.example) to `.env` and set the database values. Do not commit `.env`.
5. If you want a new admin account during installation, set `INITIAL_ADMIN_EMAIL` and a strong `INITIAL_ADMIN_PASSWORD` in `.env`.
6. From the project directory, run the idempotent migration:

   ```powershell
   php database/feature-migration.php
   ```

7. Open `http://vinylwave/` or the hostname configured by OpenServer.

The migration is safe to run again. It adds missing columns, indexes, security tables, cart and reservation tables, shipping tables, and review moderation support. Migrations are intentionally CLI-only and are not available through the browser.

## Demo catalog

To load sample artists, vinyl, CDs and merchandise, review [`database/seed-popular-releases.sql`](database/seed-popular-releases.sql), then run it manually in the `vinylwave` database. The script is repeatable and matches records by unique slug.

The seed file uses remote Unsplash images for demonstration. Replace them with artwork and photography that you are licensed to use before publishing the store.

## Configuration

Important `.env` values:

```dotenv
APP_ENV=development
APP_DEBUG=true
APP_URL=http://vinylwave

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=vinylwave
DB_USER=root
DB_PASS=
DB_CHARSET=utf8mb4

MAIL_FROM=no-reply@example.com
```

For production, use `APP_ENV=production`, `APP_DEBUG=false`, HTTPS, a non-root database account, and a real mail transport. Never place passwords, API keys, or production credentials in tracked files.

## Main features

### Storefront

- Catalog filtering by category, artist, genre, color and search
- Vinyl sleeve/record animation and audio previews
- Product pages with tracklists, stock, reviews, video and limited-drop countdowns
- Session cart and persistent carts for signed-in users
- Guest cart and wishlist merge after login or registration
- Inventory reservations during checkout
- Shipping calculator with zones and pickup points
- Product comparison, recently viewed products, dark/light theme and PWA shell

### Accounts and security

- Customer, moderator and admin roles
- CSRF protection on state-changing requests
- Secure session cookies and session ID regeneration after authentication
- Password hashing with `password_hash()`
- Login rate limiting backed by MySQL
- Email verification and one-time password-reset links
- Ban/unban controls and admin impersonation with a return flow
- Production error handler that hides database details from visitors

### Admin dashboard

- Product and artist management
- Bulk product actions and stock updates
- Review moderation queue
- Bulk order status updates
- User roles, bans and impersonation
- Sales reports and CSV export
- Product CSV import/export
- Chart.js dashboard summaries
- Image optimization and optional MP4 poster generation

## Project structure

```text
config/       Environment, database, security and upload services
controllers/  Request handlers and application workflows
models/       Database access and domain logic
views/        PHP templates
public/       Web entry point and public assets
database/     Schema, migrations, seed data and local backup placeholder
tests/        Lightweight smoke checks
```

The application entry point is [`public/index.php`](public/index.php). The root [`index.php`](index.php) forwards to it for OpenServer setups whose document root is the repository directory.

## Tests and checks

Run the smoke test from the project directory:

```powershell
php tests/smoke.php
```

For a syntax check of every PHP file on OpenServer:

```powershell
Get-ChildItem -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }
```

## Production deployment checklist

- Point the web server document root to `public/` when possible.
- Enable HTTPS and set secure session cookies.
- Set `APP_DEBUG=false`.
- Use a dedicated least-privilege database user.
- Configure email delivery for verification and password reset.
- Replace demo audio, remote images and sample catalog data with licensed assets.
- Create a database backup before every migration.
- Keep `database/`, `config/`, `models/`, `controllers/`, `views/`, `tests/` and `.env` outside public web access. The bundled `.htaccess` also denies these paths for Apache deployments.

## License

The repository is marked MIT in `composer.json`. Third-party images, audio, fonts, logos and artist branding remain subject to their respective licenses and are not automatically covered by the repository license.
