<?php

namespace Liip\RD\Version\Persister;

use Liip\RD\VCS\VCSInterface;

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
        $tags = $this->getValidVersionTags($this->versionRegex);
        if (count($tags)===0){
            throw new \Liip\RD\Exception\NoReleaseFoundException('No VCS tag matching the regex ['.$this->versionRegex.']');
        }
        sort($tags);
        return $this->getVersionFromTag(array_pop($tags));
    }

    public function save($versionNumber)
    {
        $this->vcs->createTag($this->getTagFromVersion($versionNumber));
    }

    public function init()
    {
    }

    public function getInformationRequests()
    {
        return array();
    }

    public function getTagPrefix()
    {
        $prefix = isset($this->options['tag-prefix']) ? $this->options['tag-prefix'] : '';
        return $prefix;
    }

    public function getTagFromVersion($versionName)
    {
        return $this->getTagPrefix().$versionName;
    }

    public function getVersionFromTag($tagName)
    {
        return substr($tagName, strlen($this->getTagPrefix()));
    }

    public function getCurrentVersionTag()
    {
        return $this->getTagFromVersion($this->getCurrentVersion());
    }

    /**
     * Return all tags matching the versionRegex and prefix
     * @param $versionRegex
     */
    public function getValidVersionTags($versionRegex)
    {
        $validator = new TagValidator($versionRegex, $this->getTagPrefix());
        return $validator->filtrateList($this->vcs->getTags());
    }


}
