<?php

namespace Liip\RD\Version\Generator;

class SimpleGenerator implements GeneratorInterface
{
    public function generateNextVersion($currentVersion, $options = array())
    {
        return ++$currentVersion;
    }

    public function registerUserQuestions()
    {
        return array();
    }

    public function getValidationRegex()
    {
        return '\d+';
    }
}

