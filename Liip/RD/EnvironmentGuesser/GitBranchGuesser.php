<?php

namespace Liip\RD\EnvironmentGuesser;

use Liip\RD\VCS\GIT;

class GitBranchGuesser implements EnvironmentGuesserInterface
{
    public function getCurrentEnvironment()
    {
        $git = new GIT();
        return $git->getCurrentBranch();
    }
}
