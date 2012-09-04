<?php

namespace Liip\RD\VCS;

interface VCSInterface
{
    public function getChangeList($lastVersionTag);
    public function getTags();
    public function createTag($tagName);
    public function pushTag($tagName);
    public function pushBranch($branchName);
    public function saveWorkingCopy();
}
