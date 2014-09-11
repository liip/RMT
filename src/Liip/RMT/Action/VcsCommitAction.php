<?php
/*
 * This file is part of the project RMT
 *
 * Copyright (c) 2013, Liip AG, http://www.liip.ch
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Liip\RMT\Action;

use Liip\RMT\Context;
use Liip\RMT\VCS\VCSInterface;

/**
 * Commit everything
 */
class VcsCommitAction extends BaseAction
{
    public function __construct($options = array())
    {
        $this->options = array_merge(
            array(
                'commit-message' => 'Release of new version %version%'
            ),
            $options
        );
    }

    public function execute()
    {
        /** @var VCSInterface $vcs */
        $vcs = Context::get('vcs');
        if (count($vcs->getLocalModifications()) == 0) {
            Context::get('output')->writeln("<error>No modification found, aborting commit</error>");

            return;
        }
        $vcs->saveWorkingCopy(
            str_replace('%version%', Context::getParam('new-version'), $this->options['commit-message'])
        );
        $this->confirmSuccess();
    }
}
