<?php

namespace Liip\RD\EnvironmentGuesser;

use Liip\RD\VCS\Git;

class GitBranchGuesser implements EnvironmentGuesserInterface
{
    public function getCurrentEnvironment()
    {
        $git = new Git();
        return $git->getCurrentBranch();
    }
}
