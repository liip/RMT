<?php

namespace Liip\RMT\VCS;

class Hg extends BaseVCS
{
    protected $dryRun = false;

    public function getAllModificationsSince($tag, $color=true)
    {
        $modifications = $this->executeHgCommand("log --template '{node|short} {desc}\n' -r tip:$tag");
        array_pop($modifications); // remove the last commit since it is the one described by the tag
        return $modifications;
    }

    public function getModifiedFilesSince($tag) {
        $data = $this->executeHgCommand("status --rev $tag:tip");
        $files = array();
        foreach($data as $d) {
            $parts = explode(" ", $d);
            $files[$parts[1]] = $parts[0];
        }
        return $files;
    }

    public function getLocalModifications(){
        return $this->executeHgCommand('status');
    }

    public function getTags()
    {
        $tags = $this->executeHgCommand("tags");
        $tags = array_map(function($t) {
            $parts = explode(' ', $t);
            return $parts[0];
        }, $tags);
        return $tags;
    }

    public function createTag($tagName)
    {
        return $this->executeHgCommand("tag $tagName");
    }

    public function publishTag($tagName)
    {
        // nothing to do, tags are published with other changes
    }

    public function publishChanges()
    {
        $this->executeHgCommand("push default");
    }

    public function saveWorkingCopy($commitMsg='')
    {
        $this->executeHgCommand("addremove");
        $this->executeHgCommand("commit -m \"$commitMsg\"");
    }

    public function getCurrentBranch()
    {
        $data = $this->executeHgCommand('branch');
	    return $data[0];
    }

    protected function executeHgCommand($cmd)
    {
        if ($this->dryRun){
            $binary = 'hg --dry-run ';
        } else {
            $binary = 'hg ';
        }

        // Execute
        $cmd = $binary.$cmd;
        exec($cmd, $result, $exitCode);

        if ($exitCode !== 0){
            throw new \Liip\RMT\Exception('Error while executing hg command: '.$cmd);
        }
        return $result;
    }
}

