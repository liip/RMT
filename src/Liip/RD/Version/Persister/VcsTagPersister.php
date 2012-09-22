<?php

namespace Liip\RD\Version\Persister;

use Liip\RD\VCS\VCSInterface;
use Liip\RD\VCS\TagValidator;

class VcsTagPersister implements PersisterInterface
{
    protected $vcs;

    public function __construct($context, $options = array())
    {
        $this->vcs = $context->getService('vcs');
        $this->versionRegex = $context->getService('version-generator')->getValidationRegex();
        $this->options = $options;
    }

    public function getCurrentVersion()
    {
        $tags = $this->vcs->getValidVersionTags($this->versionRegex);
        sort($tags);
        return $this->vcs->getVersionFromTag(array_pop($tags));
    }

    public function getCurrentVersionTag()
    {
        return $this->vcs->getTagFromVersion($this->getCurrentVersion());
    }

    public function save($versionNumber)
    {
        $this->vcs->createTag($this->vcs->getTagFromVersion($versionNumber));
    }

    public function init()
    {
    }

    public function getInformationRequests()
    {
        return array();
    }
}
