<?php

namespace Liip\RMT\Version\Generator;

class SimpleGenerator implements GeneratorInterface
{
    public function __construct($options = array())
    {
    }

    public function generateNextVersion($currentVersion, $options = array())
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
        return $a < $b ? -1 : 1 ;
    }
}

