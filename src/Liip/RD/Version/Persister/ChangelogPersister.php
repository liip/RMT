<?php

namespace Liip\RD\Version\Persister;

use Liip\RD\Version\Persister\PersisterInterface;
use Liip\RD\Context;
use Liip\RD\Changelog\ChangelogManager;

class ChangelogPersister implements PersisterInterface
{
    protected $context;
    protected $changelogManager;

    public function __construct($context, $options = array())
    {
        $this->context = $context;

        // Define a default changelog name
        if (!array_key_exists('location', $options)) {
            $options['location'] = 'CHANGELOG';
        }

        // The changelog format is related to the version-generator
        $config = $this->context->getParam('config');
        preg_match('/([^\\\]+)Generator/', $config['version-generator']['class'], $match);
        $format = $match[1];

        // Create the changlelog manager
        $this->changelogManager = new ChangelogManager(
            $context->getParam('project-root').'/' . $options['location'],
            $format
        );
    }

    public function getCurrentVersion()
    {
        return $this->changelogManager->getCurrentVersion();
    }

    public function save($versionNumber)
    {
        $comment = $this->context->getService('information-collector')->getValueFor('comment');
        $type = $this->context->getService('information-collector')->getValueFor('type', null);
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

