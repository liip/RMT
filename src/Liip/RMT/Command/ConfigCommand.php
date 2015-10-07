<?php

/*
 * This file is part of the project RMT
 *
 * Copyright (c) 2014, Liip AG, http://www.liip.ch
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\RMT\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Liip\RMT\Context;
use Symfony\Component\Yaml\Yaml;

/**
 * Display the last changes.
 */
class ConfigCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('config');
        $this->setDescription('Show the current parsed config (according to your branch)');
        $this->setHelp('The <comment>config</comment> command can be used to see the current config.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->loadContext();
        $output->writeln('<info>Current configuration is:</info>');
        $output->writeln(Yaml::dump(Context::getInstance()->getParam('config')));
    }
}
