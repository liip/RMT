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

class VcsTagPersister implements PersisterInterface
{
    protected $versionRegex;
    protected $vcs;
    protected $options;

    public function __construct($options = array())
    {
        $this->options = $options;
        $this->vcs = Context::get('vcs');
        $this->versionRegex = Context::get('version-generator')->getValidationRegex();
        if (isset($options['tag-pattern'])) {
            $this->versionRegex = $options['tag-pattern'];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentVersion()
    {
        $tags = $this->getValidVersionTags($this->versionRegex);
        if (count($tags) === 0) {
            throw new \Liip\RMT\Exception\NoReleaseFoundException('No VCS tag matching the regex [' . $this->getTagPrefix() . $this->versionRegex . ']');
        }

        // Extract versions from tags and sort them
        $versions = $this->getVersionFromTags($tags);
        usort($versions, array(Context::get('version-generator'), 'compareTwoVersions'));

        return array_pop($versions);
    }

    public function getNewVersion()
    {
        return $this->getTagFromVersion(Context::getParam('new-version'));
    }

    public function save()
    {
        $tagName = $this->getNewVersion();
        Context::get('output')->writeln("Creation of a new VCS tag [<yellow>$tagName</yellow>]");
        $this->vcs->createTag($tagName);
    }

    public function validateContext()
    {
        Context::get('vcs')->validateTag($this->getNewVersion());
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
        return $this->generatePrefix(isset($this->options['tag-prefix']) ? $this->options['tag-prefix'] : '');
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
     *
     * @param string $versionRegex
     *
     * @return array
     */
    public function getValidVersionTags($versionRegex)
    {
        $validator = new TagValidator($versionRegex, $this->getTagPrefix());

        return $validator->filtrateList($this->vcs->getTags());
    }

    protected function generatePrefix($userTag)
    {
        preg_match_all('/\{([^\}]*)\}/', $userTag, $placeHolders);
        foreach ($placeHolders[1] as $pos => $placeHolder) {
            if ($placeHolder == 'branch-name') {
                $replacement = $this->vcs->getCurrentBranch();
            } elseif ($placeHolder == 'date') {
                $replacement = date('Y-m-d');
            } else {
                throw new \Liip\RMT\Exception("There is no rules to process the prefix placeholder [$placeHolder]");
            }
            $userTag = str_replace($placeHolders[0][$pos], $replacement, $userTag);
        }

        return $userTag;
    }
}
