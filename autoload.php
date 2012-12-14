<?php

// Search for an autoloader
if (file_exists($file = __DIR__.'/../../autoload.php')) {

    // Composer standarmt location
    $loader = require_once $file;
    $loader->add('Liip\RMT\Tests', __DIR__.'/test');
    $loader->add('Liip', __DIR__.'/src');
}
elseif ( file_exists($file = __DIR__.'/vendor/autoload.php')) {

    // Composer when on RMT standalone install (used in travis.ci)
    $loader = require_once $file;
    $loader->add('Liip\RMT\Tests', __DIR__.'/test');
    $loader->add('Liip', __DIR__.'/src');
}
elseif ( file_exists( $file = __DIR__.'/../symfony/src/Symfony/Component/ClassLoader/UniversalClassLoader.php')) {

    // Symfony 2.0
    require_once $file;
    $loader = new \Symfony\Component\ClassLoader\UniversalClassLoader();
    $loader->registerNamespaces(array(
        'Liip' => array(__DIR__.'/src', __DIR__.'/test'),
        'Symfony' => __DIR__.'/../symfony/src',
    ));
    $loader->register();
}
else {

    throw new \Exception("Unable to find the an autoloader");
}