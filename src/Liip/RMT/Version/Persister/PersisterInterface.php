<?php

namespace Liip\RMT\Version\Persister;

use Liip\RMT\Context;

interface PersisterInterface
{
    public function __construct($options = array());
    public function getCurrentVersion();
    public function save($versionNumber);
    public function getInformationRequests();

    // Use the very first time to init this persistence
    public function init();
}

