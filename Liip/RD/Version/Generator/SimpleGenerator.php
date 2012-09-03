<?php

namespace Liip\RD\Version\Generator;

class SimpleGenerator implements GeneratorInterface
{

    public function getNextVersion($currentVersion, $options = array())
    {
        return ++$currentVersion;
    }
}
