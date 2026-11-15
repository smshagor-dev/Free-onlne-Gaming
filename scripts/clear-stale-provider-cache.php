<?php

declare(strict_types=1);

// Composer may remove require-dev packages (such as laravel/pail) before
// Laravel's cached provider manifests have been rebuilt. Clear both manifests
// before any Artisan command boots the application.
$cacheDirectory = dirname(__DIR__).'/bootstrap/cache';

foreach (['packages.php', 'services.php'] as $manifest) {
    $path = $cacheDirectory.'/'.$manifest;

    if (is_file($path) && ! @unlink($path)) {
        fwrite(STDERR, "Unable to remove stale Laravel provider manifest: {$path}\n");
        exit(1);
    }
}
