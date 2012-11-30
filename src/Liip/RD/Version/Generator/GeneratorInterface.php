<?php

namespace Liip\RD\Version\Generator;

interface GeneratorInterface
{
    public function __construct($options = array());

    /**
     * Return the next version number, according to the current one and optional options
     * @param $currentVersion
     * @param array $options
     * @return string
     */
    public function generateNextVersion($currentVersion);

    /**
     * Function used to compare two versions. Must return:
     *  * -1 if $a is older than $b
     *  * 0 if $a and $b are the same
     *  * 1 if $a is more recent than $b
     * @param $a
     * @param $b
     * @return integer
     */
    public function compareTwoVersions($a, $b);

    public function getInformationRequests();

    public function getValidationRegex();

    public function getInitialVersion();
}

