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

class Git extends BaseVCS
{
    protected $dryRun = false;

    public function getAllModificationsSince($tag, $color = true, $noMergeCommits = false)
    {
        $color = $color ? '--color=always' : '';
        $noMergeCommits = $noMergeCommits ? '--no-merges' : '';

        return $this->executeGitCommand("log --oneline $tag..HEAD $color $noMergeCommits");
    }

    public function getModifiedFilesSince($tag)
    {
        $data = $this->executeGitCommand("diff --name-status $tag..HEAD");
        $files = array();
        foreach ($data as $d) {
            $parts = explode("\t", $d);
            $files[$parts[1]] = $parts[0];
        }

        return $files;
    }

    public function getLocalModifications()
    {
        return $this->executeGitCommand('status -s');
    }

    public function getTags()
    {
        return $this->executeGitCommand('tag');
    }

    public function createTag($tagName)
    {
        // this requires git and gpg configured
        $signOption = (isset($this->options['sign-tag']) && $this->options['sign-tag']) ? '-s' : '';

        return $this->executeGitCommand("tag $signOption $tagName -m $tagName");
    }

    public function deleteTag($tagName)
    {
        return $this->executeGitCommand("tag -d $tagName");
    }

    public function publishTag($tagName, $remote = null)
    {
        $remote = $remote == null ? 'origin' : $remote;
        $this->executeGitCommand("push $remote $tagName");
    }

    public function publishChanges($remote = null)
    {
        $remote = $remote === null ? 'origin' : $remote;
        $this->executeGitCommand("push $remote ".$this->getCurrentBranch());
    }

    public function saveWorkingCopy($commitMsg = '')
    {
        $this->executeGitCommand('add --all');

        // this requires git and gpg configured
        $signOption = (isset($this->options['sign-commit']) && $this->options['sign-commit']) ? '-S' : '';

        $this->executeGitCommand("commit $signOption -m \"$commitMsg\"");
    }

    public function revertLastCommit($commitMsg = null)
    {
        if (! is_null($commitMsg)) {
            $msg = $this->executeGitCommand('log -1 --pretty=%B');
            if (count($msg) != 1 || $msg[0] !== $commitMsg) {
                return;
            }
        }

        $this->executeGitCommand('reset --hard HEAD~1');
    }

    public function getCurrentBranch()
    {
        $branches = $this->executeGitCommand('branch');
        foreach ($branches as $branch) {
            if (strpos($branch, '* ') === 0 && !preg_match('/^\*\s\(.*\)$/', $branch)) {
                return substr($branch, 2);
            }
        }
        throw new \Liip\RMT\Exception('Not currently on any branch');
    }

    protected function executeGitCommand($cmd)
    {
        // Avoid using some commands in dry mode
        if ($this->dryRun) {
            if ($cmd !== 'tag') {
                $cmdWords = explode(' ', $cmd);
                if (in_array($cmdWords[0], array('tag', 'push', 'add', 'commit'))) {
                    return;
                }
            }
        }

        // Execute
        $cmd = 'git ' . $cmd;
        exec($cmd, $result, $exitCode);
        if ($exitCode !== 0) {
            throw new \Liip\RMT\Exception('Error while executing git command: ' . $cmd . "\n" . implode("\n", $result));
        }

        return $result;
    }
}
