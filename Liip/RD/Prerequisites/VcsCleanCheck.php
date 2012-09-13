<?php

namespace Liip\RD\Prerequisites;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

use Liip\RD\VCS\VCSInterface;
use Liip\RD\Context;


class VcsCleanCheck  {

    public function execute(Context $context)
    {
//        if ($input->getOption('ignore-check'))
//        {
//            $output->writeln("Check is ignored...");
//            return;
//        }
        $context->getOutput()->writeln('<info>Check that your working copy is clean</info>');
        //$context->getVCS()->checkStatus();
        $context->getOutput()->writeln("Check OK !");
    }

    public function getOptions()
    {
        return array(
            new InputOption('ignore-check', null, InputOption::VALUE_NONE, 'Do not process the check for clean working directory')
        );
    }
}