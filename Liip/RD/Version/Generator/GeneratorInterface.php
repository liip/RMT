<?php

namespace Liip\RD\Version\Generator;


interface GeneratorInterface
{
    /**
     * Return the next version number, according to the current one and optional options
     * @param $currentVersion
     * @param array $options
     * @return string
     */
    public function getNextVersion($currentVersion, $options=array());

    public function getUserQuestions();
}
