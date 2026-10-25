# ShipFast (Laravel)

Global shipping & logistics platform — Laravel 12 + Blade + Alpine + Tailwind, MySQL.
This is the PHP rewrite of the original Node app, built to run on standard cPanel/
Namecheap shared hosting (PHP + MySQL, no Node process required in production).

## Current local version

The public website and operations portal have been redesigned around tracking, transparent estimates and shipment milestones. Implemented features include:

- Public home, service catalog, tracking timeline and live service comparison.
- Guided booking with addresses, parcel contents, cross-border goods details and signature requirements.
- Configurable USD tariff, validated dimensions, chargeable weight and business-day estimates.
- Guarded status transitions, atomic tracking updates, exception reasons and recipient delivery confirmation.
- Seven-day quotes with transaction-protected, idempotent booking conversion.
- Editable uncollected bookings, an internal printable waybill, and an attention queue.
- Customer, invoice, route, notification and super-admin management screens.
- PHPUnit feature coverage for pricing, booking, authorization and lifecycle rules.

Rates are ShipFast estimates, not carrier rates. Tracking is operator-recorded, not GPS or carrier-fed. The internal waybill is not a carrier label. Customs fields do not submit a declaration. Carrier integrations, pickup booking, holiday calendars and queued notification retries remain future work.

Local Windows preview uses PHP 8.3 and SQLite at http://127.0.0.1:8123. To rebuild when Windows blocks Vite helper processes:

~~~powershell
node node_modules/vite/bin/vite.js build --configLoader native --config vite.local.config.js
~~~

Run tests with PHP:

~~~sh
php vendor/phpunit/phpunit/phpunit
~~~

The tests use a separate in-memory SQLite database. Local operation lists are not prepopulated with demo shipments.

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
