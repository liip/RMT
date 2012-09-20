<?php

namespace Liip\RD\VCS;

abstract class BaseVCS implements \Liip\RD\VCS\VCSInterface
{
    protected $options;

    public function __construct($context = null, $options = array())
    {
        $this->options = $options;
    }

    public function getTagPrefix()
    {
        return isset($this->options['tag-prefix']) ? $this->options['tag-prefix'] : '';
    }

    public function getTagFromVersion($versionName)
    {
        return $this->getTagPrefix().$versionName;
    }

    public function getVersionFromTag($tagName)
    {
        return substr($tagName, strlen($this->getTagPrefix()));
    }

    /**
     * Return all tags matching the versionRegex and prefix
     * @param $versionRegex
     */
    public function getValidVersionTags($versionRegex)
    {
        $validator = new TagValidator($versionRegex, $this->getTagPrefix());
        return $validator->filtrateList($this->getTags());
    }

}
