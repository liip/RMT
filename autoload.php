<?php

// Project context autoloader
if (file_exists(__DIR__.'/../../autoload.php')) {
    $loader = require_once __DIR__.'/../../autoload.php';
}
// Standalone autoloader
elseif ( file_exists( __DIR__.'/vendor/autoload.php')) {
    $loader = require_once __DIR__.'/vendor/autoload.php';
}
else {
    throw new Exception("Unable to find the composer autoloader");
}

// and manually add the Liip namespace, until we move the project on packagist
$loader->add('Liip\RD\Tests', __DIR__.'/test');
$loader->add('Liip', __DIR__.'/src');
