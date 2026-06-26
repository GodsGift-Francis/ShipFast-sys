# ShipFast (Laravel)

Global shipping & logistics platform — Laravel 12 + Blade + Alpine + Tailwind, MySQL.
This is the PHP rewrite of the original Node app, built to run on standard cPanel/
Namecheap shared hosting (PHP + MySQL, no Node process required in production).

## Sprint status

- **Sprint 0 (this drop) — Foundation:** project skeleton, MySQL config, full database
  schema (migrations) + Eloquent models, role middleware, the design system
  (maritime/industrial tokens, fonts, effects), base public layout + home hero, and
  the super-admin seeder.
- **Next:** S1 public pages (Services/Track/Quote), S2 portal shell + dashboard,
  S3 shipments + rate engine, S4 customers/invoices/routes, S5 notifications,
  S6 super-admin panel, S7 polish + deploy.

## Requirements

- PHP 8.2+ with extensions: pdo_mysql, mbstring, openssl, tokenizer, xml, ctype, json, bcmath, fileinfo, curl
- Composer
- MySQL 8 / MariaDB 10.4+
- Node 18+ (only for building CSS/JS assets — not needed at runtime in production)

## Local setup

```bash
composer install
cp .env.example .env
php artisan key:generate

# point .env at your MySQL database, then:
php artisan migrate --seed      # creates tables + seeds the super admin

npm install
npm run build                   # or: npm run dev  (hot reload while developing)

php artisan serve               # http://localhost:8000
```

Default super admin (change in `.env` before seeding for production):
`admin@shipfast.local` / `admin12345`

## Deploying to Namecheap (cPanel shared hosting)

You build assets locally, then upload. The web root must point at `/public`.

1. **Build locally:** `composer install --no-dev --optimize-autoloader` and `npm run build`.
2. **Database:** in cPanel → MySQL Databases, create a database + user, grant all
   privileges. Note the DB name/user (cPanel prefixes them, e.g. `cpuser_shipfast`).
3. **Upload:** zip the project (excluding `node_modules`), upload via File Manager to
   a folder **outside** `public_html` (e.g. `~/shipfast`), and extract. Then either:
   - point your domain's document root to `~/shipfast/public` (Domains → set document root), **or**
   - move the contents of `public/` into `public_html/` and edit the two paths in
     `public_html/index.php` to point at `~/shipfast/vendor/autoload.php` and
     `~/shipfast/bootstrap/app.php`.
4. **Configure:** copy `.env.example` to `.env`, set `APP_ENV=production`,
   `APP_DEBUG=false`, `APP_URL=https://yourdomain`, the cPanel DB credentials, and SMTP.
5. **Via SSH (cPanel Terminal):**
   ```bash
   cd ~/shipfast
   php artisan key:generate
   php artisan migrate --seed --force
   php artisan storage:link
   php artisan config:cache && php artisan route:cache && php artisan view:cache
   ```
6. **Queue worker (for email/SMS notifications):** add a cron job in cPanel:
   ```
   * * * * * cd ~/shipfast && php artisan schedule:run >> /dev/null 2>&1
   ```
   (A `queue:work` runner is wired up in the notifications sprint.)

## Notes

- Sessions, cache, and the queue all use the **database** driver — no Redis needed.
- SMS is optional: without `TWILIO_*` set, SMS notifications are logged instead of sent.
- The original app's algorithms (rate engine, status-transition notifications, role
  gating, super-admin controls) are ported 1:1 in the coming sprints.
