# PlayFlowPOS

PlayFlowPOS is a Laravel-based spa POS mockup project.

The current phase uses mocked data from `App\Services\MockDataService` to validate screens and flow before wiring real MySQL tables.

## Current stack

- Backend: Laravel 6 (project baseline)
- Runtime in this workspace: PHP 8.5
- Frontend: Bootstrap CDN + Blade views
- Database target: MySQL (for local and Hostinger)

## Local setup

1. Install PHP dependencies:

```bash
composer install
```

If `composer` is not in PATH, run `php C:\Users\<your-user>\AppData\Roaming\Composer\latest.phar install`.

2. Prepare environment:

```bash
cp .env.example .env
php artisan key:generate
```

3. Configure `.env` for MySQL.

4. Run migrations (when real tables are ready):

```bash
php artisan migrate
```

5. Start local server:

```bash
php artisan serve
```

or:

```bash
php artisan.php
```

Frontend note: no `npm install` is required for the current mock UI.

## Routes

- `/` dashboard
- `/pos`
- `/booking`
- `/staff`

## Axios usage

`axios` is removed because the current UI does not call backend APIs via JavaScript.

If you add AJAX features later, re-add axios (or use `fetch`) only where needed.

## Hostinger deploy checklist (hPanel)

1. Upload project files.
2. Set document root to `public`.
3. Create MySQL database/user in hPanel.
4. Update `.env` with production DB credentials and `APP_ENV=production`.
5. Run:

```bash
php artisan key:generate --force
php artisan config:cache
php artisan route:cache
```

6. Ensure writable permissions for `storage/` and `bootstrap/cache/`.

## Version upgrade note

To move to the latest Laravel major version, upgrade PHP first.

This workspace now uses PHP 8.5. Laravel 6 works with a temporary compatibility guard for deprecation handling, but production should move to a newer Laravel major version.
The compatibility patch is auto-applied after Composer autoload via `scripts/apply_php85_compat.php`.

See `UPGRADE_PLAN.md` for a phased migration approach.
