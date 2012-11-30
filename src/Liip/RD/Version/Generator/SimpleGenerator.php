<?php

namespace Liip\RD\Version\Generator;

class SimpleGenerator implements GeneratorInterface
{
    public function __construct($options = array()){

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
}

