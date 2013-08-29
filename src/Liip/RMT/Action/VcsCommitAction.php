<?php

namespace Liip\RMT\Action;

use Liip\RMT\Context;
use Liip\RMT\VCS\VCSInterface;

/**
 * Commit everything
 */
class VcsCommitAction extends BaseAction
{
    public function execute()
    {
        /** @var VCSInterface $vcs */
        $vcs = Context::get('vcs');
        if (count($vcs->getLocalModifications()) == 0) {
            Context::get('output')->writeln("<error>No modification found, aborting commit</error>");
            return;
        }
        $vcs->saveWorkingCopy('Release of new version '.Context::getParam('new-version'));
        $this->confirmSuccess();
    }
}

