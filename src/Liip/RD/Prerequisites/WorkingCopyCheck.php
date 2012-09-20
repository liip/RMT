<?php

namespace Liip\RD\Prerequisites;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

use Liip\RD\VCS\VCSInterface;
use Liip\RD\Context;


class WorkingCopyCheck  {

    public $ignoreCheckOptionName = 'ignore-check';

    public function execute($context)
    {
        $context->getService('output')->write('<info>Check that your working copy is clean:</info> ');
        if ($context->getService('input')->getOption($this->ignoreCheckOptionName))
        {
            $context->getService('output')->writeln("Check is ignored...");
            return;
        }
        if (count($modif = $context->getService('vcs')->getLocalModifications()) > 0){
            throw new \Exception('Your working directory contain local modifications, use --'.$this->ignoreCheckOptionName.' option to bypass this check');
        }
        $context->getService('output')->writeln("Check OK !");
        $context->getService('output')->writeln(" ");

    }

    public function getOptions()
    {
        return array(
            new InputOption($this->ignoreCheckOptionName, null, InputOption::VALUE_NONE, 'Do not process the check for clean working copy')
        );
    }
}