# VINYLWAVE

Modern PHP/MySQL vinyl & official merch shop for OpenServer.

## Run on OpenServer
1. Copy the `vinylwave-project` folder into your OpenServer `domains` directory.
2. Start Apache and MySQL.
3. Open phpMyAdmin/Adminer and import `database/schema.sql` (fresh installs).
4. Copy `.env.example` to `.env` and set database credentials. For a new administrator, set `INITIAL_ADMIN_EMAIL` and a strong `INITIAL_ADMIN_PASSWORD` (12+ characters).
5. **Run the feature migration from CLI only**: `php database/feature-migration.php`.
6. Open `http://vinylwave/` (or the domain configured by OpenServer).

> Upgrading an existing install? Just run step 4 — the migration is idempotent
> and safe to run multiple times. Existing reviews stay published; new reviews
> land in the moderation queue.

## Production checklist

- Set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` and `MAIL_FROM` in `.env`.
- Serve the site over HTTPS and point the web server DocumentRoot to `public/`.
- Keep `.env`, `database/`, `config/`, `models/`, `controllers/` and `views/` outside web access; the bundled `.htaccess` also denies them.
- Configure PHP mail or SMTP transport for email verification and password recovery.
- Run `php tests/smoke.php` and `php database/feature-migration.php` during deployment.

## Included

### Shop
- Pure PHP with lightweight MVC-style controllers, PDO/MySQL
- Product catalog with category/artist/genre/color filtering
- Vinyl sleeve → record hover animation
- 15-second preview player + sticky footer player
- WaveSurfer.js waveform on the product page
- Product pages, tracklist, stock, sizes and limited-drop countdown
- Session cart + persistent user carts, `TRAVIS10` promo code
- Guest checkout with **cart merge on login** and **guest wishlist merge**
- Inventory reservations (stock held for 15 minutes during checkout)
- Shipping calculator by city/region with zones and pickup points

### New features
- **Wishlist / Favorites** — guests via localStorage, registered via DB,
  automatic merge after login/register; page at `index.php?page=wishlist`
- **Product comparison** — side-by-side spec table (up to 4 products),
  `index.php?page=compare`
- **Recently viewed products** — localStorage strip above the catalog
- **Infinite scroll / “Load more”** — paginated catalog with a JSON endpoint
- **Dark/light theme toggle** — persisted in localStorage, applied before paint
- **PWA support** — `manifest.json`, service worker (offline catalog shell),
  generated app icons
- **Review moderation queue** — new reviews are `pending`; approve/reject
  one-by-one or in bulk in the admin panel
- **User management** — roles (customer/moderator/admin), ban/unban with
  reason, impersonation (“Войти” with a return banner)
- **Sales reports** — date range, category and artist filters, revenue/units/
  orders summary, top products, CSV download
- **Bulk actions** — multi-select products: delete, hide/show, set stock;
  bulk order status updates
- **Product import/export** — CSV export of the catalog, CSV import with
  artist auto-creation and update-or-insert semantics
- **Dashboard charts (Chart.js)** — revenue & orders over 30 days, revenue by
  category (doughnut), top products (horizontal bars)
- **Image optimization** — uploads auto-converted to WebP with generated
  thumbnails (GD)
- **Video upload + thumbnails** — MP4 upload for products, poster frame
  generated via ffmpeg when available (set `FFMPEG_PATH` if not in PATH)
- **Drag-and-drop track reordering** in the product edit form

## Admin panel tabs
Обзор (charts) · Товары (bulk) · + Новый товар · Артисты · Отзывы ·
Модерация (queue) · Заказы (bulk status) · Пользователи (roles/ban/impersonate) ·
Отчеты (sales) · Импорт/Экспорт (CSV)

## Notes
- Audio previews use public SoundHelix demo MP3s. Replace `preview_url` values in the database with your licensed 15-second samples before production.
- Images use remote Unsplash URLs; replace them with your own licensed artwork for production.
- ffmpeg is optional: without it, MP4 uploads work but no poster frame is generated.
- Review statuses: existing reviews were migrated to `approved`; all new reviews require moderation.
