<?php

namespace Liip\RD\Version\Generator;


interface GeneratorInterface
{
    public function getNextVersion($currentVersion, $options=array());
}
