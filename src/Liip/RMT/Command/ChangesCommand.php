<?php
/*
 * This file is part of the project RMT
 *
 * Copyright (c) 2013, Liip AG, http://www.liip.ch
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\RMT\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
        $this->addOption('exclude-merge-commits', null, InputOption::VALUE_NONE, 'Exclude merge commits');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $lastVersion = Context::get('version-persister')->getCurrentVersionTag();
        $noMerges = $input->getOption('exclude-merge-commits');

        $output->writeln("Here is the list of changes since <green>$lastVersion</green>:");
        $output->indent();
        $output->writeln(Context::get('vcs')->getAllModificationsSince($lastVersion, false, $noMerges));
    }
}
