# Upgrade Plan to Current Laravel

This project currently targets Laravel 6 and now runs on PHP 8.5 in local dev.

To upgrade to the latest Laravel major safely, use this sequence:

1. Stabilize runtime baseline
- Keep local/hosting at PHP 8.2+ (current local is 8.5)
- If legacy package issues appear, test with PHP 8.2 for smoother transition

2. Create a backup branch / snapshot
- Keep a rollback point before framework upgrade

3. Upgrade framework in controlled steps
- Laravel 6 -> 8 -> 10 -> 12 (recommended path for legacy codebases)
- Run test suite after each jump

4. Replace deprecated framework internals
- Middleware class names / kernel defaults
- Exception handler and bootstrap updates
- Asset pipeline migration (Mix -> Vite) when frontend bundling is needed

5. Re-verify deployment
- `php artisan optimize`
- `php artisan config:cache`
- `php artisan route:cache`
- Ensure `storage/` and `bootstrap/cache/` are writable

6. Cutover checklist
- `APP_DEBUG=false`
- Production `.env` secrets set
- DB migrations validated on staging first

Notes:
- This repository is now aligned back to Laravel request lifecycle (`public/index.php`).
- Mock data flow remains intact, so UI development can continue during upgrade preparation.
