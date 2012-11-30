<?php

namespace Liip\RD\Action;

use Liip\RD\Context;

class VcsCommitAction extends BaseAction
{
    public function execute()
    {
        Context::getInstance()->getService('vcs')->saveWorkingCopy(
            'Release of new version '.Context::getInstance()->getParam('new-version')
        );
        $this->confirmSuccess();
    }

}
