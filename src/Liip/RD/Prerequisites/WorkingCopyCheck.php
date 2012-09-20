<?php

namespace Liip\RD\Prerequisites;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

use Liip\RD\VCS\VCSInterface;
use Liip\RD\Context;


class WorkingCopyCheck  {

    public function execute($context)
    {
        if ($context->getService('input')->getOption('ignore-check'))
        {
            $context->getService('output')->writeln("Check is ignored...");
            return;
        }
        $context->getService('output')->writeln('<info>Check that your working copy is clean</info>');
        if (count($modif = $context->getService('vcs')->getLocalModifications()) > 0){
            throw new \Exception('Your working directory contain local modifications');
        }
        $context->getService('output')->writeln("Check OK !");
    }

    public function getOptions()
    {
        return array(
            new InputOption('ignore-check', null, InputOption::VALUE_NONE, 'Do not process the check for clean working copy')
        );
    }
}