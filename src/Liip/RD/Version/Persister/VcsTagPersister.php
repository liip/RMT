<?php

namespace Liip\RD\Version\Persister;

use Liip\RD\VCS\VCSInterface;
use Liip\RD\Context;

class VcsTagPersister implements PersisterInterface
{
    protected $versionRegex;
    protected $vcs;
    protected $prefix;

    public function __construct($options = array())
    {
        $this->vcs = Context::get('vcs');
        $this->versionRegex = Context::get('version-generator')->getValidationRegex();
        $this->prefix = $this->generatePrefix(isset($options['tag-prefix']) ? $options['tag-prefix'] : '');
    }

    public function getCurrentVersion()
    {
        $tags = $this->getValidVersionTags($this->versionRegex);
        if (count($tags)===0){
            throw new \Liip\RD\Exception\NoReleaseFoundException(
                'No VCS tag matching the regex ['.$this->getTagPrefix().$this->versionRegex.']');
        }

        // Extract versions from tags and sort them
        $versions = $this->getVersionFromTags($tags);
        usort($versions, array(Context::get('version-generator'), 'compareTwoVersions'));

        return array_pop($versions);
    }

    public function save($versionNumber)
    {
        $tagName = $this->getTagFromVersion($versionNumber);
        Context::get('output')->writeln("Creation of a new VCS tag [<yellow>$tagName</yellow>]");
        $this->vcs->createTag($tagName);
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
        return $this->prefix;
    }

    public function getTagFromVersion($versionName)
    {
        return $this->getTagPrefix().$versionName;
    }

    public function getVersionFromTag($tagName)
    {
        return substr($tagName, strlen($this->getTagPrefix()));
    }

    public function getVersionFromTags($tags)
    {
        $versions = array();
        foreach ($tags as $tag) {
            $versions[] = $this->getVersionFromTag($tag);
        }
        return $versions;
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

    protected function generatePrefix($userTag){
        preg_match_all('/\{([^\}]*)\}/', $userTag, $placeHolders);
        foreach ($placeHolders[1] as $pos => $placeHolder){
            if ($placeHolder == 'branch-name'){
                $replacement = $this->vcs->getCurrentBranch();
            }
            else if ($placeHolder == 'date'){
                $replacement = date('Y-m-d');
            }
            else {
                throw new \Liip\RD\Exception("There is no rules to process the prefix placeholder [$placeHolder]");
            }
            $userTag = str_replace($placeHolders[0][$pos], $replacement, $userTag);
        }
        return $userTag;
    }


}
