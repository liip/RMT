<?php

/*
 * This file is part of the project RMT
 *
 * Copyright (c) 2013, Liip AG, http://www.liip.ch
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\RMT\VCS;

interface VCSInterface
{
    /**
     * Return the current branch
     */
    public function getCurrentBranch();

    /**
     * Return all tags of the project
     *
     * @return array
     */
    public function getTags();

    /**
     * Create a new tag at the current position
     *
     * @param string $tagName
     */
    public function createTag($tagName);

    /**
     * Delete a tag
     *
     * @param string $tagName
     */
    public function deleteTag($tagName);

    /**
     * Publish a new created tag
     *
     * @param string      $tagName
     * @param string|null $remote
     */
    public function publishTag($tagName, $remote = null);

    /**
     * Return the list of all modifications from the given tag until now
     *
     * @param string $tag
     * @param bool   $color
     * @param bool   $noMergeCommits
     *
     * @return array
     */
    public function getAllModificationsSince($tag, $color = true, $noMergeCommits = false);

    /**
     * Return the list of all modified files from the given tag until now
     * The result is an array with the filename as key and the status as value.
     * Status is one of the following : M (modified), A (added), R (removed)
     *
     * @param string $tag
     *
     * @return array
     */
    public function getModifiedFilesSince($tag);

    /**
     * Return local modification
     *
     * @return array of local modification
     */
    public function getLocalModifications();

    /**
     * Save the local modifications (commit)
     *
     * @param string $commitMsg
     *
     * @return mixed
     */
    public function saveWorkingCopy($commitMsg = '');

    /**
     * Revert the last commit. If a message is given, only revert
     * if the commit message matches.
     *
     * @param string|null $commitMsg
     */
    public function revertLastCommit($commitMsg = null);

    /**
     * Publish local modification
     *
     * @param string|null $remote
     */
    public function publishChanges($remote = null);
}
