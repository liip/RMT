<?php

namespace Liip\RD\Action;

class VcsCommitAction extends BaseAction
{
    public function execute($context)
    {
        $context->getService('vcs')->saveWorkingCopy('Release of new version '.$context->getParam('new-version'));
    }

}
