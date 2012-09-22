<?php

namespace Liip\RD\PreReleaseAction;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

use Liip\RD\VCS\VCSInterface;
use Liip\RD\Context;

abstract class BasePreReleaseAction
{
    public function getTitle()
    {
        return get_class($this);
    }

    abstract public function execute(Context $context);

    /**
     * A pre-release action can provide options, override this method and return a array of InputOption
     * @return InputOption[]
     */
    public function getInformationRequests()
    {
        return array();
    }
}