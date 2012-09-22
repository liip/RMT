<?php

namespace Liip\RD\Version\Persister;

use Liip\RD\Context;

interface PersisterInterface
{
    public function __construct($context, $options = array());
    public function getCurrentVersion();
    public function save($versionNumber);
    public function getInformationRequests();

    // Use the very first time to init this persistence
    public function init();
}

