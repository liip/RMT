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

use Liip\RMT\Exception\InvalidTagNameException;
use Liip\RMT\Exception\TagAlreadyExistsException;

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
     * Validate that a tag name is valid for the given VCS. If possible
     * should also check if this tag already exists or if we can create
     * it freely.
     *
     * @param $tagName
     * @throws InvalidTagNameException in case the name is invalid
     * @throws TagAlreadyExistsException in case the tag name already exists
     */
    public function validateTag($tagName);

    /**
     * Create a new tag at the current position
     *
     * @param string $tagName
     */
    public function createTag($tagName);

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
     * Publish local modification
     *
     * @param string|null $remote
     */
    public function publishChanges($remote = null);
}
