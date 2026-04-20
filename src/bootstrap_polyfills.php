<?php

// Manual polyfill loading for environments without intl/mbstring extensions
// This file is automatically loaded by Composer via the "files" autoloading section.

$projectDir = dirname(__DIR__);

foreach ([
    '/vendor/symfony/polyfill-intl-normalizer/bootstrap.php',
    '/vendor/symfony/polyfill-intl-grapheme/bootstrap.php',
    '/vendor/symfony/polyfill-intl-icu/bootstrap.php',
    '/vendor/symfony/polyfill-mbstring/bootstrap.php',
] as $file) {
    if (file_exists($projectDir.$file)) {
        require_once $projectDir.$file;
    }
}
