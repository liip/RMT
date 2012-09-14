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
        $validator = new TagValidator($this->versionRegex, $this->getPrefix());
        $tags = $validator->filtrateList($this->vcs->getTags());
        sort($tags);
        $lastTag = array_pop($tags);
        return substr($lastTag, strlen($this->getPrefix()));
    }

    public function getCurrentVersionTag()
    {
        return $this->getTagForVersion($this->getCurrentVersion());
    }

    public function getTagForVersion($version)
    {
        return $this->getPrefix().$version;
    }

    public function save($versionNumber)
    {
        $this->vcs->createTag($this->getTagForVersion($versionNumber));
    }

    /**
     * Filtrate the provided list and remove tags that don't match the prefix and the regex
     * @param $tags
     * @param $prefix
     * @param $versionRegex
     */
    public function filterTags($tags, $prefix, $versionRegex)
    {
        $validTags = array();
        foreach ($tags as $tag){
            if (strpos($tag,$prefix) !==0){
                continue;
            }
            if (!preg_match($versionRegex, substr($tag, strlen($prefix)))){
                continue;
            }
            $validTags[] = $tag;
        }

        return $validTags;
    }

    public function registerUserQuestions()
    {
        // TODO: Implement registerUserQuestions() method.
    }

    protected function getPrefix()
    {
        return isset($this->options['prefix']) ? $this->options['prefix'] : '';
    }

    public function init()
    {
        // TODO: Implement init() method.
    }
}
