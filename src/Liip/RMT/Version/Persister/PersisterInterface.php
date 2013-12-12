<?php
/*
 * This file is part of the project RMT
 *
 * Copyright (c) 2013, Liip AG, http://www.liip.ch
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\RMT\Version\Persister;

use Liip\RMT\Context;
use Liip\RMT\Exception\NoReleaseFoundException;

interface PersisterInterface
{
    public function __construct($options = array());

    /**
     * Return the current release name
     *
     * @return mixed The current release number
     * @throws NoReleaseFoundException
     * */
    public function getCurrentVersion();

    public function save($versionNumber);

    public function getInformationRequests();

    // Use the very first time to init this persistence
    public function init();
}

