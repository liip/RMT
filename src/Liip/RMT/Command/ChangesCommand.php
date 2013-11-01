<?php

namespace Liip\RMT\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Liip\RMT\Context;


/**
 * Display the last changes.
 */
class ChangesCommand extends BaseCommand
{
   protected function configure()
    {
        $this->setName('changes');
        $this->setDescription('Shows the list of changes since last release');
        $this->setHelp('The <comment>changes</comment> command is used to list the changes since last release.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $lastVersion = Context::get('version-persister')->getCurrentVersionTag();
        $output->writeln("Here is the list of changes since <green>$lastVersion</green>:");
        $output->indent();
        $output->writeln(Context::get('vcs')->getAllModificationsSince($lastVersion));
    }
}
