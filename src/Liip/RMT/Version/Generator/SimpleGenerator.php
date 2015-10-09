<?php

/*
 * This file is part of the project RMT
 *
 * Copyright (c) 2013, Liip AG, http://www.liip.ch
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\RMT\Version\Generator;

class SimpleGenerator implements GeneratorInterface
{
    public function __construct($options = array())
    {
    }

    public function generateNextVersion($currentVersion)
    {
        return ++$currentVersion;
    }

    public function getInformationRequests()
    {
        return array();
    }

    public function getValidationRegex()
    {
        return '\d+';
    }

    public function getInitialVersion()
    {
        return '0';
    }

    public function compareTwoVersions($a, $b)
    {
        if ($a == $b) {
            return 0;
        }

        return $a < $b ? -1 : 1;
    }
}
