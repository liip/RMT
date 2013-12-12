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

use Liip\RMT\Version\Persister\PersisterInterface;
use Liip\RMT\Context;
use Liip\RMT\Changelog\ChangelogManager;

class ChangelogPersister implements PersisterInterface
{
    protected $changelogManager;

    public function __construct($options = array())
    {
        // Define a default changelog name
        if (!array_key_exists('location', $options)) {
            $options['location'] = 'CHANGELOG';
        }

        // The changelog format is related to the version-generator
        $config = Context::getParam('config');
        preg_match('/([^\\\]+)Generator/', $config['version-generator']['class'], $match);
        $format = $match[1];

        // Create the changelog manager
        $this->changelogManager = new ChangelogManager(
            Context::getParam('project-root').'/' . $options['location'],
            $format
        );
    }

    public function getCurrentVersion()
    {
        return $this->changelogManager->getCurrentVersion();
    }

    public function save($versionNumber)
    {
        $comment = Context::get('information-collector')->getValueFor('comment');
        $type = Context::get('information-collector')->getValueFor('type', null);
        $this->changelogManager->update($versionNumber, $comment, array('type'=>$type));
    }

    public function getInformationRequests()
    {
        return array('comment');
    }

    public function init()
    {
        // TODO: Implement init() method.
    }
}

