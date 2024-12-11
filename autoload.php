<?php
/*
 * This file is part of the project RMT
 *
 * Copyright (c) 2013, Liip AG, http://www.liip.ch
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


// Search for an autoloader

// in phar environment
if (extension_loaded('phar') && method_exists('Phar', 'running') && file_exists($file = Phar::running() . '/vendor/autoload.php')) {
    $loader = require_once $file;
} elseif (file_exists($file = __DIR__ . '/../../autoload.php')) {

    // Composer standard location
    $loader = require_once $file;
    $loader->add('Liip\RMT\Tests', __DIR__ . '/test');
    $loader->add('Liip', __DIR__ . '/src');
} elseif (file_exists($file = __DIR__ . '/vendor/autoload.php')) {

    // Composer when on RMT standalone install (used in travis.ci)
    $loader = require $file;
    $loader->add('Liip\RMT\Tests', __DIR__.'/test');
    $loader->add('Liip', __DIR__.'/src');
} elseif (file_exists($file = __DIR__ . '/../symfony/src/Symfony/Component/ClassLoader/UniversalClassLoader.php')) {

    // Symfony 2.0
    require_once $file;
    $loader = new \Symfony\Component\ClassLoader\UniversalClassLoader();
    $loader->registerNamespaces(array(
        'Liip' => array(__DIR__ . '/src', __DIR__ . '/test'),
        'Symfony' => __DIR__ . '/../symfony/src',
    ));
    $loader->register();
} else {
    throw new \Exception("Unable to find an autoloader");
}
