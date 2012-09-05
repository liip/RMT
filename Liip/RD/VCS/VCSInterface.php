<?php

namespace Liip\RD\VCS;

interface VCSInterface
{
    /**
     * Return all tags of the project
     * @return array
     */
    public function getTags();

    /**
     * Create a new tag at the current position
     * @param $tagName
     */
    public function createTag($tagName);

    /**
     * Publish a new created tag
     * @param $tagName
     */
    public function publishTag($tagName);

    /**
     * Return the list of all modifications from the given tag until now
     * @param $tag
     * @return array
     */
    public function getAllModificationsSince($tag);

    /**
     * Save the local modifications (commit)
     * @param $commitMsg
     * @return mixed
     */
    public function saveWorkingCopy($commitMsg = '');

    /**
     * Publish local modification
     */
    public function publishChanges();

}
