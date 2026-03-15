<?php
// Helper script to run Laravel on a custom port with one command.
$vendorAutoload = __DIR__ . '/vendor/autoload.php';

if (!file_exists($vendorAutoload)) {
    fwrite(STDERR, "Missing dependencies: run `composer install` first.\n");
    exit(1);
}

$port = getenv('APP_PORT') ?: 8080;
$host = '127.0.0.1';

echo "Starting PlayFlow POS on http://{$host}:{$port}\n";
passthru("php artisan serve --host={$host} --port={$port}");
