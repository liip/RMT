<?php

// Autoloading
$loader = require_once __DIR__.'/../../autoload.php';
$loader->add('Liip', __DIR__);

// Test a command
use Liip\RD\Application;
$application = new Application();
$application->run();