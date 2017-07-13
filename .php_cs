<?php

/*
 * This file is part of the project RMT
 *
 * Copyright (c) 2017, Liip AG, http://www.liip.ch
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$finder = PhpCsFixer\Finder::create()
    ->in(realpath(getcwd() . '/src'));

return PhpCsFixer\Config::create()
    ->setUsingCache(false)
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR2' => true
    ])
    ->setFinder($finder);