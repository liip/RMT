<?php

namespace Liip\RD\Action;

use Liip\RD\Context;

class VcsCommitAction extends BaseAction
{
    public function execute()
    {
        Context::get('vcs')->saveWorkingCopy(
            'Release of new version '.Context::getParam('new-version')
        );
        $this->confirmSuccess();
    }

}
