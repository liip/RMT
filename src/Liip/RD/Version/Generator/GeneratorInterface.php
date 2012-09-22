<?php

namespace Liip\RD\Version\Generator;

interface GeneratorInterface
{
    public function __construct($context, $options = array());

    /**
     * Return the next version number, according to the current one and optional options
     * @param $currentVersion
     * @param array $options
     * @return string
     */
    public function generateNextVersion($currentVersion);

    public function getInformationRequests();

    public function getValidationRegex();
}

