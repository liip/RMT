<?php
namespace Liip\RD\Command;


use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Liip\RD\Changelog\ChangelogManager;
use Liip\RD\Config\Handler;
use Liip\RD\Context;


class CurrentCommand extends BaseCommand {

    protected function configure()
    {
        $this->setName('current');
        $this->setDescription('Display information about the current version');
        $this->setHelp('The <comment>current</comment> task can be used to display information on the current release');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->loadContext();
        $output->writeln('Current release is: '. $this->context->getService('version-persister')->getCurrentVersion());
    }








}
