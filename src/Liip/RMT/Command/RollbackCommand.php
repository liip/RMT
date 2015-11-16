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
use Symfony\Component\Console\Output\OutputInterface;
use Liip\RMT\Context;

/**
 * Rollback the last release
 */
class RollbackCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('rollback');
        $this->setDescription('Rollback the last release if there was no change since.');
        $this->setHelp('The <comment>rollback</comment> should be used to cancel a previously done release.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (count(Context::get('vcs')->getLocalModifications()) > 0) {
            Context::get('output')->writeln('<error>Local modifications found. Aborting.</error>');
            return;
        }

        $tag = Context::get('version-persister')->getCurrentVersionTag();
        $modifications = Context::get('vcs')->getAllModificationsSince($tag, false, false);
        if (count($modifications) > 0) {
            Context::get('output')->writeln('<error>There were commits since the last release. Aborting.</error>');
            return;
        }

        $this->rollbackActionListIfExist('post-release-actions');
        $this->rollbackActionListIfExist('pre-release-actions');
    }

    protected function rollbackActionListIfExist($name)
    {
        $actions = Context::getInstance()->getList($name);
        $actions = array_reverse($actions);
        foreach ($actions as $num => $action) {
            $this->getOutput()->write(++$num.') '.$action->getTitle().' : ');
            $action->rollback();
        }
    }
}
