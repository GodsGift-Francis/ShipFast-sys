# Hosted deployment

This is a Laravel PHP application. Use a fresh hosted database and serve only the public/ directory. Node is needed for the frontend build, not the hosted runtime.

## Package and verify

```sh
php vendor/phpunit/phpunit/phpunit
node --test --test-isolation=none tests/js/shipping-estimator.test.cjs
npm run build
node scripts/package-release.mjs /absolute/new/release-directory
```

On restricted Windows environments use the existing local Vite config:

```sh
node node_modules/vite/bin/vite.js build --configLoader native --config vite.local.config.js
```

The packager uses an explicit allowlist, includes built assets, and excludes local configuration, databases, user accounts, logs, sessions, Git metadata and development dependencies. It refuses to replace an existing staging directory.

## Hosting setup

1. Create a separate PHP hosting site and fresh MariaDB database. On Alwaysdata Free, check current personal-use eligibility, resource limits and the assigned site URL. Keep other hosted applications untouched.
2. Stage the package outside the current public document root. Choose the same supported PHP version for CLI and the website (PHP 8.3/8.4 recommended).
3. Run `composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction` in the staged release.
4. Create a private .env using .env.hosted.example. Use a fresh encryption key, production environment, debug disabled, the real HTTPS site URL, real database credentials and secure session cookies. Never copy local .env or local user records.
5. Alternatively, the CLI-only scripts/configure-hosted.php accepts setup JSON on stdin with url, db_host, db_name, db_user and db_password. It creates .env with restrictive permissions and a fresh encryption key, and refuses replacement. Never put credentials into shell arguments, chat or public setup scripts.
6. Run `php artisan migrate --force --no-interaction`. Do not run a development seeder in production.
7. Create the first administrator with `php artisan shipfast:provision-admin`, entering email/name and a privately chosen 16–72 character password at hidden prompts. The command never resets an existing super administrator. Its --stdin mode is intended only for a private verified-SSH setup form.
8. Remove the hosted one-time configuration helper after use. Run `php artisan optimize` on hosting.
9. Set the PHP site document root to `/home/ACCOUNT/shipfast/public`. Enable HTTPS and HTTP-to-HTTPS redirection. Do not serve the application root.
10. Check public pages, customer sign-in, operator sign-in at /admin/login, role redirects, secure cookies, and inaccessible /.env, /.git/config, /storage/logs/laravel.log and /scripts/configure-hosted.php. Exercise registration/review, profile decisions, quotes, booking, milestones and invoices with sample data.
11. Revoke temporary deployment access and record the verified release commit and URL. Keep local/hosted identities separate. Validate a backup restoration before broader use.

Customer entry: /login and /account. Operator entry: /admin/login. Settings: /settings. Super-admin review queue: /account-reviews. Admin sign-in is unlisted, not protected by URL secrecy or a device restriction.

Rates and tracking remain local/operator-driven. MAIL_MAILER=log does not send email, and SMS needs separately configured provider credentials. This package does not book carrier pickup, purchase labels or collect payment.

References: [Alwaysdata plans](https://www.alwaysdata.com/en/offers/), [PHP](https://help.alwaysdata.com/en/docs/web-hosting/languages/php/), [SSH](https://help.alwaysdata.com/en/docs/web-hosting/remote-access/ssh/), [Laravel deployment](https://laravel.com/docs/12.x/deployment).
