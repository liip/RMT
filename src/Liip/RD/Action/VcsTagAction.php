<?php

namespace Liip\RD\Action;
use Liip\RD\Context;

class VcsTagAction extends BaseAction
{
    public function execute()
    {
        Context::getInstance()->getService('vcs')->createTag(
            Context::getInstance()->getService('vcs')->getTagFromVersion(
                Context::getInstance()->getParam('new-version')
            )
        );
        $this->confirmSuccess();
    }

}
